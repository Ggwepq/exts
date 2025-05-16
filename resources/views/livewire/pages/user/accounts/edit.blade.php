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
                    editing: false,
                    name: @entangle('name')
                }" class="relative w-full max-w-full">

                    <!-- Display name (click to edit) -->
                    <span x-show="!editing" @click="editing = true; $nextTick(() => $refs.nameInput.focus())"
                        class="cursor-pointer font-bold text-3xl block truncate text-primary"
                        x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                    </span>

                    <!-- Editable input -->
                    <input x-show="editing" x-ref="nameInput" x-model="name" wire:model.lazy="name"
                        @click.away="editing = false" type="text" placeholder="Name" autocomplete="name"
                        class="input input-ghost input-primary text-primary input-xl font-bold text-4xl w-full bg-transparent outline-none border-none" />

                </div>
            </div>
            <div class="flex flex-col gap-3 w-1/2 mt-2">
                <div x-data="{
                    editing: false,
                    amount: @entangle('amount'),
                    formatted() {
                        const num = parseFloat(this.amount || 0);
                        return num.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
                    }
                }" class="relative w-full max-w-xs">

                    <!-- Display formatted amount -->
                    <span x-show="!editing" class="cursor-pointer text-2xl font-semibold block truncate text-primary"
                        x-text="formatted()">
                    </span>
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
                <div tabindex="0"
                    class="dropdown-content z-[1] menu mt-4 shadow-lg bg-base-100 rounded-xl w-60 border border-base-200">
                    <ul class="ml-2 my-1.5 flex flex-col overflow-auto max-h-40 space-y-1">
                        @foreach ($this->groups as $group)
                            <li class=" text-6sm  ">
                                <a wire:click="$set('category_id', {{ $group->id }})"
                                    class="flex items-center justify-between px-3 py-2 transition-all duration-200 group hover:bg-primary">

                                    <span class="flex space-x-1 items-center group-hover:text-primary-content">
                                        <span class="truncate ">{{ $group->name }}</span>
                                    </span>

                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <a @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Group', component: 'pages.user.groups.add'}); rightSidebarOpen = true; console.log(rightSidebarOpen)"
                        class="flex items-center justify-center px-3 py-2 transition-all duration-200 group rounded-xl border-4 hover:bg-primary border-primary">

                        <span class="flex space-x-1 items-center justify-center group-hover:text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>New</span>
                        </span>
                    </a>
                </div>
                @error('account_id')
                    <span class="validator-hint">{{ $message }}</span>
                @enderror
            </div>
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
