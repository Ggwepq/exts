<?php
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    public $transactions;
    public $filters = [
        'types' => [],
        'account_id' => [],
        'category_id' => [],
        'frequency' => [],
        'date_mode' => '',
        'search' => '',
        'sort' => [
            'field' => 'created_at',
            'direction' => 'DESC',
        ],
    ];
    public $accounts;

    public function mount()
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        $this->loadTransactions();
    }

    /**
     * Fetch user transactions based on filters
     */
    #[On('transactionUpdate')]
    public function loadTransactions()
    {
        $query = Transaction::with(['tags', 'accounts', 'types'])
            ->where('user_id', Auth::id())
            ->where('name', 'not like', 'Initial Account Balance');

        // Apply type filter
        if (!empty($this->filters['types'])) {
            $query->whereIn('type_id', $this->filters['types']);
        }

        // Apply account filter
        if (!empty($this->filters['account_id'])) {
            $query->whereIn('account_id', $this->filters['account_id']);
        }

        // Apply category filter
        if (!empty($this->filters['category_id'])) {
            $query->whereIn('category_id', $this->filters['category_id']);
        }
        //
        // Filter by recurrence frequency
        if (!empty($this->filters['frequency'])) {
            $query->whereHas('recurringTransactions', function ($q) {
                $q->whereIn('frequency', (array) $this->filters['frequency']);
            });
        }

        // Search
        if (!empty($this->filters['search'])) {
            $searchTerm = '%' . $this->filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhereHas('tags', function ($tagQuery) use ($searchTerm) {
                        $tagQuery->where('name', 'like', $searchTerm);
                    });
            });
        }

        // Sorting
        if (isset($this->filters['sort'])) {
            $query->orderBy($this->filters['sort']['field'], $this->filters['sort']['direction']);
        } else {
            $query->orderBy('created_at', 'DESC');
        }

        $transactions = $query->get();

        if (isset($this->filters['sort']) && $this->filters['sort']['field'] === 'amount') {
            $this->transactions = [
                'Transactions Sorted by Amount' => $transactions,
            ];
        } else {
            $this->transactions = $transactions
                ->groupBy(function ($transaction) {
                    $date = Carbon::parse($transaction->created_at);

                    switch ($this->filters['date_mode']) {
                        case 'yearly':
                            return $date->format('Y'); // 2025

                        case 'monthly':
                            return $date->format('F Y'); // April 2025

                        case 'weekly':
                            $startOfWeek = $date->copy()->startOfWeek(); // Monday of that week
                            $startOfYear = $startOfWeek->copy()->startOfYear();

                            // dd($startOfWeek, $nextMonth, $startOfMonth);

                            // Count week number from the month the week STARTS in
                            $weekNumber = intval($startOfYear->diffInWeeks($startOfWeek)) + 1;

                            $yearLabel = $startOfYear->format('Y');
                            $weekLabel = $this->ordinal($weekNumber) . ' Week';

                            return "{$weekLabel} of {$yearLabel} (" . $startOfWeek->format('M j') . ')';

                        case 'daily':
                        default:
                            if ($date->isToday()) {
                                return 'Today';
                            } elseif ($date->isYesterday()) {
                                return 'Yesterday';
                            } else {
                                return $date->format('F j, Y'); // April 14, 2025
                            }
                    }
                })
                ->all();
        }

        $this->dispatch('accountUpdate');
    }

    public function ordinal($number)
    {
        if (!in_array($number % 100, [11, 12, 13])) {
            switch ($number % 10) {
                case 1:
                    return $number . 'st';
                case 2:
                    return $number . 'nd';
                case 3:
                    return $number . 'rd';
            }
        }
        return $number . 'th';
    }

    /**
     * Update filters when changed in the header component
     */
    #[On('filters-updated')]
    public function updateFilters($filters)
    {
        $this->filters = $filters;
        $this->loadTransactions();
    }

    /**
     * Sort transactions by the given field and direction
     */
    #[On('sort-updated')]
    public function updateSort($field, $direction)
    {
        $this->filters['sort'] = [
            'field' => $field,
            'direction' => $direction,
        ];
        $this->loadTransactions();
    }

    /**
     * Apply the selected filters
     */
    public function applyFilters()
    {
        $this->loadTransactions();
    }

    /**
     * Reset all filters to default values
     */
    public function resetFilters()
    {
        $this->filters = [
            'types' => [],
            'account_id' => '',
            'category_id' => '',
            'frequency' => '',
            'date_mode' => '',
            'search' => '',
            'sort' => [
                'field' => 'created_at',
                'direction' => 'DESC',
            ],
        ];

        $this->loadTransactions();
    }

    public function formatShortAmount($amount)
    {
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1) . 'M';
        } elseif ($amount >= 1_000) {
            return number_format($amount / 1_000, 1) . 'K';
        }
        return number_format($amount, 2);
    }

    // Bulk Delete
    public function deleteSelected(array $ids)
    {
        Transaction::whereIn('id', $ids)->delete();
        Toaster::success('Items Deleted!');
        $this->dispatch('transactionUpdate');
    }
}; ?>

