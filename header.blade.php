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
        'date_to' => ''
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
            'date_to' => ''
        ];
        $this->dispatch('filters-updated', $this->filters);
    }
}; ?>

<div class="flex flex-row">
    <div class="flex-grow">
        <h1 class="text-2xl font-semibold ml-2">Transactions</h1>
    </div>
    <div class="flex-none flex gap-2">
        <!-- Filter Button -->
        <div class="dropdown dropdown-end">
            <label tabindex="0" class="btn btn-ghost btn-circle hover:bg-base-200" aria-label="Filter">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                     stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                          d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-base-100 bg-primary"></span>
            </label>
            <div tabindex="0" class="dropdown-content z-[1] menu p-4 shadow bg-base-200 rounded-box w-80">
                <h3 class="font-bold text-lg mb-2">Filter Transactions</h3>
                <div class="divider my-1"></div>
                
                <!-- Type Filter -->
                <div class="form-control mb-3">
                    <label class="label">
                        <span class="label-text font-medium">Transaction Type</span>
                    </label>
                    <div class="flex flex-row gap-1">
                        <input type="checkbox" class="btn btn-xs btn-outline btn-primary flex-1" wire:model.live="filters.types" value="1">
                            <span>Income</span>
                        </input>
                        <input type="checkbox" class="btn btn-xs btn-outline btn-secondary flex-1" wire:model.live="filters.types" value="2">
                            <span>Expense</span>
                        </input>
                    </div>
                </div>
                
                <!-- Account Filter -->
                <div class="form-control mb-3">
                    <label class="label">
                        <span class="label-text font-medium">Account</span>
                    </label>
                    <select class="select select-bordered select-sm w-full" wire:model.live="filters.account_id">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Date Filter -->
                <div class="form-control mb-3">
                    <label class="label">
                        <span class="label-text font-medium">Date Range</span>
                    </label>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs">From:</span>
                            <input type="date" class="input input-bordered input-sm w-full" 
                                   wire:model.live="filters.date_from">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs">To:</span>
                            <input type="date" class="input input-bordered input-sm w-full" 
                                   wire:model.live="filters.date_to">
                        </div>
                    </div>
                </div>
                
                <div class="divider my-1"></div>
                <div class="flex justify-center">
                    <button class="btn btn-sm btn-ghost" wire:click="resetFilters">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Add Button -->
        <label class="btn btn-primary"
            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.add', modelId: 12})">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </label>
    </div>
</div>
