<?php

use Livewire\Volt\Component;
use App\Models\Account;
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $accounts = [];
    public $categories = [];
    public $filters = [
        'types' => [],
        'account_id' => [],
        'category_id' => [],
        'date_mode' => '',
        'search' => '',
        'sort' => [
            'field' => 'created_at',
            'direction' => 'DESC',
        ],
    ];

    public function mount($filters = null)
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        $this->categories = TransactionCategory::where('user_id', Auth::id())->get();
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
            'account_id' => [],
            'category_id' => [],
            'date_mode' => '',
            'search' => '',
        ];
        $this->sortField = 'created_at';
        $this->sortDirection = 'DESC';
        $this->dispatch('filters-updated', $this->filters);
    }

    public function applyFilters()
    {
        $this->dispatch('filters-updated', $this->filters);
    }

    public function filterBy($field, $value)
    {
        switch ($field) {
            case 'account':
                if (in_array($value, $this->filters['account_id'])) {
                    $this->filters['account_id'] = array_filter($this->filters['account_id'], fn($id) => $id !== $value);
                } else {
                    $this->filters['account_id'][] = $value;
                }

                break;

            case 'category':
                if (in_array($value, $this->filters['category_id'])) {
                    $this->filters['category_id'] = array_filter($this->filters['category_id'], fn($id) => $id !== $value);
                } else {
                    $this->filters['category_id'][] = $value;
                }

                break;

            case 'type':
                if (in_array($value, $this->filters['types'])) {
                    // Remove it
                    $this->filters['types'] = array_filter($this->filters['types'], fn($type) => $type !== $value);
                } else {
                    // Add it
                    $this->filters['types'][] = $value;
                }
                break;

            case 'date':
                if (in_array($value, ['daily', 'weekly', 'monthly', 'yearly'])) {
                    $this->filters['date_mode'] = $value;
                }
                break;
        }

        $this->dispatch('filters-updated', $this->filters);
    }
}; ?>

