<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\Type;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    public Transaction $transaction;
    public Transaction $oldTransaction;
    public $selectedTags = [];

    public $name;
    public $description;
    public $amount; // 2MB max
    public $image;
    public $account;
    public $category;
    public $recurring_id;
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
        $this->account = $this->transaction->accounts;
        $this->category = $this->transaction->transactionCategories;
        $this->type_id = $this->transaction->type_id;
        $this->image = $this->transaction->image;
        $this->selectedTags = $this->transaction->tags->pluck('id')->toArray();
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
                    name: @entangle('name')
                }" class="relative w-full max-w-full">

                    <!-- Display name (click to edit) -->
                    <span class="cursor-pointer font-bold text-3xl block truncate"
                        :class="expense ? 'text-secondary' : 'text-primary'" x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                    </span>

                </div>
            </div>
            <div class="flex flex-col gap-3 w-1/2 mt-2">
                <div>
                    <input type="checkbox" :checked="$wire.type_id == 1" wire:model.live="type_id"
                        class="toggle border-secondary bg-secondary checked:bg-primary checked:text-primary checked:border-primary"
                        @click="expense = !expense; $wire.category_id = ''" disabled />
                    <span x-text="expense ? 'Expense' : 'Income'"
                        :class="expense ? 'text-secondary' : 'text-primary'"></span>
                </div>

                <div x-data="{
                    amount: @entangle('amount'),
                    formatted() {
                        const num = parseFloat(this.amount || 0);
                        return num.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
                    }
                }" class="relative w-full max-w-xs">

                    <!-- Display formatted amount -->
                    <span class="cursor-pointer text-2xl font-semibold block truncate"
                        :class="expense ? 'text-secondary' : 'text-primary'" x-text="formatted()">
                    </span>
                </div>
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
                        <span>{{ $account->name ?? 'None' }}</span>
                        @if ($account)
                            <span class="badge badge-sm block truncate"
                                :class="expense ? 'badge-secondary' : 'badge-primary'">₱{{ number_format($account->balance) }}</span>
                        @endif
                    </label>
                </div>
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
                        <span>{{ $category->name ?? 'None' }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="form-control" :class="expense ? 'text-secondary' : 'text-primary'">
            <label class="label mb-2" for="description">
                <span class="label-text text-sm">Description</span>
            </label>
            <textarea id="description" wire:model="description" placeholder="..." class="textarea textarea-bordered w-full"
                :class="expense ? 'textarea-secondary' : 'textarea-primary'" autocomplete="description" disabled></textarea>
            @error('description')
                <span class="text-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Image Upload -->
        <div class="form-control" :class="expense ? 'text-secondary' : 'text-primary'">
            @if ($transaction->image_url)
                <div class="avatar"
                    @click="$dispatch('open-image-viewer', '{{ asset('app/' . $transaction->image_url) }}')">
                    <div class="w-20 rounded border-4 " :class="expense ? 'border-secondary' : 'border-primary'">
                        <img src="{{ asset('app/' . $transaction->image_url) }}" alt="Transaction Receipt" />
                    </div>
                </div>
            @endif
        </div>
    </form>

    <div x-data="{ isDelete: false }" class="mt-6">
        <template x-if="!isDelete">
            <button @click="isDelete = true" class="btn btn-error w-full">Delete Transaction<span
                    wire:loading.class="loading loading-bars loading-lg"></span></button>
        </template>
        <template x-if="isDelete">
            <div class="flex flex-row gap-x-2">
                <button @click="isDelete = false" class="flex-1 btn btn-neutral">Cancel</button>
                <button class="btn btn-error flex-1" wire:click="delete" @click="isDelete = false">Delete<span
                        class="loading loading-bars loading-lg" wire:loading></span></button>
            </div>
        </template>
    </div>
</section>
