<?php
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $transactions;
    public $filters = [
        'types' => [],
        'account_id' => '',
        'date_from' => '',
        'date_to' => ''
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
        $query = Transaction::where('user_id', Auth::id())
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
        
        $this->transactions = $query->orderBy('created_at', 'DESC')
            ->get()
            ->groupBy(function ($transaction) {
                $date = \Carbon\Carbon::parse($transaction->created_at);
                return $date->format('F j, Y');
            })
            ->all();
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
            'date_to' => ''
        ];
        
        $this->loadTransactions();
    }

    public function placeholder()
    {
        return view('livewire.pages.user.components.placeholders.placeholder');
    }
}; ?>

<div>
    <!-- Main Content with Animated Margin -->
    <div class="transition-all duration-300 ease-in-out" :class="{ 'md:mr-[28rem]': detailSidebarOpen }">
        @livewire('pages.user.containers.main-header', [
            'component' => 'pages.user.transactions.header',
            'filters' => $filters,
            'wire:key' => 'transaction-header'
        ])

        <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                <!-- Active Filters Banner -->
                @if (!empty($filters['types']) || !empty($filters['account_id']) || !empty($filters['date_from']) || !empty($filters['date_to']))
                    <div class="badge badge-info gap-2 mb-4 p-3">
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
                    <ul class="list bg-base-100 rounded-box shadow-md">
                        @foreach ($transactions as $date => $record)
                            <li class="p-4 pb-2 text-xs opacity-60 tracking-wide">{{ $date }}</li>

                            @foreach ($record as $transaction)
                                <li class="list-row hover:bg-base-200">
                                    <div>
                                        @if($transaction->image_url)
                                            <img class="size-10 rounded-box cursor-pointer"
                                                src="{{ asset('app/' . $transaction->image_url) }}"
                                                @click.stop="$dispatch('open-image-viewer', '{{ asset('app/' . $transaction->image_url) }}')" 
                                                alt="Receipt for {{ $transaction->name }}" />
                                        @else
                                            <img class="size-10 rounded-box"
                                                src="{{ asset('img/default-img.png') }}" 
                                                alt="No receipt" />
                                        @endif
                                    </div>
                                    <div @click="$dispatch('showSidebar', {operation: 'edit', page: 'Transaction', component: 'pages.user.transactions.edit', modelId: {{ $transaction->id }}}); detailSidebarOpen = true;">
                                        <div class="font-bold text-md mb-2">{{ $transaction->name }}</div>
                                        <div
                                            class="text-[0.70rem] uppercase font-semibold badge badge-outline {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                            {{ $transaction->types->name }}</div>
                                    </div>

                                    <div @click="$dispatch('showSidebar', {operation: 'edit', page: 'Transaction', component: 'pages.user.transactions.edit', modelId: {{ $transaction->id }}}); detailSidebarOpen = true;">
                                        <div
                                            class="text-[0.70rem] uppercase font-semibold badge badge-outline {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                            {{ $transaction->accounts->name }}</div>
                                    </div>

                                    <div @click="$dispatch('showSidebar', {operation: 'edit', page: 'Transaction', component: 'pages.user.transactions.edit', modelId: {{ $transaction->id }}}); detailSidebarOpen = true;">
                                        <div
                                            class="text-sm uppercase font-semibold badge {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                            {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ $transaction->amount }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center p-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                            stroke="currentColor" class="size-12 text-base-300 mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round" 
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span class="text-base-300">No transactions found</span>
                        @if (!empty($filters['types']) || !empty($filters['account_id']) || !empty($filters['date_from']) || !empty($filters['date_to']))
                            <button class="btn btn-sm btn-ghost mt-2" wire:click="resetFilters">
                                Clear filters
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
</div>
