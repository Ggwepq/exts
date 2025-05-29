<?php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\Budget;
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
    public $budgetLimit;
    public $amountSpent;

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
    public $type_id = false;

    public function loadBudget()
    {
        $budget = Budget::where('user_id', auth()->id())
            ->where('transaction_category_id', $this->category_id)
            ->where('status', 'Active')
            ->where('end_date', '>=', Carbon::today())
            ->first();

        if ($budget) {
            $this->budgetLimit = $budget->limit_amount;
            $this->amountSpent = Transaction::where('user_id', auth()->id())
                ->where('category_id', $this->category_id)
                ->where('type_id', 2) // replace with your Expense type_id
                ->sum('amount');
        } else {
            $this->budgetLimit = null;
            $this->amountSpent = 0;
        }
    }

    public function save()
    {
        $this->validate();

        $this->type_id = $this->type_id ? 1 : 2;

        if ($this->category_id) {
            $this->loadBudget();

            if ($this->budgetLimit && $this->amountSpent + $this->amount > $this->budgetLimit) {
                Toaster::warning('Budget limit has been exceeded');
            }
        }

        if ($this->type_id == 2) {
            $account = Account::find($this->account_id);
            if ($account->balance < $this->amount) {
                Toaster::error('Insufficient Account Balance');
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
                'type_id' => $this->type_id,
                'name' => $this->name,
                'description' => $this->description,
                'amount' => $this->amount,
                'image_url' => $imagePath,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Attach selected tags to the transaction
            $transaction->tags()->sync($this->selectedTags);

            $account = Account::find($transaction->account_id);
            if ($transaction->types->name == 'Expense') {
                $account->balance -= $transaction->amount;
            } else {
                $account->balance += $transaction->amount;
            }
            $account->save();
        });

        // Reset form fields
        $this->reset(['name', 'description', 'amount', 'image', 'account_id', 'category_id', 'recurring_id', 'selectedTags']);

        // Emit event to refresh transaction list
        $this->dispatch('transactionUpdate');
        $this->dispatch('rightSidebarClose');
        $this->dispatch('reloadDropdowns');

        Toaster::success('Transaction Created!');
    }

    public function mount()
    {
        $this->loadDropdowns();

        // Initialize with empty tags array
        $this->selectedTags = [];
    }

    #[On('reloadDropdowns')]
    public function loadDropdowns()
    {
        $this->accounts = Account::where('user_id', Auth::id())->get();
        $this->incomes = TransactionCategory::where('user_id', Auth::id())->where('type_id', 1)->get();
        $this->expenses = TransactionCategory::where('user_id', Auth::id())->where('type_id', 2)->get();
    }

    #[On('update-selected-tags')]
    public function updateSelectedTags($tags)
    {
        $this->selectedTags = $tags;
    }

    public function getSelectedAccountProperty()
    {
        return collect($this->accounts)->firstWhere('id', $this->account_id);
    }

    public function getSelectedCategoryProperty()
    {
        $category = $this->type_id == 1 ? collect($this->incomes)->firstWhere('id', $this->category_id) : collect($this->expenses)->firstWhere('id', $this->category_id);
        return $category;
    }

    public function getPercentage()
    {
        $category = TransactionCategory::findOrFail($this->category_id);

        $percentage = ($category->transactions->sum('amount') / $category->budgets->limit_amount) * 100;
        return $percentage;
    }

    public function getSelectedGradientProperty()
    {
        return $this->type_id == 1 ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : 'bg-gradient-to-r from-secondary/100 to-secondary/50 text-secondary-content';
    }
}; ?>

