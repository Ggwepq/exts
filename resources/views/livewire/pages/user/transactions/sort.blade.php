<?php

use Livewire\Volt\Component;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $accounts = [];
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
    public $sortField = 'created_at';
    public $sortDirection = 'DESC';

    public function mount($filters = null)
    {
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
            'date_mode' => '',
            'search' => '',
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
            'direction' => 'DESC',
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
            'direction' => $direction,
        ];
        $this->dispatch('sort-updated', $field, $direction);
    }
}; ?>
<!-- Sort Button -->
<div class="dropdown dropdown-start md:dropdown-center">
    <label tabindex="0"
        class="btn btn-sm bg-indigo-100 hover:bg-indigo-200 text-indigo-700 border border-indigo-300 shadow-sm font-medium"
        aria-label="Sort">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-6 mr-1 text-indigo-600">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12" />
        </svg>
        <span>Sort</span>
    </label>
    <div tabindex="0"
        class="dropdown-content z-[1] menu p-3 mt-4 shadow-lg bg-base-100 rounded-xl w-60 border border-base-200">
        <h3 class="font-bold text-md mb-2">Sort Transactions</h3>
        <li>
            <details class="mb-1">
                <summary
                    class="flex items-center justify-between cursor-pointer hover:bg-base-200 transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        <span>Amount</span>
                    </div>
                    @if ($sortField == 'amount')
                        <span class="badge badge-xs badge-primary p-3">
                            @if ($sortDirection == 'ASC')
                                ASC
                            @else
                                DESC
                            @endif
                        </span>
                    @endif
                </summary>
                <ul class="ml-2 mt-1.5">
                    <li class="text-6sm">
                        <a wire:click="sortBy('amount', 'ASC')"
                            class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ $sortField === 'amount' && $sortDirection === 'ASC' && $sortField !== null ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                            <span class="flex space-x-1 items-center group-hover:text-primary">
                                <span class="truncate ">Low to High</span>
                            </span>
                        </a>
                        <a wire:click="sortBy('amount', 'DESC')"
                            class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ $sortField === 'amount' && $sortDirection === 'DESC' && $sortField !== null ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                            <span class="flex space-x-1 items-center group-hover:text-primary">
                                <span class="truncate ">High to Low</span>
                            </span>
                        </a>
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
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        <span>Date</span>
                    </div>

                    @if ($sortField == 'created_at')
                        <span class="badge badge-xs badge-primary p-3">
                            @if ($sortDirection == 'ASC')
                                ASC
                            @else
                                DESC
                            @endif
                        </span>
                    @endif
                </summary>
                <ul class="ml-2 mt-1.5">
                    <li class="text-6sm">
                        <a wire:click="sortBy('created_at', 'ASC')"
                            class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ $sortField === 'created_at' && $sortDirection === 'ASC' && $sortField !== null ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                            <span class="flex space-x-1 items-center group-hover:text-primary">
                                <span class="truncate ">Oldest</span>
                            </span>
                        </a>
                        <a wire:click="sortBy('created_at', 'DESC')"
                            class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ $sortField === 'created_at' && $sortDirection === 'DESC' && $sortField !== null ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                            <span class="flex space-x-1 items-center group-hover:text-primary">
                                <span class="truncate ">Newest</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </details>
        </li>
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
