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

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('nullable|exists:account_categories,id')]
    public $category_id;

    #[Validate('required|numeric|min:0.01')]
    public $amount; // 2MB max

    public function create()
    {
        $this->validate();

        Account::create([
            'user_id' => Auth::id(),
            'category_id' => $this->category_id ? $this->category_id : null,
            'name' => $this->name,
            'balance' => $this->amount,
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ]);

        // Reset form fields
        $this->reset(['name', 'amount', 'category_id']);

        // Emit event to refresh transaction list
        $this->dispatch('accountCreated');

        session()->flash('message', 'Account created successfully!');
    }
}; ?>

<section>
    <!-- Close Button -->
    <div class="flex mb-4">
        <h3 class="flex-1 text-xl font-bold">New Account</h3>
        <button @click="isOpen = false" class="btn btn-circle btn-ghost btn-sm flex-none">
            ✕
        </button>
    </div>

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
    <form wire:submit="create" class="space-y-5">
        <!-- Name -->
        <div class="form-control">
            <label class="label" for="name">
                <span class="label-text">Name</span>
            </label>
            <input id="name" type="text" wire:model="name" class="input input-bordered w-full" required
                autocomplete="name" />
            @error('name')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Amount -->
        <div class="form-control">
            <label class="label" for="amount">
                <span class="label-text">Amount</span>
            </label>
            <input id="amount" type="number" wire:model="amount" class="input input-bordered w-full" step="0.01"
                required autocomplete="amount" />
            @error('amount')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Category -->
        <div class="form-control">
            <label class="label" for="category_id">
                <span class="label-text">Category</span>
            </label>
            <select id="category_id" wire:model="category_id" class="select select-bordered w-full">
                <option value="None">None</option>
                @foreach (\App\Models\AccountCategory::all() as $category)
                    @if ($category->name !== 'None')
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endif
                @endforeach
            </select>
            @error('category_id')
                <span class="text-error">{{ $message . ' ' . $category_id }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-full">Save</button>
    </form>
</section>
