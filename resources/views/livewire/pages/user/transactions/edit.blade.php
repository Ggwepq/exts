<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\Type;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Livewire\Actions\User\Balance;
use Masmerise\Toaster\Toaster;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public Transaction $transaction;
    public Transaction $oldTransaction;
    public Balance $balance;
    public $selectedTags = [];

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('nullable|string|max:500')]
    public $description;

    #[Validate('required|numeric|min:0.01')]
    public $amount; // 2MB max

    #[Validate('nullable|image')]
    public $image;

    #[Validate('required|exists:accounts,id')]
    public $account_id;

    #[Validate('nullable|exists:transaction_categories,id')]
    public $category_id;

    #[Validate('nullable|exists:transaction_categories,id')]
    public $recurring_id;

    #[Validate('required')]
    public $type_id;

    public $accounts;
    public $incomes;
    public $expenses;

    public function mount(?int $modelId = null)
    {
        $transaction = Transaction::with('tags')->findOrFail($modelId);
        $this->transaction = $transaction;
        $this->oldTransaction = $transaction;

        $this->reloadTransaction();
    }

    public function reloadTransaction()
    {
        $this->name = $this->transaction->name;
        $this->description = $this->transaction->description;
        $this->amount = $this->transaction->amount;
        $this->account_id = $this->transaction->account_id;
        $this->category_id = $this->transaction->category_id;
        $this->type_id = $this->transaction->type_id;
        $this->image = $this->transaction->image;
        $this->selectedTags = $this->transaction->tags->pluck('id')->toArray();

        $this->dropdowns();
    }

    public function dropdowns()
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        $this->incomes = TransactionCategory::where('user_id', Auth::id())->where('type_id', 1)->get();
        $this->expenses = TransactionCategory::where('user_id', Auth::id())->where('type_id', 2)->get();
    }

    public function delete()
    {
        // Get the transaction details before deleting
        $account = Account::find($this->transaction->account_id);
        $type_id = $this->transaction->type_id;
        $amount = $this->transaction->amount;

        // Update the account balance before deleting the transaction
        if ($type_id == 1) {
            // Income
            // Check if removing this income would cause a negative balance
            if ($account->balance >= $amount) {
                $account->balance -= $amount;
            } else {
                // Handle the edge case - set to zero or minimum allowed balance
                $account->balance = 0;
                Toaster::warning('Account balance was set to 0 as it would have gone negative.');
            }
        } else {
            // Expense - add the amount back
            $account->balance += $amount;
        }
        $account->save();

        // Delete the transaction
        $this->transaction->delete();

        // Dispatch events
        $this->dispatch('transactionUpdate');
        $this->dispatch('accountUpdate');

        Toaster::success('Transaction Deleted!');
    }

    #[On('update-selected-tags')]
    public function handleTagUpdate($tags)
    {
        // Just update the selected tags without saving
        $this->selectedTags = $tags;
    }

    public function save()
    {
        $oldTransaction = $this->transaction;

        $this->validate();

        if ($this->account_id != $this->oldTransaction->account_id) {
            $currentAccount = Account::find($this->account_id);
        } else {
            $currentAccount = $this->oldTransaction->accounts;
        }

        // Check for sufficient balance if it's an expense transaction
        if ($this->type_id == 2) {
            // Expense type
            $accountBalance = $currentAccount->balance;

            // If this is an update of an existing expense, add back the old amount to the balance check
            if ($this->oldTransaction->type_id == 2 && $this->account_id == $this->oldTransaction->account_id) {
                $accountBalance += $this->oldTransaction->amount;
            }

            if ($accountBalance < $this->amount) {
                Toaster::error('Insufficient Account Balance');
                return;
            }
        }

        $imagePath = $this->image == null ? $this->transaction->image_url : $this->image->store('img/transactions', 'local');

        DB::transaction(function () use ($imagePath, $currentAccount) {
            $this->transaction->update([
                'user_id' => Auth::id(),
                'account_id' => $currentAccount->id,
                'category_id' => $this->category_id == null ? 1 : $this->category_id,
                'recurring_id' => $this->recurring_id,
                'type_id' => $this->type_id ? 2 : 1,
                'name' => $this->name,
                'description' => $this->description,
                'amount' => $this->amount,
                'image_url' => $imagePath,
                'updated_at' => now(),
            ]);

            Toaster::success('Transaction Updated!');

            // Sync tags only when explicitly saving
            $this->transaction->tags()->sync($this->selectedTags);

            // If the transaction is being moved to a different account, update both accounts
            if ($this->account_id != $this->oldTransaction->account_id) {
                $previousAccount = Account::find($this->oldTransaction->account_id);
                // Revert the effect of the old transaction on the previous account
                if ($this->oldTransaction->type_id == 1) {
                    // Income
                    $previousAccount->balance -= $this->oldTransaction->amount;
                } else {
                    // Expense
                    $previousAccount->balance += $this->oldTransaction->amount;
                }
                $previousAccount->save();

                // Add the effect of the transaction to the new account
                if ($this->type_id == 1) {
                    // Income
                    $currentAccount->balance += $this->amount;
                } else {
                    // Expense
                    $currentAccount->balance -= $this->amount;
                }
                $currentAccount->save();
            } else {
                // Same account, but possibly different type or amount
                // First, revert the old transaction
                if ($this->oldTransaction->type_id == 1) {
                    // Old was Income
                    $currentAccount->balance -= $this->oldTransaction->amount;
                } else {
                    // Old was Expense
                    $currentAccount->balance += $this->oldTransaction->amount;
                }

                // Then add the new transaction
                if ($this->type_id == 1) {
                    // New is Income
                    $currentAccount->balance += $this->amount;
                } else {
                    // New is Expense
                    $currentAccount->balance -= $this->amount;
                }
                $currentAccount->save();
            }
        });

        $this->reloadTransaction();
        $this->dispatch('transactionUpdate');
        $this->dispatch('accountUpdate');
    }
};

