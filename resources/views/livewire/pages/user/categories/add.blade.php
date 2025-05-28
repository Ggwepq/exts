<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\CategoryGroup;
use App\Models\Type;
use Carbon\Carbon;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\Attribute\On;

new #[Layout('layouts.app')] class extends Component {
    // [Validate('required|string|max:255')]
    public $name;

    #[Validate('nullable')]
    public $group_id = null;

    #[Validate('required')]
    public $type_id = false;

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('transaction_categories')
                        ->where('user_id', Auth::id())
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->exists();

                    if ($exists) {
                        Toaster::error('Name must be Unique');
                        $fail('');
                    }
                },
            ],
        ];
    }

    public function save()
    {
        $this->validate();
        $this->type_id = $this->type_id ? 1 : 2;

        try {
            // Use a database transaction to ensure consistency
            DB::beginTransaction();
            $category = TransactionCategory::create([
                'user_id' => Auth::id(),
                'group_id' => !empty($this->group_id) ? $this->group_id : null,
                'name' => $this->name,
                'type_id' => $this->type_id,
            ]);
            DB::commit();

            Toaster::success('Category Created!');
        } catch (\Exception $e) {
            DB::rollBack();
            Toaster::error('Category Creation Failed!');
        }

        $this->resetFields();

        // Emit event to refresh transaction list
        $this->dispatch('categoryUpdate');
        $this->dispatch('rightSidebarClose');
        $this->dispatch('reloadDropdowns');
    }

    public function resetFields()
    {
        $this->name = null;
        $this->group_id = null;
        $this->type_id = $this->type_id == 2 ? false : true;
    }

    public function mount() {}

    #[On('reloadDropdowns')]
    public function getGroupsProperty()
    {
        return CategoryGroup::where('user_id', Auth::id())->where('type', 'Transaction')->get();
    }

    public function getSelectedGroupProperty()
    {
        return collect($this->groups)->firstWhere('id', $this->group_id);
    }

    public function getSelectedGradientProperty()
    {
        return $this->type_id == 1 ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : 'bg-gradient-to-r from-secondary/100 to-secondary/50 text-secondary-content';
    }
}; ?>

<section>
    <!-- Form -->

    <form wire:submit="save" class="space-y-10" x-data="{ expense: true }">
        <!-- name -->
        <div class="flex flex-row w-full justify-between">
            <div class="flex items-center gap-2 mb-2 ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6" :class="expense ? 'text-secondary' : 'text-primary'">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                </svg>
                <span class="text-xs font-semibold" :class="expense ? 'text-secondary' : 'text-primary'">
                    {{ carbon::now()->format('l, F j Y') }}
                </span>
            </div>

            <div>
                <input type="checkbox" checked="$wire.type_id == 1" wire:model.live="type_id"
                    class="toggle border-secondary bg-secondary checked:bg-primary checked:text-primary checked:border-primary"
                    @click="expense = !expense; $wire.category_id = ''" />
                <span x-text="expense ? 'Expense' : 'Income'"
                    :class="expense ? 'text-secondary' : 'text-primary'"></span>
            </div>
        </div>
        <div class="flex flex-col gap-3 mt-2">

            <div x-data="{
                editing: false,
                name: @entangle('name')
            }" class="relative w-full max-w-full">

                <!-- display name (click to edit) -->
                <span x-show="!editing" @click="editing = true; $nextTick(() => $refs.nameinput.focus())"
                    class="cursor-pointer font-bold block"
                    :class="(expense ? 'text-secondary input-secondary' : 'text-primary input-primary') + ' ' + (!name ?
                        'text-center text-3xl' : 'text-left truncate  text-5xl')"
                    x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                </span>

                <!-- editable input -->
                <input x-show="editing" x-ref="nameinput" x-model="name" wire:model.lazy="name"
                    @click.away="editing = false" type="text" placeholder="name" autocomplete="name"
                    class="input input-xl font-bold text-4xl w-full bg-transparent outline-none border-none"
                    :class="(expense ? 'text-secondary input-secondary' : 'text-primary input-primary') + ' ' + (!name ?
                        'text-center ' : 'text-left')"
                    :class="name != null ? 'text-left' : 'text-center'" autofocus />

            </div>
            @error('name')
                <span class="validator-hint">{{ $message }}</span>
            @enderror
        </div>
        <div class="flex flex-row gap-4 ">

            <div class="dropdown dropdown-center w-full">
                <label tabindex="0" class="btn btn-md border shadow-sm w-full" aria-label="Select Group"
                    :class="expense ? 'text-secondary border-secondary hover:bg-secondary/50' :
                        'text-primary border-primary hover:bg-primary/50'">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <span>{{ $group_id ? $this->selectedGroup->name : 'Group' }}</span>
                </label>
                <div tabindex="0"
                    class="dropdown-content z-[1] menu mt-4 shadow-lg bg-base-100 rounded-xl w-60 border border-base-200">
                    <ul class="ml-2 my-1.5 flex flex-col overflow-auto max-h-40 space-y-1">
                        @foreach ($this->groups as $group)
                            <li class=" text-6sm  ">
                                <a wire:click="$set('group_id', {{ $group->id }})"
                                    class="flex items-center justify-between px-3 py-2 transition-all duration-200 group
        {{ $group_id == $group->id ? $this->selectedGradient : '' }}"
                                    :class="expense ? 'hover:bg-secondary' : 'hover:bg-primary'">

                                    <span class="flex space-x-1 items-center group-hover:text-primary"
                                        :class="expense ? 'group-hover:text-secondary-content' :
                                            'group-hover:text-primary-content'">
                                        <span class="truncate ">{{ $group->name }}</span>
                                    </span>

                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <a @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Group', component: 'pages.user.groups.add'}); rightSidebarOpen = true; console.log(rightSidebarOpen)"
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
                @error('account_id')
                    <span class="validator-hint">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-full"
            :class="expense ? 'bg-secondary' : 'bg-primary'">Save<span
                wire:loading.class="loading loading-bars loading-lg"></span></button>
    </form>
</section>
