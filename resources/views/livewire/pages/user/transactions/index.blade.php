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
        
        $this->transactions = $query->orderBy('created_at', 'DESC')
            ->get()
            ->groupBy(function ($transaction) {
                $date = \Carbon\Carbon::parse($transaction->created_at);
                return $date->format('F j, Y');
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
            'date_to' => ''
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
    <div class="transition-all duration-300 ease-in-out" :class="{ 'md:mr-[28rem]': detailSidebarOpen }">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.transactions.header'])

        <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                <!-- Active Filters Banner -->
                @if (!empty($filters['types']) || !empty($filters['account_id']) || !empty($filters['date_from']) || !empty($filters['date_to']))
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
                            <li class="bg-base-200/50 text-sm font-medium py-2 px-4 rounded-lg mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                        stroke="currentColor" class="size-4 text-base-content/70">
                                        <path stroke-linecap="round" stroke-linejoin="round" 
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    {{ $date }}
                                </div>
                            </li>

                            @foreach ($record as $transaction)
                                <li class="group list-row hover:bg-base-200 flex items-center justify-between w-full px-5 py-4 border border-base-200 rounded-xl mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer"
                                    @click="$dispatch('showSidebar', {operation: 'edit', page: 'Transaction', component: 'pages.user.transactions.edit', modelId: {{ $transaction->id }}}); detailSidebarOpen = true;">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="flex-shrink-0 relative">
                                            @if($transaction->image_url)
                                                <div class="size-12 rounded-lg overflow-hidden shadow-md border border-base-200 bg-base-100">
                                                    <img class="w-full h-full object-cover cursor-pointer hover:scale-110 transition-transform duration-300"
                                                        src="{{ asset('app/' . $transaction->image_url) }}"
                                                        @click.stop="$dispatch('open-image-viewer', '{{ asset('app/' . $transaction->image_url) }}')" 
                                                        alt="Receipt for {{ $transaction->name }}" />
                                                </div>
                                                <div class="absolute -bottom-1 -right-1 size-4 rounded-full bg-primary shadow-sm border border-base-100"></div>
                                            @else
                                                <div class="size-12 rounded-lg flex items-center justify-center bg-base-200/70 border border-base-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                                        stroke="currentColor" class="size-6 text-base-content/40">
                                                        <path stroke-linecap="round" stroke-linejoin="round" 
                                                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-md mb-1.5 truncate group-hover:text-primary transition-colors duration-200">{{ $transaction->name }}</div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <div
                                                    class="text-[0.70rem] uppercase font-semibold badge badge-outline {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                                    {{ $transaction->types->name }}</div>
                                            </div>
                                            
                                            <!-- Tags -->
                                            @if(count($transaction->tags) > 0)
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($transaction->tags as $tag)
                                                <span class="badge badge-sm badge-ghost">{{ $tag->name }}</span>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex-shrink-0 flex items-center gap-2">
                                        <div
                                            class="text-[0.70rem] uppercase font-semibold badge badge-outline {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                            {{ $transaction->accounts->name }}
                                        </div>
                                        <div
                                            class="text-sm uppercase font-semibold badge badge-lg whitespace-nowrap {{ $transaction->types->name == 'Expense' ? 'badge-secondary text-white' : 'badge-primary text-white' }}">
                                            {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ $transaction->amount }}
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
                        <p class="text-base-content/40 text-sm mb-4">Start adding your transactions to track your finances</p>
                        @if (!empty($filters['types']) || !empty($filters['account_id']) || !empty($filters['date_from']) || !empty($filters['date_to']))
                            <button class="btn btn-sm btn-outline btn-primary mt-2" wire:click="resetFilters">
                                Clear filters
                            </button>
                        @else
                            <button class="btn btn-sm btn-primary" 
                                @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.add', modelId: 12})">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-5 mr-1">
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