<section x-data="{
    detailSidebarOpen: false,
    selected: [],
    toggle(id) {
        if (this.selected.includes(id)) {
            this.selected = this.selected.filter(item => item !== id);
        } else {
            this.selected.push(id);
        }
    },
    selectAll(ids) {
        this.selected = [...ids];
    },
    clearSelected() {
        this.selected = [];
    }
}" x-cloak class="h-screen">
    <!-- Main Content with Animated Margin -->
    <!-- <div class="transition-all duration-300 ease-in-out" -->
    <!--     :class="{ 'md:mr-[17rem] lg:mr-[23rem] xl:mr-[29rem] 2xl:mr-[31rem]': detailSidebarOpen }"> -->

    <!-- No Margin -->
    <div class="transition-all duration-300 ease-in-out">

        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.transactions.header', 'header' => 'Transactions'])

        <div class="flex-1 overflow-y-auto pt-4 pb-10 px-6 bg-base-200">

            <div x-show="selected.length > 0" x-transition
                class="fixed bottom-1 left-1/2 transform -translate-x-1/2 bg-base-100 shadow-lg rounded-lg p-3 z-50 border border-base-content/10">
                <div class="flex gap-2 justify-center items-center">
                    <div class="flex justify-center items-center gap-2 mr-4">
                        <!-- <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" -->
                        <!--     stroke="currentColor" class="size-8"> -->
                        <!--     <path stroke-linecap="round" stroke-linejoin="round" -->
                        <!--         d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" /> -->
                        <!-- </svg> -->
                        <span class=" text-sm font-semibold badge badge-primary" x-text="selected.length"></span>
                        <span class=" text-sm font-semibold" x-text="'selected'"></span>
                    </div>
                    <button class="btn btn-sm btn-ghost btn-outline"
                        @click="$dispatch('showRightSidebar', {
                            operation: 'edit',
                            page: 'Transactions',
                            component: 'pages.user.transactions.bulk-edit',
                            modelId: selected
                        }); rightSidebarOpen = true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Edit Fields</button>
                    <button class="btn btn-sm btn-ghost btn-outline"
                        @click="$wire.call('deleteSelected', selected); selected = []">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Delete
                    </button>
                    <button class="btn btn-sm btn-outline" @click="clearSelected()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                @if (count($transactions))
                    <ul class="list bg-base-100 space-y-4">
                        @foreach ($transactions as $date => $record)
                            @php
                                if ($date === 'Transactions Sorted by Amount') {
                                    $totalIncome = $record->where('types.name', 'Income')->sum('amount');
                                    $totalExpense = $record->where('types.name', 'Expense')->sum('amount');
                                } else {
                                    $totalIncome = $record->where('types.name', 'Income')->sum('amount');
                                    $totalExpense = $record->where('types.name', 'Expense')->sum('amount');
                                }

                            @endphp
                            <li
                                class="bg-base-200 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm flex justify-between">
                                <div class="flex items-center gap-2">
                                    <!-- <input type="checkbox" class="checkbox " /> -->
                                    <div class="flex items-center gap-2"
                                        @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.create'}); rightSidebarOpen = true;">

                                        @if ($date === 'Transactions Sorted by Amount')
                                            @if ($filters['sort'] && $filters['sort']['direction'] == 'ASC')
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                                </svg>
                                                Amount (Low to High)
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                </svg>
                                                Amount (High to Low)
                                            @endif
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor"
                                                class="size-4 text-base-content/70">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                            {{ $date }}
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right text-xs w-1/2  font-semibold">
                                    <span class="text-primary truncate inline-block md:hidden w-1/2 md:w-auto">
                                        +₱{{ $this->formatShortAmount($totalIncome) }}
                                    </span>
                                    <span class="text-primary truncate hidden md:inline-block w-1/2 md:w-auto">
                                        +₱{{ number_format($totalIncome, 2) }}
                                    </span>

                                    <span class="text-secondary truncate inline-block md:hidden w-1/2 md:w-auto">
                                        -₱{{ $this->formatShortAmount($totalExpense) }}
                                    </span>
                                    <span class="text-secondary truncate hidden md:inline-block w-1/2 md:w-auto">
                                        -₱{{ number_format($totalExpense, 2) }}
                                    </span>
                                </div>
                            </li>

                            @foreach ($record as $transaction)
                                <li
                                    class="group list-row hover:bg-base-200 flex items-center justify-between w-full p2-5 py-4 border border-base-200 mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer">
                                    <!-- Red for Expense, Green for Income -->
                                    <div class="opacity-0 hover:opacity-100 transition-all duration-100 ease-in-out"
                                        :class="selected.length > 0 ? 'opacity-100' : 'opacity-0'">

                                        <input type="checkbox" class="checkbox "
                                            :checked="selected.includes({{ $transaction->id }})"
                                            @change="toggle({{ $transaction->id }})" />
                                    </div>

                                    <div class="flex flex-row md:items-center w-full grow"
                                        @click="$dispatch('showSidebar', {operation: 'view', page: 'Transaction', component: 'pages.user.transactions.view', modelId: {{ $transaction->id }}}); detailSidebarOpen = true;">
                                        <!-- Transaction Name -->
                                        <div class="w-1/3 truncate font-bold md:flex">
                                            <span
                                                class="text-md mb-1.5 mr-2 text-base-content transition-colors duration-200 {{ $transaction->types->name == 'Expense' ? 'group-hover:text-secondary' : 'group-hover:text-primary' }}">
                                                {{ $transaction->name }}
                                            </span>
                                            <span>
                                                @if ($transaction->recurringTransactions)
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        @click.stop="$dispatch('showSidebar', {operation: 'view', page: 'Recurring', component: 'pages.user.recurrings.view', modelId: {{ $transaction->recurringTransactions->id }}}); detailSidebarOpen = true;"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="h-5 w-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" />
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Account Name -->
                                        <div class="w-1/3 truncate uppercase font-semibold hidden md:flex">
                                            <span
                                                class="text-xs badge badge-lg badge-outline {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                                {{ $transaction->accounts->name }}
                                            </span>
                                        </div>

                                        <!-- Amount -->
                                        <div class="w-1/3 flex-shrink-0 text-right grow">
                                            <span
                                                class="text-xs uppercase font-semibold badge badge-lg truncate w-3/4 md:w-auto  {{ $transaction->types->name == 'Expense' ? 'badge-secondary ' : 'badge-primary ' }}">
                                                <span class="md:hidden">
                                                    {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ $this->formatShortAmount($transaction->amount) }}
                                                </span>
                                                <span class="hidden md:inline">
                                                    {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ number_format($transaction->amount, 2) }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center p-10 bg-base-200/30 ">
                        <span class="text-base-content text-lg font-medium mb-1">
                            😴 No transactions found
                        </span>
                        <p class="text-base-content/40 text-sm mb-4">
                            @if (
                                !empty($filters['search']) ||
                                    !empty($filters['types']) ||
                                    !empty($filters['account_id']) ||
                                    !empty($filters['date_mode']))
                            @endif
                        </p>
                        @if (
                            !empty($filters['search']) &&
                                (empty($filters['types']) && empty($filters['account_id']) && empty($filters['date_mode'])))
                            <!-- No button for search-only filtering -->
                        @elseif (
                            !empty($filters['types']) ||
                                !empty($filters['account_id']) ||
                                !empty($filters['date_mode']) ||
                                !empty($filters['search']))
                            <button class="btn btn-sm btn-outline btn-primary mt-2" wire:click="resetFilters">
                                Clear filters
                            </button>
                        @else
                            <button class="btn btn-sm btn-primary"
                                @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.add', modelId: 12})">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Add Transaction
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    @livewire('pages.user.containers.details-sidebar', ['lazy' => true])
    @livewire('pages.user.containers.right-sidebar', ['lazy' => true])

    <!-- Add the Image Viewer Component -->
    <x-image-viewer imageUrl="" />
</section>
