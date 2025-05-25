<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\CategoryGroup;
use App\Models\Type;
use Carbon\Carbon;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    // [Validate('required|string|max:255')]
    public $name;
    public $currentCategory;

    #[Validate('nullable')]
    public $group_id = null;

    #[Validate('required')]
    public $type_id = false;

    public function mount(?int $modelId = null)
    {
        $this->loadCategory($modelId);
    }

    public function loadCategory($id)
    {
        $this->currentCategory = TransactionCategory::findOrFail($id);
        $this->name = $this->currentCategory->name;
        $this->group_id = $this->currentCategory->group_id;
        $this->type_id = $this->currentCategory->type_id;
    }
}; ?>

<section>
    <!-- Form -->

    <form wire:submit="save" class="space-y-10" x-data="{ expense: $wire.type_id == 1 ? false : true }">
        <!-- name -->
        <div class="flex flex-row w-full justify-between">
            <div class="flex items-center gap-2 mb-2 ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6"
                    :class="$wire.type_id == 2 || !$wire.type_id ? 'text-secondary' : 'text-primary'">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                </svg>
                <span class="text-xs font-semibold"
                    :class="$wire.type_id == 2 || !$wire.type_id ? 'text-secondary' : 'text-primary'">
                    {{ carbon::now()->format('l, F j Y') }}
                </span>
            </div>

            <div>
                <input type="checkbox" :checked="$wire.type_id == 1" wire:model.live="type_id"
                    class="toggle border-secondary bg-secondary checked:bg-primary checked:text-primary checked:border-primary"
                    @click="expense = !expense; $wire.category_id = ''" disabled />
                <span x-text="$wire.type_id == 2 || !$wire.type_id  ? 'Expense' : 'Income'"
                    :class="$wire.type_id == 2 || !$wire.type_id ? 'text-secondary' : 'text-primary'"></span>
            </div>
        </div>
        <div class="flex flex-col gap-3 mt-2">

            <div x-data="{
                name: @entangle('name')
            }" class="relative w-full max-w-full">

                <!-- display name (click to edit) -->
                <span class="cursor-pointer font-bold text-3xl block truncate text-center"
                    :class="$wire.type_id == 2 || !$wire.type_id ? 'text-secondary' : 'text-primary'"
                    x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                </span>
            </div>
            @error('name')
                <span class="validator-hint">{{ $message }}</span>
            @enderror
        </div>
        <div class="flex flex-row gap-4 ">

            <div class="dropdown dropdown-center w-full">
                <label tabindex="0" class="btn btn-md border shadow-sm w-full" aria-label="Select Group"
                    :class="$wire.type_id == 2 || !$wire.type_id ? 'text-secondary border-secondary hover:bg-secondary/50' :
                        'text-primary border-primary hover:bg-primary/50'">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <span>{{ $group_id ? $this->selectedGroup->name : 'Group' }}</span>
                </label>
                @error('account_id')
                    <span class="validator-hint">{{ $message }}</span>
                @enderror
            </div>
        </div>

    </form>
</section>
