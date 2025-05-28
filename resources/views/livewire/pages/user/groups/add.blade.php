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

new #[Layout('layouts.app')] class extends Component {
    public $name;

    #[Validate('nullable')]
    public $group_id = null;

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('category_groups')
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

        try {
            // Use a database transaction to ensure consistency
            DB::beginTransaction();
            $group = CategoryGroup::create([
                'user_id' => Auth::id(),
                'name' => $this->name,
            ]);
            DB::commit();

            // Reset form fields

            Toaster::success('Group Created!');
        } catch (\Exception $e) {
            DB::rollBack();
            Toaster::error('Category Creation Failed!');
        }

        // Emit event to refresh transaction list
        $this->dispatch('categoryUpdate');
        $this->dispatch('reloadDropdowns');
        $this->dispatch('rightSidebarClose');
    }

    public function mount()
    {
        $this->groups = CategoryGroup::where('user_id', Auth::id())->get();
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

    <form wire:submit="save" class="space-y-10">
        <!-- name -->
        <div class="flex flex-row w-full justify-between">
            <div class="flex items-center gap-2 mb-2 ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                </svg>
                <span class="text-xs font-semibold text-primary">
                    {{ carbon::now()->format('l, F j Y') }}
                </span>
            </div>
        </div>
        <div class="flex flex-col gap-3 mt-2">

            <div x-data="{
                editing: false,
                name: @entangle('name').defer
            }" class="relative w-full max-w-full">

                <!-- display name (click to edit) -->
                <span x-show="!editing" @click="editing = true; $nextTick(() => $refs.nameinput.focus())"
                    class="cursor-pointer font-bold block text-primary"
                    :class="!name ? 'text-center text-3xl' : 'text-left truncate  text-5xl'"
                    x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                </span>

                <!-- editable input -->
                <input x-show="editing" x-ref="nameinput" x-model="name" wire:model.lazy="name"
                    @click.away="editing = false" type="text" placeholder="name" autocomplete="name"
                    class="input input-xl font-bold text-4xl w-full bg-transparent outline-none border-none text-primary input-primary"
                    :class="!name ? 'text-center ' : 'text-left'" :class="name != null ? 'text-left' : 'text-center'"
                    autofocus />

            </div>
            @error('name')
                <span class="validator-hint">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-full">Save<span
                wire:loading.class="loading loading-bars loading-lg"></span></button>
    </form>
    <template x-on:rightSidebarClose.window="rightSidebarOpen = false; console.log('Close That Bitcht')"></template>
</section>
