<?php

use Livewire\Volt\Component;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $accounts = [];
    public $filters = [
        'types' => [],
        'account_id' => '',
        'date_from' => '',
        'date_to' => '',
        'search' => '',
        'sort' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ]
    ];
    public $sortField = 'created_at';
    public $sortDirection = 'DESC';
    public $isSearchOpen = false;

    public function mount($filters = null)
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        if ($filters) {
            $this->filters = $filters;
            if (isset($filters['sort'])) {
                $this->sortField = $filters['sort']['field'];
                $this->sortDirection = $filters['sort']['direction'];
            }
        }
    }

    public function updatedFilters()
    {
        $this->dispatch('filters-updated', $this->filters);
    }

    public function resetFilters()
    {
        $this->filters = [
            'types' => [],
            'account_id' => '',
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'sort' => [
                'field' => 'created_at',
                'direction' => 'DESC'
            ]
        ];
        $this->sortField = 'created_at';
        $this->sortDirection = 'DESC';
        $this->dispatch('filters-updated', $this->filters);
    }

    public function resetSort()
    {
        $this->sortField = null;
        $this->sortDirection = null;
        
        $this->filters['sort'] = [
            'field' => 'created_at',
            'direction' => 'DESC'
        ];
        
        $this->dispatch('sort-updated', 'created_at', 'DESC');
    }

    public function applyFilters()
    {
        $this->dispatch('filters-updated', $this->filters);
    }

    public function sortBy($field, $direction)
    {
        $this->sortField = $field;
        $this->sortDirection = $direction;
        $this->filters['sort'] = [
            'field' => $field,
            'direction' => $direction
        ];
        $this->dispatch('sort-updated', $field, $direction);
    }

    public function toggleSearch()
    {
        $this->isSearchOpen = !$this->isSearchOpen;
    }
}; ?>