?>

<section>
    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="alert alert-soft alert-success mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span> {{ session('message') }}</span>
        </div>
    @endif

    <!-- Error Message -->
    @if (session()->has('error'))
        <div class="alert alert-soft alert-error mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span> {{ session('error') }}</span>
        </div>
    @endif

    <!-- Form -->
    <form wire:submit="save" class="space-y-5" x-data="{ expense: $wire.type_id == 1 ? false : true }">
        <!-- Name -->
        <div class="flex flex-row items-end mb-5 gap-x-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        :class="expense ? 'text-secondary' : 'text-primary'" stroke="currentColor"
                        class="size-4 text-base-content/70">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span class="text-sm font-semibold " :class="expense ? 'text-secondary' : 'text-primary'">
                        {{ \Carbon\Carbon::parse($transaction->created_at)->format('F j, Y') }}
                    </span>
                </div>

                <input id="name" type="text" wire:model="name" placeholder="Name"
                    :class="expense ? 'text-secondary' : 'text-primary'"
                    class="input input-ghost input-xl font-bold text-4xl" required autocomplete="name" />
            </div>
            <div class="flex flex-col gap-3">
                <div>
                    <input type="checkbox" {{ $type_id == 1 ? "checked='checked'" : '' }} wire:model.live="type_id"
                        class="toggle border-secondary bg-secondary checked:bg-primary checked:text-primary checked:border-primary"
                        @click="expense = !expense; console.log(expense)" />
                    <span x-text="expense ? 'Expense' : 'Income'"
                        :class="expense ? 'text-secondary' : 'text-primary'"></span>
                </div>

                <label class="input input-ghost font-semibold text-xl"
                    :class="expense ? 'text-secondary' : 'text-primary'">
                    <span class="label">₱</span>
                    <input id="amount" type="text" wire:model="amount" placeholder="0.00"step="0.01" required
                        autocomplete="amount" />
                </label>
            </div>
        </div>

        <div class="flex flex-row gap-4">

            <div class="grow">
                <select id="account_id" wire:model="account_id" class="select select-ghost w-full text-primary"
                    :class="expense ? 'text-secondary' : 'text-primary'" autocomplete="account">
                    <option value="">Account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                @error('account_id')
                    <span class="text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="grow">
                <select id="category_id" wire:model="category_id" class="select select-ghost w-full"
                    :class="expense ? 'text-secondary' : 'text-primary'">
                    <option value="1">Category</option>
                    @if ($type_id == 1)
                        @if ($incomes)
                            @foreach ($incomes as $income)
                                @if ($income->name !== 'None')
                                    <option value="{{ $income->id }}">{{ $income->name }}</option>
                                @endif
                            @endforeach
                        @endif
                    @else
                        @if ($expenses)
                            @foreach ($expenses as $expense)
                                @if ($expense->name !== 'None')
                                    <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                @endif
                            @endforeach
                        @endif
                    @endif
                </select>
                @error('category_id')
                    <span class="text-error">{{ $message . ' ' . $category_id }}</span>
                @enderror
            </div>
        </div>

        <!-- Description -->
        <div class="form-control" :class="expense ? 'text-secondary' : 'text-primary'">
            <label class="label mb-2" for="description">
                <span class="label-text text-sm">Description</span>
            </label>
            <textarea id="description" wire:model="description" placeholder="..." class="textarea textarea-bordered w-full"
                :class="expense ? 'textarea-secondary' : 'textarea-primary'" autocomplete="description"></textarea>
            @error('description')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Image Upload -->
        <div class="form-control" :class="expense ? 'text-secondary' : 'text-primary'">
            <div class="label mb-2 flex items-end justify-between" for="image">
                <div>
                    <span class="label-text text-sm">Attach Image</span>
                    <span class="loading loading-spinner loading-xs" wire:loading wire:target="image"></span>
                </div>
                @if ($transaction->image_url)
                    <div class="avatar"
                        @click="$dispatch('open-image-viewer', '{{ asset('app/' . $transaction->image_url) }}')">
                        <div class="w-10 rounded">
                            <img src="{{ asset('app/' . $transaction->image_url) }}" alt="Transaction Receipt" />
                        </div>
                    </div>
                @endif
            </div>
            <input id="image" type="file" wire:model="image" class="file-input file-input-bordered w-full"
                :class="expense ? 'file-input-secondary' : 'file-input-primary'" accept="image/*" />
            @error('image')
                <span class="text-error">{{ $message }}</span>
            @enderror

            @if ($image)
                <div class="avatar mt-2" @click="$dispatch('open-image-viewer', '{{ $image->temporaryUrl() }}')">
                    <div class="w-10 rounded">
                        <img src="{{ $image->temporaryUrl() }}" alt="Transaction Image" />
                    </div>
                </div>
            @endif

        </div>

        <!-- Tags -->
        <div class="form-control" :class="expense ? 'text-secondary' : 'text-primary'">
            <livewire:components.tag-manager :initialSelectedTags="$selectedTags" wire:key="tag-manager" wire:model="selectedTags" />
        </div>


        <!-- Submit Button -->
        <div class="form-control">
            <button type="submit" class="btn w-full" @click="$wire.type_id = expense"
                :class="expense ? 'btn-secondary' : 'btn-primary'">Save Transaction</button>
        </div>
    </form>

    <div x-data="{ isDelete: false }" class="mt-4">
        <template x-if="!isDelete">
            <button @click="isDelete = true" class="btn btn-error w-full">Delete Transaction<span
                    wire:loading.class="loading loading-bars loading-lg"></span></button>
        </template>
        <template x-if="isDelete">
            <div class="flex flex-row gap-x-2">
                <button @click="isDelete = false" class="flex-1 btn btn-neutral">Cancel</button>
                <button class="btn btn-error flex-1" wire:click="delete"
                    @click="setTimeout(() => detailSidebarOpen = false, 1000)">Delete<span
                        class="loading loading-bars loading-lg" wire:loading></span></button>
            </div>
        </template>
    </div>
</section>
