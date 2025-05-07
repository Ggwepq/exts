<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\AccountCategory;
use App\Models\Type;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    // [Validate('required|string|max:255')]
    public $categories;
    public $name;

    #[Validate('nullable')]
    public $category_id = null;

    #[Validate('required|numeric|min:0')]
    public $amount;

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('accounts')
                        ->where('user_id', Auth::id())
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->exists();

                    if ($exists) {
                        $fail('The name must be unique.');
                    }
                },
            ],
        ];
    }

    public function create()
    {
        $this->validate();

        try {
            // Use a database transaction to ensure consistency
            DB::beginTransaction();

            $account = Account::create([
                'user_id' => Auth::id(),
                'category_id' => !empty($this->category_id) ? $this->category_id : null,
                'name' => $this->name,
                'balance' => $this->amount, // Set initial balance directly
            ]);

            // Create an initial transaction to track this balance
            if ($this->amount > 0) {
                Transaction::create([
                    'user_id' => Auth::id(),
                    'account_id' => $account->id,
                    'category_id' => null,
                    'type_id' => 1, // Income type
                    'name' => 'Initial Account Balance',
                    'amount' => $this->amount,
                ]);
            }

            DB::commit();

            // Reset form fields
            $this->reset(['name', 'amount', 'category_id']);

            // Emit event to refresh transaction list
            $this->dispatch('accountUpdate');

            Toaster::success('Account Created!');
        } catch (\Exception $e) {
            DB::rollBack();
            Toaster::error('Account Creation Failed!');
        }
        $this->dispatch('rightSidebarClose');
        $this->dispatch('reloadDropdowns');
    }

    public function mount()
    {
        $this->categories = AccountCategory::where('user_id', Auth::id())->get();
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
                <option value="">None</option>
                @foreach ($categories as $category)
                    @if ($category->name !== 'None')
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endif
                @endforeach
            </select>
            @error('category_id')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-full">Save<span
                wire:loading.class="loading loading-bars loading-lg"></span></button>
    </form>
</section>
