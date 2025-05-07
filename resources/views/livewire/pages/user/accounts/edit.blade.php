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

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $account;
    public $name;
    public $categories;

    #[Validate('nullable|exists:account_categories,id')]
    public $category_id;

    #[Validate('required|numeric|min:0.01')]
    public $amount; // 2MB max

    public function mount($modelId)
    {
        $account = Account::find($modelId);
        $this->account = $account;
        $this->name = $account->name;
        $this->category_id = $account->category_id;
        $this->amount = $account->balance;

        $this->categories = AccountCategory::where('user_id', Auth::id())->get();
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('accounts')
                        ->where('user_id', auth()->id())
                        ->where('id', '!=', $this->account->id ?? null)
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->exists();

                    if ($exists) {
                        $fail('The name must be unique.');
                    }
                },
            ],
        ];
    }

    public function update()
    {
        $this->validate();

        $account = $this->account->update([
            'category_id' => $this->category_id ? $this->category_id : null,
            'name' => $this->name,
            'updated_at' => Carbon\Carbon::now(),
        ]);

        // Emit event to refresh transaction list
        $this->dispatch('accountUpdate');

        Toaster::success('Account Updated!');
    }

    public function delete()
    {
        $this->account->delete();
        $this->dispatch('accountUpdate');
        Toaster::success('Account Deleted!');
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
    <form wire:submit="update" class="space-y-5">
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
                required autocomplete="amount" disabled />
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
                <span class="text-error">{{ $message . ' ' . $category_id }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-full">Save Changes<span
                wire:loading.class="loading loading-bars loading-lg" wire:target="update"></span></button>
    </form>

    <div x-data="{ isDelete: false }" class="mt-4">

        <template x-if="!isDelete">
            <button @click="isDelete = true" class="btn btn-error w-full">Delete Transaction<span
                    wire:loading.class="loading loading-bars loading-lg"></span></button>
        </template>
        <template x-if="isDelete">
            <div class="flex flex-row gap-x-2">
                <button @click="isDelete = false" class="flex-1 btn btn-neutral">Cancel
                </button>

                <button class="btn btn-error flex-1" wire:click="delete"
                    @click="setTimeout(() => detailSidebarOpen = false, 1000)">Delete<span
                        wire:loading.class="loading loading-bars loading-lg" wire:target="delete"></span></button>
            </div>
        </template>

    </div>
</section>
