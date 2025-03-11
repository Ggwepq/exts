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

    #[Validate('nullable|string|max:500')]
    public $description;

    #[Validate('required|numeric|min:0.01')]
    public $amount; // 2MB max

    #[Validate('nullable|image|max:2048')]
    public $image;

    #[Validate('required|exists:accounts,id')]
    public $account_id;

    #[Validate('required|exists:transaction_categories,id')]
    public $category_id;

    #[Validate('nullable|exists:transaction_categories,id')]
    public $recurring_id;

    #[Validate('required|exists:types,id')]
    public $type_id;

    public function save()
    {
        $this->validate();

        // Handle file upload if an image is provided
        $imagePath = $this->image ? $this->image->store('transactions', 'public') : null;

        Transaction::create([
            'user_id' => Auth::id(),
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'recurring_id' => $this->recurring_id,
            'type_id' => $this->type_id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'image_url' => $imagePath,
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ]);

        // Reset form fields
        $this->reset(['name', 'description', 'amount', 'image', 'account_id', 'category_id', 'recurring_id', 'type_id']);

        // Emit event to refresh transaction list
        $this->dispatch('transactionCreated');

        session()->flash('message', 'Transaction created successfully!');
    }
}; ?>

<div>
    <!-- Sidebar Backdrop (Mobile Only) -->
    <div x-show="isOpen" x-transition.duration.500 @click.away="isOpen = false"
        class="fixed inset-y-0 bg-base-300 z-40 md:hidden" wire:click="closeDetail"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:leave="transition-opacity ease-linear duration-300">
    </div>

    <!-- Sidebar -->
    <div x-show="isOpen" x-transition.duration.500 @click.away="isOpen = false"
        class="fixed md:sticky top-0 start-0 bottom-0 right-0 w-full md:w-96 bg-base-100 border-l border-base-200 shadow-lg z-50 md:relative md:transform-none transform transition-transform duration-300"
        :class="isOpen ? 'translate-x-0' : 'translate-x-full'">
        <div class="p-6 h-full overflow-y-auto">
            <!-- Close Button -->
            <div class="flex mb-4">
                <h3 class="flex-1 text-xl font-bold">New Transaction</h3>
                <button @click="isOpen = false" class="btn btn-circle btn-ghost btn-sm flex-none">
                    ✕
                </button>
            </div>

            <!-- Success Message -->
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Form -->
            <form wire:submit="save" class="space-y-4">
                <!-- Name -->
                <div class="form-control">
                    <label class="label" for="name">
                        <span class="label-text">Transaction Name</span>
                    </label>
                    <input id="name" type="text" wire:model="name" class="input input-bordered w-full"
                        required />
                    @error('name')
                        <span class="text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-control">
                    <label class="label" for="description">
                        <span class="label-text">Description (Optional)</span>
                    </label>
                    <textarea id="description" wire:model="description" class="textarea textarea-bordered w-full"></textarea>
                    @error('description')
                        <span class="text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="form-control">
                    <label class="label" for="amount">
                        <span class="label-text">Amount</span>
                    </label>
                    <input id="amount" type="number" wire:model="amount" class="input input-bordered w-full"
                        step="0.01" required />
                    @error('amount')
                        <span class="text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Account -->
                <div class="form-control">
                    <label class="label" for="account_id">
                        <span class="label-text">Account</span>
                    </label>
                    <select id="account_id" wire:model="account_id" class="select select-bordered w-full">
                        <option value="">Select an account</option>
                        @foreach (\App\Models\Account::all() as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')
                        <span class="text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Category -->
                <div class="form-control">
                    <label class="label" for="category_id">
                        <span class="label-text">Category</span>
                    </label>
                    <select id="category_id" wire:model="category_id" class="select select-bordered w-full">
                        <option value="">Select an account</option>
                        @foreach (\App\Models\TransactionCategory::all() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="text-error">{{ $message . ' ' . $category_id }}</span>
                    @enderror
                </div>

                <!-- Type -->
                <div class="form-control">
                    <label class="label" for="type_id">
                        <span class="label-text">Transaction Type</span>
                    </label>
                    <select id="type_id" wire:model="type_id" class="select select-bordered w-full">
                        <option value="">Select a type</option>
                        @foreach (\App\Models\Type::all() as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('type_id')
                        <span class="text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Image Upload -->
                <div class="form-control">
                    <label class="label" for="image">
                        <span class="label-text">Upload Image (Optional)</span>
                    </label>
                    <input id="image" type="file" wire:model="image"
                        class="file-input file-input-bordered w-full" />
                    @error('image')
                        <span class="text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-full">Save Transaction</button>
            </form>
        </div>
    </div>
</div>
