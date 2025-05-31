<?php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\AccountCategory;
use App\Models\Type;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $account;
    public $name;
    public $categories;
    public $transactions;
    public $modelId;

    #[Validate('nullable|exists:account_categories,id')]
    public $category_id;

    #[Validate('required|numeric|min:0.01')]
    public $amount; // 2MB max

    public function mount($modelId)
    {
        $this->modelId = $modelId;
        $account = Account::find($modelId);
        $this->account = $account;
        $this->name = $account->name;
        $this->category_id = $account->category_id;
        $this->amount = $account->balance;

        $this->categories = AccountCategory::where('user_id', Auth::id())->get();
        $this->loadTransactions();
    }

    public function loadTransactions()
    {
        $transactions = $this->account->transactions->where('name', '!=', 'Initial Account Balance')->sortByDesc('created_at');

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
        $this->account->delete();
        $this->dispatch('accountUpdate');
        Toaster::success('Account Deleted!');
    }

    public function getGroupsProperty()
    {
        return AccountCategory::where('user_id', Auth::id())->get();
    }

    public function getSelectedGroupProperty()
    {
        return collect($this->categories)->firstWhere('id', $this->category_id);
    }
}; ?>
<section>
    <!-- Form -->
    <form wire:submit="update" class="space-y-5">

        <div class="flex flex-row items-end mb-5 gap-x-4">
            <div class="flex flex-col gap-3 w-1/2">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 text-primary">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span class="text-xs font-semibold text-primary">
                        {{ Carbon::now()->format('l, F j Y') }}
                    </span>
                </div>

                <div x-data="{
                    name: @entangle('name')
                }" class="relative w-full max-w-full">

                    <!-- Display name (click to edit) -->
                    <span class="cursor-pointer font-bold text-3xl block truncate text-primary"
                        x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                    </span>


                </div>
            </div>
            <div class="flex flex-col gap-3 w-1/2 mt-2">
                <div x-data="{
                    amount: @entangle('amount'),
                    formatted() {
                        const num = parseFloat(this.amount || 0);
                        return num.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
                    }
                }" class="relative w-full max-w-xs">

                    <!-- Display formatted amount -->
                    <span class="cursor-pointer text-2xl font-semibold block truncate text-primary"
                        x-text="formatted()">
                    </span>
                </div>
            </div>
        </div>

        <!-- Group Dropdown -->
        <div class="flex flex-row gap-4 ">
            <div class="dropdown dropdown-center w-full">
                <label tabindex="0"
                    class="btn btn-md border shadow-sm w-full text-primary border-primary hover:bg-primary/50"
                    aria-label="Select Group">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <span>{{ $category_id ? $this->selectedGroup->name : 'Category' }}</span>
                </label>
            </div>
        </div>

    </form>

    <div x-data="{ isDelete: false }" class="mt-4">

        <template x-if="!isDelete">
            <button @click="isDelete = true" class="btn btn-error w-full">Delete Wallet<span
                    wire:loading.class="loading loading-bars loading-lg"></span></button>
        </template>
        <template x-if="isDelete">
            <div class="flex flex-row gap-x-2">
                <button @click="isDelete = false" class="flex-1 btn btn-neutral">Cancel
                </button>

                <button class="btn btn-error flex-1" wire:click="delete"
                    @click="setTimeout(() => detailSidebarOpen = false, 1000); isDelete = false">Delete<span
                        wire:loading.class="loading loading-bars loading-lg" wire:target="delete"></span></button>
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
