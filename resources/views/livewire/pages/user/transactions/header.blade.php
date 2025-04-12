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
    ];

    public function mount($filters = null)
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        if ($filters) {
            $this->filters = $filters;
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
        ];
        $this->dispatch('filters-updated', $this->filters);
    }

    public function applyFilters()
    {
        $this->dispatch('filters-updated', $this->filters);
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
        <div class="flex gap-2 items-center">
            <!-- Search Bar -->
            <div class="form-control mr-2 relative">
                <input 
                    type="text" 
                    placeholder="Search transactions..." 
                    class="input input-sm input-bordered w-full md:w-48 lg:w-64 pr-10"
                    wire:model.live.debounce.300ms="filters.search" 
                />
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            
            <div class="flex gap-2 items-center">
                <!-- Filter Button -->
                <div class="dropdown"
                    :class="detailSidebarOpen ? 'dropdown-start md:dropdown-end' : 'dropdown-start md:dropdown-center'"
                    x-data="{ expense: false, income: false }">
                    <label tabindex="0"
                        class="btn btn-ghost btn-sm bg-base-100 hover:bg-base-200 border border-base-300 shadow-sm"
                        aria-label="Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                        <div :class="detailSidebarOpen ? 'hidden' : ''">
                            <span>Filters</span>
                            <span class="badge badge-primary badge-xs ml-1">
                                {{ count($filters['types']) + ($filters['account_id'] ? 1 : 0) + ($filters['date_from'] ? 1 : 0) + ($filters['date_to'] ? 1 : 0) + ($filters['search'] ? 1 : 0) }}
                            </span>
                        </div>
                    </label>
                    <div tabindex="0"
                        class="dropdown-content z-[1] menu p-4 mt-4 shadow-lg bg-base-100 rounded-xl w-80 border border-base-200">
                        <h3 class="font-bold text-lg mb-2">Filter Transactions</h3>

                        <!-- Type Filter -->
                        <div class="form-control mb-3">
                            <div class="flex flex-row gap-1">
                                <label class="flex-1 cursor-pointer">
                                    <input type="checkbox" class="hidden" wire:model.live="filters.types"
                                        value="1">
                                    <div class="btn btn-sm btn-primary w-full" @click="income = !income"
                                        :class="!income ? 'btn-outline' : ''">Income</div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="checkbox" class="hidden" wire:model.live="filters.types"
                                        value="2">
                                    <div class="btn btn-sm btn-secondary w-full" @click="expense = !expense"
                                        :class="!expense ? 'btn-outline' : ''">Expense</div>
                                </label>
                            </div>
                        </div>

                        <!-- Account Filter -->
                        <div class="form-control mb-3">
                            <select class="select select-bordered select-sm w-full bg-base-100"
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
                                <input type="date" class="input input-bordered input-sm w-full bg-base-100"
                                    wire:model.live="filters.date_from">
                                <input type="date" class="input input-bordered input-sm w-full bg-base-100"
                                    wire:model.live="filters.date_to">
                            </div>
                        </div>

                        <div class="divider my-1"></div>
                        <div class="flex justify-center">
                            <button class="btn btn-sm btn-outline btn-primary px-6" wire:click="resetFilters"
                                @click="expense = false; income = false">
                                Reset Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Button -->
                <label class="btn btn-primary btn-sm shadow-md" x-transition
                    @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.add', modelId: 12})">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span :class="detailSidebarOpen ? 'hidden' : ''">New</span>
                </label>
            </div>
        </div>
    </div>
</div>