<!-- Filter Button -->
<div class="dropdown"
    :class="detailSidebarOpen ? 'dropdown-start md:dropdown-end' : 'dropdown-start md:dropdown-center'">
    <label tabindex="0"
        class="btn btn-sm bg-amber-100 hover:bg-amber-200 text-amber-700 border border-amber-300 shadow-sm font-medium"
        aria-label="Filter">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-6 mr-1 text-amber-600">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
        </svg>
        <!-- <div :class="detailSidebarOpen ? 'hidden' : ''"> -->
        <!--     <span>Filters</span> -->
        <!--     <span class="badge bg-amber-500 text-white badge-xs ml-1"> -->
        <!--         {{ count($filters['types']) + count($filters['account_id']) + ($filters['date_mode'] ? 1 : 0) + ($filters['search'] ? 1 : 0) }} -->
        <!--     </span> -->
        <!-- </div> -->
        <div>
            <span>Filters</span>
            <span class="badge bg-amber-500 text-white badge-xs ml-1">
                {{ count($filters['types']) + count($filters['account_id']) + ($filters['date_mode'] ? 1 : 0) + ($filters['search'] ? 1 : 0) }}
            </span>
        </div>
    </label>

    <!-- Filter dropdown content -->
    <div tabindex="0"
        class="dropdown-content z-[1] menu p-4 mt-4 shadow-lg bg-base-100 rounded-xl w-60 border border-base-200">
        <h3 class="font-bold text-md mb-2">Filter Transactions</h3>

        <li>
            <details class="mb-1">
                <summary
                    class="flex items-center justify-between cursor-pointer hover:bg-base-200 transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <span>Type</span>
                    </div>

                    @if ($filters['types'])
                        <span class="badge badge-xs badge-primary p-3">
                            @if (in_array(1, $filters['types']) && in_array(2, $filters['types']))
                                Both
                            @elseif(in_array(2, $filters['types']))
                                Exp
                            @else
                                Inc
                            @endif
                        </span>
                    @endif
                </summary>
                <ul class="ml-2 mt-1.5">
                    <li class="text-6sm">
                        @foreach (['Income', 'Expense'] as $type)
                            <a wire:click="filterBy('type', '{{ $type == 'Income' ? 1 : 2 }}')"
                                class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ in_array($type == 'Income' ? 1 : 2, $filters['types']) ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                                <span class="flex space-x-1 items-center group-hover:text-primary">
                                    <span class="truncate ">{{ $type }}</span>
                                </span>
                            </a>
                        @endforeach
                    </li>
                </ul>
            </details>

            <details>
                <summary
                    class="flex items-center justify-between cursor-pointer hover:bg-base-200 transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <span>Date</span>
                    </div>

                    @if ($filters['date_mode'])
                        <span class="badge badge-xs badge-primary p-3">
                            @if ($filters['date_mode'] == 'daily')
                                Daily
                            @elseif($filters['date_mode'] == 'monthly')
                                Monthly
                            @else
                                Yearly
                            @endif
                        </span>
                    @endif
                </summary>
                <ul class="ml-2 mt-1.5">
                    <li class="text-6sm">
                        @foreach (['daily', 'weekly', 'monthly', 'yearly'] as $date)
                            <a wire:click="filterBy('date', '{{ $date }}')"
                                class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ $filters['date_mode'] == $date ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                                <span class="flex space-x-1 items-center group-hover:text-primary">
                                    <span class="truncate ">{{ ucfirst($date) }}</span>
                                </span>
                            </a>
                        @endforeach
                    </li>
                </ul>
            </details>


            <details>
                <summary
                    class="flex items-center justify-between cursor-pointer hover:bg-base-200 transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <span>Account</span>
                    </div>

                    @if ($filters['account_id'])
                        <span class="badge badge-xs badge-primary p-3">
                            @if (count($filters['account_id']) > 1)
                                ...
                            @else
                                @foreach ($accounts as $account)
                                    @if (in_array($account->id, $filters['account_id']))
                                        {{ $account->name }}
                                    @endif
                                @endforeach
                            @endif
                        </span>
                    @endif
                </summary>
                <ul class="ml-2 mt-1.5">
                    <li class="text-6sm">

                        @foreach ($accounts as $account)
                            <a wire:click="filterBy('account', '{{ $account->id }}')"
                                class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ in_array($account->id, $filters['account_id']) ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                                <span class="flex space-x-1 items-center group-hover:text-primary">
                                    <span class="truncate ">{{ $account->name }}</span>
                                </span>
                            </a>
                        @endforeach
                    </li>
                </ul>
            </details>

            <details>
                <summary
                    class="flex items-center justify-between cursor-pointer hover:bg-base-200 transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <span>Category</span>
                    </div>

                    @if ($filters['category_id'])
                        <span class="badge badge-xs badge-primary p-3">
                            @if (count($filters['category_id']) > 1)
                                ...
                            @else
                                @foreach ($categories as $category)
                                    @if (in_array($category->id, $filters['category_id']))
                                        {{ $category->name }}
                                    @endif
                                @endforeach
                            @endif
                        </span>
                    @endif
                </summary>
                <ul class="ml-2 mt-1.5">
                    <li class="text-6sm">

                        @foreach ($categories as $category)
                            <a wire:click="filterBy('category', '{{ $category->id }}')"
                                class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ in_array($account->id, $filters['account_id']) ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                                <span class="flex space-x-1 items-center group-hover:text-primary">
                                    <span class="truncate ">{{ $category->name }}</span>
                                </span>
                            </a>
                        @endforeach
                    </li>
                </ul>
            </details>
        </li>



        <div class="divider my-1"></div>
        <div class="flex justify-center">

            <button wire:click="resetFilters"
                class="btn btn-sm bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 w-full">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Reset Filters
            </button>
        </div>
    </div>
</div>
