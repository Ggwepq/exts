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
    public $modelId;
    public $transactions;

    #[Validate('nullable')]
    public $group_name = null;

    #[Validate('required')]
    public $type_id = false;

    public function mount(?int $modelId = null)
    {
        $this->loadCategory($modelId);
        $this->loadTransactions();
    }

    public function loadCategory($id)
    {
        $this->modelId = $id;
        $this->currentCategory = TransactionCategory::findOrFail($id);
        $this->name = $this->currentCategory->name;
        $this->group_name = $this->currentCategory->groups->name ?? 'None';
        $this->type_id = $this->currentCategory->type_id;
    }

    public function loadTransactions()
    {
        $transactions = $this->currentCategory->transactions->sortByDesc('created_at');

        $this->transactions = $transactions
            ->groupBy(function ($transaction) {
                $date = Carbon::parse($transaction->created_at);
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('F j, Y'); // April 14, 2025
                }
            })
            ->all();
    }

    public function formatShortAmount($amount)
    {
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1) . 'M';
        } elseif ($amount >= 1_000) {
            return number_format($amount / 1_000, 1) . 'K';
        }
        return number_format($amount, 2);
    }

    public function delete()
    {
        $this->currentCategory->delete();
        $this->dispatch('categoryUpdate');
        $this->dispatch('closeSidebar');
        Toaster::success('Categories Deleted!');
    }
}; ?>

<section x-data="{ expense: $wire.type_id == 1 ? false : true }">
    <!-- Form -->

    <form wire:submit="save" class="space-y-10">
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
                    <span>{{ $group_name }}</span>
                </label>
            </div>
        </div>

    </form>

    <div x-data="{ isDelete: false }" class="mt-6">
        <template x-if="!isDelete">
            <button @click="isDelete = true" class="btn btn-error w-full">Delete Transaction<span
                    wire:loading.class="loading loading-bars loading-lg"></span></button>
        </template>
        <template x-if="isDelete">
            <div class="flex flex-row gap-x-2">
                <button @click="isDelete = false" class="flex-1 btn btn-neutral">Cancel</button>
                <button class="btn btn-error flex-1" wire:click="delete" @click="isDelete = false">Delete<span
                        class="loading loading-bars loading-lg" wire:loading></span></button>
            </div>
        </template>
    </div>

    <div class="w-full mt-10">
        @if (count($transactions))
            <ul class="list bg-base-100 space-y-4">
                @foreach ($transactions as $date => $record)
                    @php
                        $totalIncome = $record->where('type_id', '1')->sum('amount');
                        $totalExpense = $record->where('type_id', '2')->sum('amount');
                    @endphp
                    <li
                        class="bg-base-200 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm flex justify-between">
                        <div class="flex items-center gap-2">
                            <!-- <input type="checkbox" class="checkbox " /> -->
                            <div class="flex items-center gap-2"
                                @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.create'}); rightSidebarOpen = true;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4 text-base-content/70">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                {{ $date }}
                            </div>
                        </div>
                        <div class="text-right text-xs w-1/2  font-semibold">
                            <span class="text-primary truncate inline-block md:hidden w-1/2 md:w-auto">
                                +₱{{ $this->formatShortAmount($totalIncome) }}
                            </span>
                            <span class="text-primary truncate hidden md:inline-block w-1/2 md:w-auto">
                                +₱{{ number_format($totalIncome, 2) }}
                            </span>

                            <span class="text-secondary truncate inline-block md:hidden w-1/2 md:w-auto">
                                -₱{{ $this->formatShortAmount($totalExpense) }}
                            </span>
                            <span class="text-secondary truncate hidden md:inline-block w-1/2 md:w-auto">
                                -₱{{ number_format($totalExpense, 2) }}
                            </span>
                        </div>
                    </li>

                    @foreach ($record as $transaction)
                        <li
                            class="group list-row hover:bg-base-200 flex items-center justify-between w-full p2-5 py-4 border border-base-200 mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer">
                            <!-- Red for Expense, Green for Income -->
                            <div class="flex flex-row md:items-center w-full grow"
                                @click="$dispatch('showRightSidebar', {operation: 'view', page: 'Transaction', component: 'pages.user.transactions.view', modelId: {{ $transaction->id }}}); rightSidebarOpen = true;">
                                <!-- Transaction Name -->
                                <div
                                    class="w-1/3 truncate font-bold text-md mb-1.5 mr-2 text-base-content transition-colors duration-200 {{ $transaction->types->name == 'Expense' ? 'group-hover:text-secondary' : 'group-hover:text-primary' }}">
                                    {{ $transaction->name }}
                                </div>

                                <!-- Account Name -->
                                <div class="w-1/3 truncate uppercase font-semibold hidden md:flex">
                                    <span
                                        class="text-xs badge badge-lg badge-outline {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                        {{ $transaction->accounts->name }}
                                    </span>
                                </div>

                                <!-- Amount -->
                                <div class="w-1/3 flex-shrink-0 text-right grow">
                                    <span
                                        class="text-xs uppercase font-semibold badge badge-lg truncate w-3/4 md:w-auto  {{ $transaction->types->name == 'Expense' ? 'badge-secondary ' : 'badge-primary ' }}">
                                        <span class="md:hidden">
                                            {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ $this->formatShortAmount($transaction->amount) }}
                                        </span>
                                        <span class="hidden md:inline">
                                            {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                @endforeach
            </ul>
        @else
            <div class="flex flex-col items-center justify-center p-10 bg-base-200/30  border border-dashed rounded-xl "
                :class="$wire.type_id == 1 ? 'border-primary' : 'border-secondary'">
                <span class="text-base-content text-lg font-medium mb-1">
                    😴 No transactions found
                </span>
            </div>
        @endif
    </div>

</section>
