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

    #[Validate('required|exists:types,id')]
    public $type_id;

    public $accounts;
    public $incomes;
    public $expenses;

    public function mount(?int $modelId = null)
    {
        $transaction = Transaction::with('tags')->findOrFail($modelId);
        $this->transaction = $transaction;
        $this->oldTransaction = $transaction;
        $this->name = $transaction->name;
        $this->description = $transaction->description;
        $this->amount = $transaction->amount;
        $this->account_id = $transaction->account_id;
        $this->category_id = $transaction->category_id;
        $this->type_id = $transaction->type_id;
        $this->image = $transaction->image;
        $this->selectedTags = $transaction->tags->pluck('id')->toArray();

        $this->dropdowns();
    }

    public function dropdowns()
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        $this->incomes = TransactionCategory::where('user_id', Auth::id())->where('type_id', 1)->get();
        $this->expenses = TransactionCategory::where('user_id', Auth::id())->where('type_id', 2)->get();
    }

    public function placeholder()
    {
        return view('livewire.pages.user.components.placeholders.details-placeholder');
    }

    public function delete()
    {
        // Get the transaction details before deleting
        $account = Account::find($this->transaction->account_id);
        $type_id = $this->transaction->type_id;
        $amount = $this->transaction->amount;
        
        // Update the account balance before deleting the transaction
        if ($type_id == 1) { // Income
            // Check if removing this income would cause a negative balance
            if ($account->balance >= $amount) {
                $account->balance -= $amount;
            } else {
                // Handle the edge case - set to zero or minimum allowed balance
                $account->balance = 0;
                session()->flash('warning', 'Account balance was set to 0 as it would have gone negative.');
            }
        } else { // Expense - add the amount back
            $account->balance += $amount;
        }
        $account->save();
        
        // Delete the transaction
        $this->transaction->delete();
        
        // Dispatch events
        $this->dispatch('transactionUpdate');
        $this->dispatch('accountUpdate');
        
        session()->flash('message', 'Transaction deleted successfully!');
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
        if ($this->type_id == 2) { // Expense type
            $accountBalance = $currentAccount->balance;
            
            // If this is an update of an existing expense, add back the old amount to the balance check
            if ($this->oldTransaction->type_id == 2 && $this->account_id == $this->oldTransaction->account_id) {
                $accountBalance += $this->oldTransaction->amount;
            }
            
            if ($accountBalance < $this->amount) {
                session()->flash('error', 'Insufficient Balance');
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
                'type_id' => $this->type_id,
                'name' => $this->name,
                'description' => $this->description,
                'amount' => $this->amount,
                'image_url' => $imagePath,
                'updated_at' => now(),
            ]);

            // Sync tags only when explicitly saving
            $this->transaction->tags()->sync($this->selectedTags);

            // If the transaction is being moved to a different account, update both accounts
            if ($this->account_id != $this->oldTransaction->account_id) {
                $previousAccount = Account::find($this->oldTransaction->account_id);
                // Revert the effect of the old transaction on the previous account
                if ($this->oldTransaction->type_id == 1) { // Income
                    $previousAccount->balance -= $this->oldTransaction->amount;
                } else { // Expense
                    $previousAccount->balance += $this->oldTransaction->amount;
                }
                $previousAccount->save();
                
                // Add the effect of the transaction to the new account
                if ($this->type_id == 1) { // Income
                    $currentAccount->balance += $this->amount;
                } else { // Expense
                    $currentAccount->balance -= $this->amount;
                }
                $currentAccount->save();
            } else {
                // Same account, but possibly different type or amount
                // First, revert the old transaction
                if ($this->oldTransaction->type_id == 1) { // Old was Income
                    $currentAccount->balance -= $this->oldTransaction->amount;
                } else { // Old was Expense
                    $currentAccount->balance += $this->oldTransaction->amount;
                }
                
                // Then add the new transaction
                if ($this->type_id == 1) { // New is Income
                    $currentAccount->balance += $this->amount;
                } else { // New is Expense
                    $currentAccount->balance -= $this->amount;
                }
                $currentAccount->save();
            }
        });

        $this->oldTransaction = $this->transaction;
        $this->dispatch('transactionUpdate');
        $this->dispatch('accountUpdate');

        session()->flash('message', 'Transaction Edited Successfully!');
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
    <form wire:submit.prevent="save" class="space-y-5">
        <!-- Name -->
        <div class="form-control">
            <label class="label" for="name">
                <span class="label-text">Name</span>
            </label>
            <input id="name" type="text" wire:model="name" placeholder="Name"
                class="input input-bordered w-full" required />
            @error('name')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Description -->
        <div class="form-control">
            <label class="label" for="description">
                <span class="label-text">Description (Optional)</span>
            </label>
            <textarea id="description" wire:model="description" placeholder="..." class="textarea textarea-bordered w-full"
                autocomplete="description"></textarea>
            @error('description')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Amount -->
        <div class="form-control">
            <label class="input w-full">
                <span class="label">₱</span>
                <input id="amount" type="number" wire:model="amount" placeholder="0.00"step="0.01" required
                    autocomplete="amount" />
            </label>
            @error('amount')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Account -->
        <div class="form-control">
            <label class="label" for="account_id">
                <span class="label-text">Account</span>
            </label>
            <select id="account_id" wire:model="account_id" class="select select-bordered w-full"
                autocomplete="account">
                <option value="">Select an account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            @error('account_id')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Type -->
        <div class="form-control">

            <div class="filter">
                <input class="btn filter-reset" type="radio" name="metaframeworks" aria-label="All" />
                <input class="btn checked:bg-secondary " type="radio" wire:model.live="type_id" value="2"
                    name="metaframeworks" aria-label="Expense" />
                <input class="btn checked:bg-primary " type="radio" wire:model.live="type_id" value="1"
                    name="metaframeworks" aria-label="Income" />
            </div>
            @error('type_id')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Category -->
        <div class="form-control">
            <label class="label" for="category_id">
                <span class="label-text">Category</span>
            </label>
            <select id="category_id" wire:model="category_id" class="select select-bordered w-full">
                <option>None</option>
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

        <div class="">
            <div class="avatar">
                <div class="w-24 rounded-xl">
                    <img
                        src="{{ $transaction->image_url ? asset('app/' . $transaction->image_url) : asset('img/default-img.png') }}" />
                </div>
            </div>
        </div>

        <!-- Image Upload -->
        <div class="form-control">
            <label class="label" for="image">
                <span class="label-text">Receipt Image</span>
            </label>
            
            @if($transaction->image_url)
            <div class="mb-2">
                <img 
                    src="{{ asset('app/' . $transaction->image_url) }}" 
                    alt="Transaction Receipt" 
                    class="max-w-full h-auto rounded-lg cursor-pointer border border-base-300 hover:border-primary transition-colors" 
                    style="max-height: 200px;" 
                    @click="$dispatch('open-image-viewer', '{{ asset('app/' . $transaction->image_url) }}')"
                />
                <div class="text-xs text-base-content/70 mt-1">Click the image to view in full size</div>
            </div>
            @endif
            
            <input id="image" type="file" wire:model="image" class="file-input file-input-bordered w-full" />
            @error('image')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Tags -->
        <div class="form-control">
            <livewire:components.tag-manager 
                :initial-selected-tags="$selectedTags" 
                :key="'tag-manager-' . $transaction->id" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-full">
            Save Transaction
            <span wire:loading.class="loading loading-bars loading-lg" wire:target="save"></span>
        </button>
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
                        wire:loading.class="loading loading-bars loading-lg" wire:target="delete"></span></button>
            </div>
        </template>
    </div>
</section>