<section>
    <!-- Form -->
    <form wire:submit="save" class="space-y-5" x-data="{ expense: true }">
        <!-- Name -->
        <div class="flex flex-row items-end mb-5 gap-x-4">
            <div class="flex flex-col gap-3 w-1/2">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        :class="expense ? 'text-secondary' : 'text-primary'" stroke="currentColor"
                        class="size-4 text-base-content/70">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span class="text-xs font-semibold" :class="expense ? 'text-secondary' : 'text-primary'">
                        {{ Carbon::now()->format('l, F j Y') }}
                    </span>
                </div>

                <div x-data="{
                    editing: false,
                    name: @entangle('name')
                }" class="relative w-full max-w-full">

                    <!-- Display name (click to edit) -->
                    <span x-show="!editing" @click="editing = true; $nextTick(() => $refs.nameInput.focus())"
                        class="cursor-pointer font-bold text-3xl block truncate"
                        :class="expense ? 'text-secondary' : 'text-primary'" x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                    </span>

                    <!-- Editable input -->
                    <input x-show="editing" x-ref="nameInput" x-model="name" wire:model.lazy="name"
                        @click.away="editing = false" type="text" placeholder="Name" autocomplete="name"
                        class="input input-ghost input-xl font-bold text-4xl w-full bg-transparent outline-none border-none"
                        :class="expense ? 'text-secondary' : 'text-primary'" />

                </div>
            </div>
            <div class="flex flex-col gap-3 w-1/2 mt-2">
                <div>
                    <input type="checkbox" checked="checked" wire:model.live="type_id"
                        class="toggle border-secondary bg-secondary checked:bg-primary checked:text-primary checked:border-primary"
                        @click="expense = !expense; $wire.category_id = ''" />
                    <span x-text="expense ? 'Expense' : 'Income'"
                        :class="expense ? 'text-secondary' : 'text-primary'"></span>
                </div>

                <div x-data="{
                    editing: false,
                    amount: @entangle('amount'),
                    formatted() {
                        const num = parseFloat(this.amount || 0);
                        return num.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
                    }
                }" class="relative w-full max-w-xs">

                    <!-- Display formatted amount -->
                    <span x-show="!editing" @click="editing = true; $nextTick(() => $refs.input.focus())"
                        class="cursor-pointer text-2xl font-semibold block truncate"
                        :class="expense ? 'text-secondary' : 'text-primary'"
                        x-text="expense ? '-'+formatted() : '+'+formatted()">
                    </span>

                    <!-- Input field -->
                    <label x-show="editing" @click.away="editing = false"
                        class="input input-ghost font-semibold text-2xl mt-2"
                        :class="expense ? 'text-secondary' : 'text-primary'">
                        <span class="label" x-text="expense ? '-₱' : '+₱'"></span>
                        <input x-ref="input" x-model="amount" wire:model.lazy="amount" type="text"
                            placeholder="0.00" step="0.01" class="bg-transparent w-full outline-none border-none" />
                    </label>
                </div>
            </div>
        </div>

        <div class="flex flex-row items-end mb-5 gap-x-4">
            <div class="flex flex-col gap-3 w-1/2">
                @error('name')
                    <span class="validator-hint">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex flex-col gap-3 w-1/2">
                @error('amount')
                    <span class="validator-hint">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex flex-row gap-4 ">

            <div class="w-1/2">
                <div class="dropdown dropdown-start w-full">
                    <label tabindex="0" class="btn btn-md border shadow-sm w-full" aria-label="Select Account"
                        :class="expense ? 'text-secondary border-secondary hover:bg-secondary/50' :
                            'text-primary border-primary hover:bg-primary/50'">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        <span>{{ $account_id ? $this->selectedAccount->name : 'Account' }}</span>
                        @if ($account_id)
                            <span class="badge badge-sm block truncate"
                                :class="expense ? 'badge-secondary' : 'badge-primary'">₱{{ $account_id ? number_format($this->selectedAccount->balance) : '' }}</span>
                        @endif
                    </label>
                    <div tabindex="0"
                        class="dropdown-content z-[1] menu mt-4 shadow-lg bg-base-100 rounded-xl w-60 border border-base-200">
                        <ul class="ml-2 my-1.5 flex flex-col overflow-auto max-h-40 space-y-1">
                            @foreach ($accounts as $account)
                                <li class=" text-6sm  ">
                                    <a wire:click="$set('account_id', {{ $account->id }})"
                                        class="flex items-center justify-between px-3 py-2 transition-all duration-200 group
        {{ $account_id == $account->id ? $this->selectedGradient : '' }}"
                                        :class="expense ? 'hover:bg-secondary' : 'hover:bg-primary'">

                                        <span class="flex space-x-1 items-center group-hover:text-primary"
                                            :class="expense ? 'group-hover:text-secondary-content' :
                                                'group-hover:text-primary-content'">
                                            <span class="truncate ">{{ $account->name }}</span>
                                        </span>

                                        <span class="badge badge-xs badge-primary p-3"
                                            :class="expense ? 'badge-secondary' : 'badge-primary'">
                                            ₱{{ number_format($account->balance) }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Account', component: 'pages.user.accounts.add'}); rightSidebarOpen = true;"
                            class="flex items-center justify-center px-3 py-2 transition-all duration-200 group rounded-xl border-4"
                            :class="expense ? 'hover:bg-secondary border-secondary' : 'hover:bg-primary border-primary'">

                            <span class="flex space-x-1 items-center justify-center group-hover:text-primary"
                                :class="expense ? 'group-hover:text-secondary-content' :
                                    'group-hover:text-primary-content'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>New</span>
                            </span>
                        </a>
                    </div>
                </div>
                @error('account_id')
                    <span class="validator-hint">{{ $message }}</span>
                @enderror
            </div>

            <div class="w-1/2">
                <div class="dropdown dropdown-end w-full">
                    <label tabindex="0" class="btn btn-md border shadow-sm w-full"
                        :class="expense ? 'text-secondary border-secondary hover:bg-secondary/50' :
                            'text-primary border-primary hover:bg-primary/50'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
                        </svg>
                        <span>{{ $category_id ? $this->selectedCategory->name : 'Category' }}</span>


                        @if ($this->selectedCategory && $this->selectedCategory->budgets)
                            <span
                                class="badge badge-sm block truncate
                                @if ($this->getPercentage() < 50) badge-success @elseif($this->getPercentage() >= 50 && $this->getPercentage() < 100) badge-warning @else badge-error @endif">₱{{ number_format($this->selectedCategory->budgets->limit_amount - $this->selectedCategory->transactions->sum('amount')) }}</span>
                        @endif
                    </label>
                    <div tabindex="0"
                        class="dropdown-content z-[1] menu mt-4 shadow-lg bg-base-100 w-60 border border-base-200">
                        <ul class="ml-2 my-1.5 flex flex-col overflow-auto max-h-40 space-y-1">
                            @if ($type_id == 1)
                                @foreach ($incomes as $income)
                                    @if ($income->name !== 'None')
                                        <li class="text-6sm">
                                            <a wire:click="$set('category_id', {{ $income->id }})"
                                                class="flex items-center justify-between px-3 py-2 transition-all duration-200 group
        {{ $category_id == $income->id ? $this->selectedGradient : '' }}"
                                                :class="expense ? 'hover:bg-secondary' : 'hover:bg-primary'">

                                                <span class="flex space-x-1 items-center group-hover:text-primary"
                                                    :class="expense ? 'group-hover:text-secondary-content' :
                                                        'group-hover:text-primary-content'">
                                                    <span class="truncate ">{{ $income->name }}</span>
                                                </span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            @else
                                @foreach ($expenses as $expense)
                                    @php
                                        if ($expense->budgets) {
                                            $percentage =
                                                ($expense->transactions->sum('amount') /
                                                    $expense->budgets->limit_amount) *
                                                100;
                                        }
                                    @endphp
                                    @if ($expense->name !== 'None')
                                        <li class="text-6sm">
                                            <a wire:click="$set('category_id', {{ $expense->id }})"
                                                class="flex items-center justify-between px-3 py-2 transition-all duration-200 group
        {{ $category_id == $expense->id ? $this->selectedGradient : '' }}"
                                                :class="expense ? 'hover:bg-secondary' : 'hover:bg-primary'">

                                                <span class="flex space-x-1 items-center group-hover:text-primary"
                                                    :class="expense ? 'group-hover:text-secondary-content' :
                                                        'group-hover:text-primary-content'">
                                                    <span class="truncate ">{{ $expense->name }}</span>
                                                </span>

                                                @if ($expense->budgets)
                                                    <span
                                                        class="badge badge-xs badge-primary p-3 @if ($percentage < 50) badge-success @elseif($percentage >= 50 && $percentage < 100) badge-warning @else badge-error @endif">
                                                        ₱{{ number_format($expense->budgets->limit_amount - $expense->transactions->sum('amount')) }}
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        </ul>
                        <a @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Category', component: 'pages.user.categories.add'}); rightSidebarOpen = true;"
                            class="flex items-center justify-center px-3 py-2 transition-all duration-200 group rounded-xl border-4"
                            :class="expense ? 'hover:bg-secondary border-secondary' : 'hover:bg-primary border-primary'">

                            <span class="flex space-x-1 items-center justify-center group-hover:text-primary"
                                :class="expense ? 'group-hover:text-secondary-content' :
                                    'group-hover:text-primary-content'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>New</span>
                            </span>
                        </a>
                    </div>
                </div>
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

        <!-- Tags -->
        <div class="form-control" :class="expense ? 'text-secondary' : 'text-primary'">
            <livewire:components.tag-manager :initialSelectedTags="$selectedTags" wire:key="tag-manager"
                wire:model.live="selectedTags" />
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

        <!-- Submit Button -->
        <div class="form-control">
            <button type="submit" class="btn w-full"
                :class="expense ? 'btn-secondary' : 'btn-primary'">Save</button>
        </div>
    </form>
</section>