<div class="flex flex-col md:items-center justify-between gap-4 md:flex-row">
    <div class="flex items-center gap-3">
        @livewire('pages.user.components.sidebar-button')
        <div class="bg-primary/20 p-3 rounded-lg hidden md:flex">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-8 text-primary">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-base-content">Transactions</h1>
    </div>

    <div class="flex-none">
        <div class="flex gap-2 items-center" x-data="{ showSearchBar: false }">
            <div class="gap-2 items-center"
                :class="showSearchBar && (detailSidebarOpen && showSearchBar) ? 'hidden' : 'flex'">
                
                <!-- Sort Button -->
                <div class="dropdown dropdown-start md:dropdown-center">
                    <label tabindex="0"
                        class="btn btn-sm bg-indigo-100 hover:bg-indigo-200 text-indigo-700 border border-indigo-300 shadow-sm font-medium"
                        aria-label="Sort">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                            stroke="currentColor" class="size-4 mr-1 text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round" 
                                d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12" />
                        </svg>
                        <span>Sort</span>
                    </label>
                    <div tabindex="0"
                        class="dropdown-content z-[1] menu p-3 mt-4 shadow-lg bg-base-100 rounded-xl w-56 border border-base-200">
                        <h3 class="font-bold text-md mb-2">Sort Transactions</h3>
                        <div class="divider my-1"></div>
                        
                        <!-- By Amount -->
                        <div class="font-medium text-sm mb-2">By Amount</div>
                        <div class="flex flex-col gap-1">
                            <button wire:click="sortBy('amount', 'ASC')" 
                                class="btn btn-sm {{ $sortField === 'amount' && $sortDirection === 'ASC' && $sortField !== null ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-0' : 'bg-emerald-100 text-emerald-700 border border-emerald-200 hover:bg-emerald-200' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                    stroke="currentColor" class="size-4 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M8.25 6.75L12 3m0 0l3.75 3.75M12 3v18" />
                                </svg>
                                Ascending (Low to High)
                            </button>
                            <button wire:click="sortBy('amount', 'DESC')" 
                                class="btn btn-sm {{ $sortField === 'amount' && $sortDirection === 'DESC' && $sortField !== null ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-0' : 'bg-emerald-100 text-emerald-700 border border-emerald-200 hover:bg-emerald-200' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                    stroke="currentColor" class="size-4 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M15.75 17.25L12 21m0 0l-3.75-3.75M12 21V3" />
                                </svg>
                                Descending (High to Low)
                            </button>
                        </div>

                        <!-- By Date -->
                        <div class="font-medium text-sm mt-3 mb-2">By Date</div>
                        <div class="flex flex-col gap-1">
                            <button wire:click="sortBy('created_at', 'ASC')" 
                                class="btn btn-sm {{ $sortField === 'created_at' && $sortDirection === 'ASC' && $sortField !== null ? 'bg-gradient-to-r from-indigo-500 to-blue-500 text-white border-0' : 'bg-indigo-100 text-indigo-700 border border-indigo-200 hover:bg-indigo-200' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                    stroke="currentColor" class="size-4 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M8.25 6.75L12 3m0 0l3.75 3.75M12 3v18" />
                                </svg>
                                Oldest First
                            </button>
                            <button wire:click="sortBy('created_at', 'DESC')" 
                                class="btn btn-sm {{ $sortField === 'created_at' && $sortDirection === 'DESC' && $sortField !== null ? 'bg-gradient-to-r from-indigo-500 to-blue-500 text-white border-0' : 'bg-indigo-100 text-indigo-700 border border-indigo-200 hover:bg-indigo-200' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                    stroke="currentColor" class="size-4 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M15.75 17.25L12 21m0 0l-3.75-3.75M12 21V3" />
                                </svg>
                                Newest First
                            </button>
                        </div>
                        
                        <!-- Reset Sort Button -->
                        <div class="divider my-2"></div>
                        <div class="flex justify-center">
                            <button wire:click="resetSort" 
                                class="btn btn-sm bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                    stroke="currentColor" class="size-4 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                Reset Sort
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Button -->
                <div class="dropdown"
                    :class="detailSidebarOpen ? 'dropdown-start md:dropdown-end' : 'dropdown-start md:dropdown-center'"
                    x-data="{ expense: false, income: false }">
                    <label tabindex="0"
                        class="btn btn-sm bg-amber-100 hover:bg-amber-200 text-amber-700 border border-amber-300 shadow-sm font-medium"
                        aria-label="Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4 mr-1 text-amber-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                        <div :class="detailSidebarOpen ? 'hidden' : ''">
                            <span>Filters</span>
                            <span class="badge bg-amber-500 text-white badge-xs ml-1">
                                {{ count($filters['types']) + ($filters['account_id'] ? 1 : 0) + ($filters['date_from'] ? 1 : 0) + ($filters['date_to'] ? 1 : 0) + ($filters['search'] ? 1 : 0) }}
                            </span>
                        </div>
                    </label>
                    
                    <!-- Filter dropdown content -->
                    <div tabindex="0"
                        class="dropdown-content z-[1] menu p-4 mt-4 shadow-lg bg-base-100 rounded-xl w-80 border border-base-200">
                        <h3 class="font-bold text-lg mb-2">Filter Transactions</h3>

                        <!-- Type Filter -->
                        <div class="form-control mb-3">
                            <div class="flex flex-row gap-1">
                                <label class="flex-1 cursor-pointer">
                                    <input type="checkbox" class="hidden" wire:model.live="filters.types"
                                        value="1">
                                    <div class="btn btn-sm w-full {{ in_array('1', $filters['types']) ? 'bg-gradient-to-r from-indigo-500 to-blue-500 text-white border-0' : 'bg-indigo-100 text-indigo-700 border border-indigo-200 hover:bg-indigo-200' }}" @click="income = !income">Income</div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="checkbox" class="hidden" wire:model.live="filters.types"
                                        value="2">
                                    <div class="btn btn-sm w-full {{ in_array('2', $filters['types']) ? 'bg-gradient-to-r from-pink-500 to-rose-500 text-white border-0' : 'bg-pink-100 text-pink-700 border border-pink-200 hover:bg-pink-200' }}" @click="expense = !expense">Expense</div>
                                </label>
                            </div>
                        </div>

                        <!-- Account Filter -->
                        <div class="form-control mb-3">
                            <select class="select select-bordered select-sm w-full bg-base-100 focus:border-amber-400 focus:ring focus:ring-amber-200"
                                wire:model.live="filters.account_id">
                                <option value="">All Accounts</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div class="form-control mb-3">
                            <div class="flex flex-col gap-2">
                                <input type="date" class="input input-bordered input-sm w-full bg-base-100 focus:border-amber-400 focus:ring focus:ring-amber-200"
                                    wire:model.live="filters.date_from">
                                <input type="date" class="input input-bordered input-sm w-full bg-base-100 focus:border-amber-400 focus:ring focus:ring-amber-200"
                                    wire:model.live="filters.date_to">
                            </div>
                        </div>

                        <div class="divider my-1"></div>
                        <div class="flex justify-center">
                            <button class="btn btn-sm bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 px-6" wire:click="resetFilters"
                                @click="expense = false; income = false">
                                Reset Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Button -->
                <label class="btn btn-sm shadow-md bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white border-0" x-transition
                    @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.add', modelId: 12}), showSearchBar = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span :class="detailSidebarOpen ? 'hidden' : ''">New</span>
                </label>
            </div>

            <!-- Search Component -->
            <div>
                <!-- Search Bar -->
                <label class="input" x-show="showSearchBar" @click.away="showSearchBar = false">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none"
                            stroke="currentColor">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </g>
                    </svg>
                    <input type="text" placeholder="Search transactions..."
                        wire:model.live.debounce.300ms="filters.search" />
                    <kbd class="kbd kbd-sm">⌘</kbd>
                    <kbd class="kbd kbd-sm">K</kbd>
                </label>

                <div class="flex" @click="showSearchBar = true" x-show="!showSearchBar">
                    <label class="btn btn-sm bg-sky-100 hover:bg-sky-200 text-sky-700 border border-sky-300 shadow-sm" x-transition>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5 text-sky-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>