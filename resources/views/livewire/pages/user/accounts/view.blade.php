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
use Carbon\Carbon;

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

    public function delete()
    {
        $this->account->delete();
        $this->dispatch('accountUpdate');
        Toaster::success('Account Deleted!');
    }

    public function getGroupsProperty()
    {
        return AccountCategory::where('user_id', Auth::id())->get();
    }

    public function getSelectedGroupProperty()
    {
        return collect($this->categories)->firstWhere('id', $this->category_id);
    }
}; ?>
<section>
    <!-- Form -->
    <form wire:submit="update" class="space-y-5">

        <div class="flex flex-row items-end mb-5 gap-x-4">
            <div class="flex flex-col gap-3 w-1/2">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 text-primary">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span class="text-xs font-semibold text-primary">
                        {{ Carbon::now()->format('l, F j Y') }}
                    </span>
                </div>

                <div x-data="{
                    name: @entangle('name')
                }" class="relative w-full max-w-full">

                    <!-- Display name (click to edit) -->
                    <span class="cursor-pointer font-bold text-3xl block truncate text-primary"
                        x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                    </span>


                </div>
            </div>
            <div class="flex flex-col gap-3 w-1/2 mt-2">
                <div x-data="{
                    amount: @entangle('amount'),
                    formatted() {
                        const num = parseFloat(this.amount || 0);
                        return num.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
                    }
                }" class="relative w-full max-w-xs">

                    <!-- Display formatted amount -->
                    <span class="cursor-pointer text-2xl font-semibold block truncate text-primary"
                        x-text="formatted()">
                    </span>
                </div>
            </div>
        </div>

        <!-- Group Dropdown -->
        <div class="flex flex-row gap-4 ">
            <div class="dropdown dropdown-center w-full">
                <label tabindex="0"
                    class="btn btn-md border shadow-sm w-full text-primary border-primary hover:bg-primary/50"
                    aria-label="Select Group">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <span>{{ $category_id ? $this->selectedGroup->name : 'Category' }}</span>
                </label>
            </div>
        </div>

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
                    @click="setTimeout(() => detailSidebarOpen = false, 1000); isDelete = false">Delete<span
                        wire:loading.class="loading loading-bars loading-lg" wire:target="delete"></span></button>
            </div>
        </template>

    </div>
</section>
