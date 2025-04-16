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
            'date_mode' => '',
            'search' => '',
            'sort' => [
                'field' => 'created_at',
                'direction' => 'DESC',
            ],
        ];

        $this->loadTransactions();
    }
}; ?>

<section x-data="{ detailSidebarOpen: false }" x-cloak>
    <!-- Main Content with Animated Margin -->
    <div class="transition-all duration-300 ease-in-out" :class="{ 'md:mr-[23rem]': detailSidebarOpen }">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.transactions.header', 'header' => 'Transactions'])

        <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">

                @if (count($transactions))
                    <ul class="list bg-base-100 rounded-box space-y-4">
                        @foreach ($transactions as $date => $record)
                            @php
                                // Calculate totals differently if we're looking at amount sorting special case
if ($date === 'Transactions Sorted by Amount') {
    $totalIncome = $record->where('types.name', 'Income')->sum('amount');
    $totalExpense = $record->where('types.name', 'Expense')->sum('amount');
} else {
    $totalIncome = $record->where('types.name', 'Income')->sum('amount');
    $totalExpense = $record->where('types.name', 'Expense')->sum('amount');
                                }
                            @endphp

                            <li
                                class="bg-base-300/50 text-sm font-medium py-2 px-4 rounded-lg mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm flex justify-between">
                                <div class="flex items-center gap-2">
                                    @if ($date === 'Transactions Sorted by Amount')
                                        @if ($filters['sort'] && $filters['sort']['direction'] == 'ASC')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                            </svg>
                                            Amount (Low to High)
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
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
                                <div class="text-right text-xs font-semibold">
                                    <span class="text-primary">+₱{{ number_format($totalIncome, 2) }}</span>
                                    <span class="text-secondary">-₱{{ number_format($totalExpense, 2) }}</span>
                                </div>
                            </li>

                            @foreach ($record as $transaction)
                                <li class="group list-row hover:bg-base-200 flex items-center justify-between w-full px-5 py-4 border border-base-200 rounded-xl mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer"
                                    @click="$dispatch('showSidebar', {operation: 'edit', page: 'Transaction', component: 'pages.user.transactions.edit', modelId: {{ $transaction->id }}}); detailSidebarOpen = true;">
                                    <!-- Red for Expense, Green for Income -->

                                    <div class="flex flex-row md:items-center w-full grow">
                                        <!-- Transaction Name -->
                                        <div
                                            class="w-1/3 truncate font-bold text-md mb-1.5 mr-2 text-base-content transition-colors duration-200 {{ $transaction->types->name == 'Expense' ? 'group-hover:text-secondary' : 'group-hover:text-primary' }}">
                                            {{ $transaction->name }}
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
                                                class="text-xs uppercase font-semibold badge badge-lg {{ $transaction->types->name == 'Expense' ? 'badge-secondary ' : 'badge-primary ' }}">
                                                {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ number_format($transaction->amount, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center p-10 bg-base-200/30 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-16 text-base-300 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span class="text-base-content/60 text-lg font-medium mb-1">
                            @if (!empty($filters['search']))
                                No transactions found matching your search.
                            @else
                                No transactions found
                            @endif
                        </span>
                        <p class="text-base-content/40 text-sm mb-4">
                            @if (
                                !empty($filters['search']) ||
                                    !empty($filters['types']) ||
                                    !empty($filters['account_id']) ||
                                    !empty($filters['date_mode']))
                                Try adjusting your filters or search criteria
                            @else
                                Start adding your transactions to track your finances
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
                                Add Your First Transaction
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    @livewire('pages.user.containers.details-sidebar', ['lazy' => true])

    <!-- Add the Image Viewer Component -->
    <x-image-viewer imageUrl="" />
</section>
