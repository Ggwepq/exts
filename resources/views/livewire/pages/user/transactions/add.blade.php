<?php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\Type;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $accounts;
    public $expenses;
    public $incomes;

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

    public function save()
    {
        $this->validate();

        // Check for sufficient balance if it's an expense transaction
        if ($this->type_id == 2) {
            // Expense type
            $account = Account::find($this->account_id);
            if ($account->balance < $this->amount) {
                session()->flash('error', 'Insufficient Balance');
                return;
            }
        }

        // Handle file upload if an image is provided
        $imagePath = $this->image ? $this->image->store('img/transactions', 'local') : null;

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'account_id' => $this->account_id,
            'category_id' => $this->category_id == null ? 1 : $this->category_id,
            'recurring_id' => $this->recurring_id,
            'type_id' => $this->type_id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'image_url' => $imagePath,
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ]);

        // Updates the Account
        $account = Account::find($transaction->account_id);
        $account->balance = $transaction->types->name == 'Expense' ? $account->balance - $transaction->amount : $account->balance + $transaction->amount;
        $account->save();

        // Reset form fields
        $this->reset(['name', 'description', 'amount', 'image', 'account_id', 'category_id', 'recurring_id', 'type_id']);

        // Emit event to refresh transaction list
        $this->dispatch('transactionUpdate');

        session()->flash('message', 'Transaction created successfully!');
    }

    public function mount()
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        $this->incomes = TransactionCategory::where('user_id', Auth::id())->where('type_id', 1)->get();
        $this->expenses = TransactionCategory::where('user_id', Auth::id())->where('type_id', 2)->get();

        // dd($accounts);
    }

    public function placeholder()
    {
        return view('livewire.pages.user.components.details-placeholder');
    }
}; ?>

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
    <form wire:submit="save" class="space-y-10">
        <!-- Name -->
        <div class="flex flex-row items-end mb-5 gap-x-4" x-data="{ expense: false }">
            <div class="">
                <input id="name" type="text" wire:model="name" placeholder="Name"
                    :class="expense ? 'text-primary' : 'text-secondary'"
                    class="input input-ghost input-xl font-bold text-4xl" required autocomplete="name" />
            </div>
            <div class="flex flex-col gap-3">
                <div>
                    <input type="checkbox" checked="checked" wire:model.live="type_id"
                        class="toggle border-secondary bg-secondary checked:bg-primary checked:text-primary checked:border-primary"
                        @click="expense = !expense; console.log(expense)" />
                    <span x-text="expense ? 'Expense' : 'Income'"
                        :class="expense ? 'text-primary' : 'text-secondary'"></span>
                </div>

                <label class="input input-ghost font-semibold text-2xl"
                    :class="expense ? 'text-primary' : 'text-secondary'">
                    <span class="label">₱</span>
                    <input id="amount" type="text" wire:model="amount" placeholder="0.00"step="0.01" required
                        autocomplete="amount" />
                </label>
            </div>
            <div class="">
            </div>
        </div>

        <div class="flex flex-row gap-4 mt-5">

            <div class="grow">
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

            <div class="grow">
                <select id="category_id" wire:model="category_id" class="select select-bordered w-full">
                    <option value="1">Select a category</option>
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
        <div class="form-control">
            <label class="label" for="description">
                <span class="label-text">Note</span>
            </label>
            <textarea id="description" wire:model="description" placeholder="..." class="textarea textarea-bordered w-full"
                autocomplete="description"></textarea>
            @error('description')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Image Upload -->
        <div class="form-control">
            <label class="label" for="image">
                <span class="label-text">Receipt Image (Optional)</span>
                <span class="label-text-alt">Max 2MB</span>
            </label>
            <input id="image" type="file" wire:model="image" class="file-input file-input-bordered w-full"
                accept="image/*" />
            <div class="text-xs text-base-content/70 mt-1">Upload a photo of your receipt for this transaction</div>
            @error('image')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="form-control">
            <button type="submit" class="btn btn-primary w-full">Save</button>
        </div>
    </form>
</section>
