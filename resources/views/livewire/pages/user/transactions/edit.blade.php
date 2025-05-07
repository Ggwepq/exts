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
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public Transaction $transaction;
    public Transaction $oldTransaction;
    public Balance $balance;
    public $accounts;
    public $incomes;
    public $expenses;
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
        $account = $this->transaction->accounts;

        if ($this->transaction->type_id == 1) {
            // Income → subtract from balance
            $account->balance -= $this->transaction->amount;
        } else {
            // Expense → add back to balance
            $account->balance += $this->transaction->amount;
        }

        $account->save();
        $this->transaction->delete();

        $this->dispatch('transactionUpdate');
        $this->dispatch('detailSidebarClose');
        Toaster::success('Transaction Deleted!');
    }

    #[On('update-selected-tags')]
    public function updateSelectedTags($tags)
    {
        $this->selectedTags = $tags;
    }

    public function save()
    {
        $oldTransaction = $this->transaction;
        $this->type_id = $this->type_id ? 2 : 1;

        $this->validate();

        // Checks
        $noChanges = $this->transaction->account_id == $this->account_id && $this->transaction->category_id == $this->category_id && $this->transaction->type_id == $this->type_id && $this->transaction->amount == $this->amount && $this->transaction->name === $this->name && $this->transaction->description === $this->description && $this->transaction->selectedTags === $this->selectedTags && $this->transaction->image === $this->image;

        if ($noChanges) {
            Toaster::error('No changes detected');
            return;
        }

        if ($this->type_id == 2) {
            $account = Account::find($this->account_id);
            if ($account->balance < $this->amount) {
                Toaster::error('Insufficient Account Balance');
                $this->amount = $this->oldTransaction->amount;
                return;
            }
        }

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
                'type_id' => $this->type_id,
                'name' => $this->name,
                'description' => $this->description,
                'amount' => $this->amount,
                'image_url' => $imagePath,
                'updated_at' => now(),
            ]);

            Toaster::success('Transaction Updated!');

            // Sync tags only when explicitly saving
            $this->transaction->tags()->sync($this->selectedTags);

            $previousAccount = $this->oldTransaction->accounts;
            $currentAccount = Account::find($this->account_id);

            // DO NOT ANYTHING HERE FOR THE LOVE OF GOD
            // Case 1: Account has changed
            if ($this->account_id != $this->oldTransaction->account_id) {
                // Reverse old transaction from old account
                if ($this->oldTransaction->type_id == 1) {
                    $previousAccount->balance -= $this->oldTransaction->amount;
                } else {
                    $previousAccount->balance += $this->oldTransaction->amount;
                }

                // Apply new transaction to new account
                if ($this->type_id == 1) {
                    $currentAccount->balance += $this->amount;
                } else {
                    $currentAccount->balance -= $this->amount;
                }
            }
            // Case 2: Same account, but type changed
            elseif ($this->type_id != $this->oldTransaction->type_id) {
                // Reverse old type
                if ($this->oldTransaction->type_id == 1) {
                    // Was income → remove it
                    $currentAccount->balance -= $this->oldTransaction->amount;
                } else {
                    // Was expense → add it back
                    $currentAccount->balance += $this->oldTransaction->amount;
                }

                // Apply new type
                if ($this->type_id == 1) {
                    $currentAccount->balance += $this->amount;
                } else {
                    $currentAccount->balance -= $this->amount;
                }
            } else {
                if ($this->type_id == 1) {
                    $currentAccount->balance += $this->amount;
                } else {
                    $currentAccount->balance -= $this->amount;
                }
            }

            // Save accounts
            $previousAccount->save();
            $currentAccount->save();
        });
        $this->oldTransaction = $this->transaction;
        $this->reloadTransaction();
        $this->dispatch('transactionUpdate');
        $this->dispatch('refreshTransaction');
    }

    public function getSelectedAccountProperty()
    {
        return collect($this->accounts)->firstWhere('id', $this->account_id);
    }

    public function getSelectedCategoryProperty()
    {
        return $this->type_id == 1 ? collect($this->incomes)->firstWhere('id', $this->category_id) : collect($this->expenses)->firstWhere('id', $this->category_id);
    }

    public function getSelectedGradientProperty()
    {
        return $this->type_id == 1 ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : 'bg-gradient-to-r from-secondary/100 to-secondary/50 text-secondary-content';
    }
};

