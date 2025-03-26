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

    <!-- Form -->
    <form wire:submit="save" class="space-y-5">
        <!-- Name -->
        <div class="form-control">
            <label class="label" for="name">
                <span class="label-text">Name</span>
            </label>
            <input id="name" type="text" wire:model="name" placeholder="Name"
                class="input input-bordered w-full" required autocomplete="name" />
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
                <option value="1">None</option>
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

        <!-- Image Upload -->
        <div class="form-control">
            <label class="label" for="image">
                <span class="label-text">Image (Optional)</span>
            </label>
            <input id="image" type="file" wire:model="image" class="file-input file-input-bordered w-full" />
            @error('image')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-full">Save Transaction<span
                wire:loading.class="loading loading-bars loading-lg"></span></button>
    </form>
</section>
