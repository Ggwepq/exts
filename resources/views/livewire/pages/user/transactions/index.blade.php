<?php
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public $transactions;
    public $filters = [
        'types' => [],
        'account_id' => '',
        'date_from' => '',
        'date_to' => '',
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
            $query->where('account_id', $this->filters['account_id']);
        }

        // Apply date range filter
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        $this->transactions = $query
            ->orderBy('created_at', 'DESC')
            ->get()
            ->groupBy(function ($transaction) {
                $date = \Carbon\Carbon::parse($transaction->created_at);

                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('F j, Y');
                }
            })
            ->all();

        // Dispatch account update to make sure account balances are refreshed
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
            'date_from' => '',
            'date_to' => '',
        ];

        $this->loadTransactions();
    }

    public function placeholder()
    {
        return view('livewire.pages.user.components.placeholders.placeholder');
    }
}; ?>

<section x-data="{ detailSidebarOpen: false }" x-cloak>
    <!-- Main Content with Animated Margin -->
    <div class="transition-all duration-300 ease-in-out" :class="{ 'md:mr-[23rem]': detailSidebarOpen }">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.transactions.header'])

        <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                <!-- Active Filters Banner -->
                @if (
                    !empty($filters['types']) ||
                        !empty($filters['account_id']) ||
                        !empty($filters['date_from']) ||
                        !empty($filters['date_to']))
                    <div class="badge badge-info gap-2 mb-4 p-3 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                        Filters applied
                        <button class="text-xs" wire:click="resetFilters">Clear</button>
                    </div>
                @endif

                @if (count($transactions))
                    <ul class="list bg-base-100 rounded-box space-y-4">
                        @foreach ($transactions as $date => $record)
                            @php
                                $totalIncome = $record->where('types.name', 'Income')->sum('amount');
                                $totalExpense = $record->where('types.name', 'Expense')->sum('amount');
                            @endphp

                            <li
                                class="bg-base-300/50 text-sm font-medium py-2 px-4 rounded-lg mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm flex justify-between">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4 text-base-content/70">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    {{ $date }}
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

                                    <div class="flex flex-row md:items-center w-full">
                                        <!-- Transaction Name -->
                                        <div
                                            class="w-1/3 truncate font-bold text-md mb-1.5 mr-2  transition-colors duration-200 {{ $transaction->types->name == 'Expense' ? 'group-hover:text-secondary' : 'group-hover:text-primary' }}">
                                            {{ $transaction->name }}
                                        </div>

                                        <!-- Account Name -->
                                        <div class="w-1/3 truncate uppercase font-semibold ">
                                            <span
                                                class="text-xs badge badge-lg badge-outline {{ $transaction->types->name == 'Expense' ? 'badge-secondary text-white' : 'badge-primary text-white' }}">
                                                {{ $transaction->accounts->name }}
                                            </span>
                                        </div>

                                        <!-- Amount -->
                                        <div class="w-1/3 flex-shrink-0 text-right">
                                            <span
                                                class="text-xs uppercase font-semibold badge badge-lg {{ $transaction->types->name == 'Expense' ? 'badge-secondary text-white' : 'badge-primary text-white' }}">
                                                {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ number_format($transaction->amount, 3) }}
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
                        <span class="text-base-content/60 text-lg font-medium mb-1">No transactions found</span>
                        <p class="text-base-content/40 text-sm mb-4">Start adding your transactions to track your
                            finances</p>
                        @if (
                            !empty($filters['types']) ||
                                !empty($filters['account_id']) ||
                                !empty($filters['date_from']) ||
                                !empty($filters['date_to']))
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