?>

<section>
    <!-- Form -->
    <form wire:submit="save" class="space-y-5" x-data="{ expense: $wire.type_id == 1 ? false : true }">
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
                @error('name')
                    <span class="validator-hint">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex flex-col gap-3 w-1/2 mt-2">
                <div>
                    <input type="checkbox" :checked="$wire.type_id == 1" wire:model.live="type_id"
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
                        :class="expense ? 'text-secondary' : 'text-primary'" x-text="formatted()">
                    </span>

                    <!-- Input field -->
                    <label x-show="editing" @click.away="editing = false"
                        class="input input-ghost font-semibold text-2xl mt-2"
                        :class="expense ? 'text-secondary' : 'text-primary'">
                        <span class="label">₱</span>
                        <input x-ref="input" x-model="amount" wire:model.lazy="amount" type="text"
                            placeholder="0.00" step="0.01" class="bg-transparent w-full outline-none border-none" />
                    </label>
                </div>
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
                        <span>{{ $this->selectedAccount?->name ?? 'Account' }}</span>
                        @if ($account_id)
                            <span class="badge badge-sm block truncate"
                                :class="expense ? 'badge-secondary' : 'badge-primary'">₱{{ $account_id ? number_format($this->selectedAccount->balance) : '' }}</span>
                        @endif
                    </label>
                    <div tabindex="0"
                        class="dropdown-content z-[1] menu mt-4 shadow-lg bg-base-100 rounded-xl w-60 border border-base-200">
                        <ul class="ml-2 mt-1.5">
                            <li class="text-6sm">
                                @foreach ($accounts as $account)
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
                                @endforeach
                            </li>
                        </ul>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
                        </svg>
                        <span>{{ $this->selectedCategory?->name ?? 'Category' }}</span>
                    </label>
                    <div tabindex="0"
                        class="dropdown-content z-[1] menu mt-4 shadow-lg bg-base-100 w-60 border border-base-200">
                        <ul class="ml-2 mt-1.5">
                            <li class="text-6sm">
                                @if ($type_id == 1)
                                    @foreach ($incomes as $income)
                                        @if ($income->name !== 'None')
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
                                        @endif
                                    @endforeach
                                @else
                                    @foreach ($expenses as $expense)
                                        @if ($expense->name !== 'None')
                                            <a wire:click="$set('category_id', {{ $expense->id }})"
                                                class="flex items-center justify-between px-3 py-2 transition-all duration-200 group
        {{ $category_id == $expense->id ? $this->selectedGradient : '' }}"
                                                :class="expense ? 'hover:bg-secondary' : 'hover:bg-primary'">

                                                <span class="flex space-x-1 items-center group-hover:text-primary"
                                                    :class="expense ? 'group-hover:text-secondary-content' :
                                                        'group-hover:text-primary-content'">
                                                    <span class="truncate ">{{ $expense->name }}</span>
                                                </span>
                                            </a>
                                        @endif
                                    @endforeach
                                @endif
                            </li>
                        </ul>
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
            <livewire:components.tag-manager :initialSelectedTags="$selectedTags" wire:key="tag-manager" wire:model="selectedTags" />
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
                        <div class="w-10 rounded border-4 " :class="expense ? 'border-secondary' : 'border-primary'">
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
                <button class="btn btn-error flex-1" wire:click="delete">Delete<span
                        class="loading loading-bars loading-lg" wire:loading></span></button>
            </div>
        </template>
    </div>
</section>
