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
            'account_id' => [],
            'date_mode' => '',
            'search' => '',
        ];
        $this->sortField = 'created_at';
        $this->sortDirection = 'DESC';
        $this->dispatch('filters-updated', $this->filters);
    }
}; ?>
<!-- Search Component -->
<div>
    <!-- Search Bar w/Click to show -->
    <label class="input" x-show="showSearchBar"
        @if (empty($filters['search'])) @click.away="showSearchBar = false" @endif>
        <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
            </g>
        </svg>
        <input type="text" placeholder="Search transactions..." wire:model.live.debounce.300ms="filters.search" />
    </label>

    <div class="flex" @click="showSearchBar = true" x-show="!showSearchBar">
        <label class="btn btn-sm bg-sky-100 hover:bg-sky-200 text-sky-700 border border-sky-300 shadow-sm" x-transition>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-5 text-sky-600">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <span>Search</span>
        </label>
    </div>
</div>
