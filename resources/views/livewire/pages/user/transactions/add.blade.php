<?php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\Type;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;
use Masmerise\Toaster\Toaster;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $accounts;
    public $expenses;
    public $incomes;
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

    public function save()
    {
        $this->validate();

        if ($this->type_id == 2) {
            $account = Account::find($this->account_id);
            if ($account->balance < $this->amount) {
                session()->flash('error', 'Insufficient Balance. Available balance: ₱' . number_format($account->balance, 2));
                return;
            }
        }

        DB::transaction(function () {
            $imagePath = $this->image ? $this->image->store('img/transactions', 'local') : null;

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'account_id' => $this->account_id,
                'category_id' => $this->category_id == null ? 1 : $this->category_id,
                'recurring_id' => $this->recurring_id,
                'type_id' => $this->type_id ? 2 : 1,
                'name' => $this->name,
                'description' => $this->description,
                'amount' => $this->amount,
                'image_url' => $imagePath,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Attach selected tags to the transaction
            if (!empty($this->selectedTags)) {
                $this->transaction->tags()->sync($this->selectedTags);
            }

            $account = Account::find($transaction->account_id);
            if ($transaction->types->name == 'Expense') {
                $account->balance -= $transaction->amount;
            } else {
                $account->balance += $transaction->amount;
            }
            $account->save();
        });

        // Reset form fields
        $this->reset(['name', 'description', 'amount', 'image', 'account_id', 'category_id', 'recurring_id', 'type_id', 'selectedTags']);

        // Emit event to refresh transaction list
        $this->dispatch('transactionUpdate');
        $this->dispatch('accountUpdate');

        Toaster::success('Transaction Created!');
    }

    public function mount()
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        $this->incomes = TransactionCategory::where('user_id', Auth::id())->where('type_id', 1)->get();
        $this->expenses = TransactionCategory::where('user_id', Auth::id())->where('type_id', 2)->get();

        // Initialize with empty tags array
        $this->selectedTags = [];
    }

    #[On('tagsUpdated')]
    public function updateSelectedTags($tagIds)
    {
        $this->selectedTags = $tagIds;
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
    <form wire:submit="save" class="space-y-5" x-data="{ expense: true }">
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
                    <span class="text-sm font-semibold" :class="expense ? 'text-secondary' : 'text-primary'">
                        {{ Carbon::now()->format('F j, Y') }}
                    </span>
                </div>

                <input id="name" type="text" wire:model="name" placeholder="Name"
                    :class="expense ? 'text-secondary' : 'text-primary'"
                    class="input input-ghost input-xl font-bold text-4xl" required autocomplete="name" />
            </div>
            <div class="flex flex-col gap-3">
                <div>
                    <input type="checkbox" checked="checked" wire:model.live="type_id"
                        class="toggle border-secondary bg-secondary checked:bg-primary checked:text-primary checked:border-primary"
                        @click="expense = !expense; console.log(expense)" />
                    <span x-text="expense ? 'Expense' : 'Income'"
                        :class="expense ? 'text-secondary' : 'text-primary'"></span>
                </div>

                <label class="input input-ghost font-semibold text-2xl"
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
            <label class="label mb-2" for="image">
                <span class="label-text text-sm">Attach Image</span>
                <span class="loading loading-spinner loading-xs" wire:loading wire:target="image"></span>
            </label>
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
            <livewire:components.tag-manager :initialSelectedTags="$selectedTags" wire:key="tag-manager" wire:model.live="selectedTags" />
        </div>


        <!-- Submit Button -->
        <div class="form-control">
            <button type="submit" class="btn w-full" @click="$wire.type_id = expense; expense = true"
                :class="expense ? 'btn-secondary' : 'btn-primary'">Save</button>
        </div>
    </form>
</section>
