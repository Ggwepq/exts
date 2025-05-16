<?php

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Livewire\Actions\User\BulkTransactionUpdater;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public array $transactionIds = [];
    public array $bulkData = [
        'name' => null,
        'account_id' => null,
        'category_id' => null,
        'type_id' => null,
        'tags' => [],
    ];
    public $accounts = [];
    public $categories = [];
    public $typeOptions = [
        1 => 'Income',
        2 => 'Expense',
    ];
    public $selectedTags = [];
    public $availableTags = [];

    public $fieldsToUpdate = [
        'name' => false,
        'account' => false,
        'category' => false,
        'type' => false,
        'tags' => false,
    ];

    public $incomes;
    public $expenses;

    public function mount($modelId)
    {
        $this->transactionIds = is_array($modelId) ? $modelId : [$modelId];
        $this->accounts = Account::all();
        $this->incomes = TransactionCategory::where('user_id', Auth::id())->where('type_id', 1)->get();
        $this->expenses = TransactionCategory::where('user_id', Auth::id())->where('type_id', 2)->get();
        $this->availableTags = $this->getAllTags();
    }

    protected function getAllTags()
    {
        // Retrieve all available tags from your database
        // This is a placeholder - implement based on your tag system
        return []; // Replace with actual tag retrieval
    }

    public function save()
    {
        // Validate only the fields that are selected for update
        $validationRules = [];

        if ($this->fieldsToUpdate['name']) {
            $validationRules['bulkData.name'] = 'required';
        }

        if ($this->fieldsToUpdate['account']) {
            $validationRules['bulkData.account_id'] = 'required|exists:accounts,id';
        }

        if ($this->fieldsToUpdate['category']) {
            $validationRules['bulkData.category_id'] = 'nullable|exists:transaction_categories,id';
        }

        if ($this->fieldsToUpdate['type']) {
            $validationRules['bulkData.type_id'] = 'required|in:1,2';
        }

        if (!empty($validationRules)) {
            $this->validate($validationRules);
        }

        // Build update data array based on selected fields
        $updateData = [];

        if ($this->fieldsToUpdate['name']) {
            $updateData['name'] = $this->bulkData['name'];
        }

        if ($this->fieldsToUpdate['account']) {
            $updateData['account_id'] = $this->bulkData['account_id'];
        }

        if ($this->fieldsToUpdate['category']) {
            $updateData['category_id'] = $this->bulkData['category_id'];
        }

        if ($this->fieldsToUpdate['type']) {
            $updateData['type_id'] = $this->bulkData['type_id'] == 1 ? 1 : 2;
        }
        // dd($this, $updateData, $validationRules);

        // Only proceed if there's data to update
        if (!empty($updateData)) {
            (new BulkTransactionUpdater())->execute($this->transactionIds, $updateData, $this->fieldsToUpdate['tags'] ? $this->selectedTags : null);

            $this->dispatch('transactionUpdate');
            $this->dispatch('rightSidebarClose');
        } else {
            Toaster::warning('No Fields Selected');
        }
    }

    public function cancel()
    {
        $this->dispatch('rightSidebarClose');
    }

    public function getCategory($type_id)
    {
        if ($type_id == 1) {
            $this->categories = $this->incomes;
        } else {
            $this->categories = $this->expenses;
        }
    }
};
?>

<div class="w-full">
    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Name -->
        <div class="form-control">
            <div class="flex items-center mb-2">
                <input type="checkbox" wire:model.live="fieldsToUpdate.name" class="checkbox checkbox-primary"
                    id="update-name">
                <label for="update-name" class="ml-2 text-base-content font-medium">Update Name</label>
            </div>
            <div class="@if (!$fieldsToUpdate['name']) opacity-50 pointer-events-none @endif">
                <input type="text" placeholder="Name" class="input" wire:model="bulkData.name"
                    @if (!$fieldsToUpdate['name']) disabled @endif />
                @error('bulkData.name')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Account Selection -->
        <div class="form-control">
            <div class="flex items-center mb-2">
                <input type="checkbox" wire:model.live="fieldsToUpdate.account" class="checkbox checkbox-primary"
                    id="update-account">
                <label for="update-account" class="ml-2 text-base-content font-medium">Update Account</label>
            </div>
            <div class="@if (!$fieldsToUpdate['account']) opacity-50 pointer-events-none @endif">
                <select wire:model="bulkData.account_id" class="select select-bordered w-full"
                    @if (!$fieldsToUpdate['account']) disabled @endif>
                    <option value="">-- Select Account --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                @error('bulkData.account_id')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Transaction Type -->
        <div class="form-control">
            <div class="flex items-center mb-2">
                <input type="checkbox" wire:model.live="fieldsToUpdate.type" class="checkbox checkbox-primary"
                    id="update-type">
                <label for="update-type" class="ml-2 text-base-content font-medium">Update Type</label>
            </div>
            <div class="@if (!$fieldsToUpdate['type']) opacity-50 pointer-events-none @endif">
                <select wire:model="bulkData.type_id" class="select select-bordered w-full"
                    @if (!$fieldsToUpdate['type']) disabled @endif>
                    <option value="">-- Select Type --</option>
                    @foreach ($typeOptions as $value => $label)
                        <option @click="$wire.getCategory({{ $value }})" value="{{ $value }}">
                            {{ $label }}</option>
                    @endforeach
                </select>
                @error('bulkData.type_id')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Category Selection -->
        <div class="form-control">
            <div class="flex items-center mb-2">
                <input type="checkbox" wire:model.live="fieldsToUpdate.category" class="checkbox checkbox-primary"
                    id="update-category">
                <label for="update-category" class="ml-2 text-base-content font-medium">Update Category</label>
            </div>
            <div class="@if (!$fieldsToUpdate['category']) opacity-50 pointer-events-none @endif">
                <select wire:model="bulkData.category_id" class="select select-bordered w-full"
                    @if (!$fieldsToUpdate['category']) disabled @endif>
                    <option value="">-- Select Category --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('bulkData.category_id')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Tags Selection - Implement based on how you handle tags in your application -->
        <div class="form-control">
            <div class="flex items-center mb-2">
                <input type="checkbox" wire:model.live="fieldsToUpdate.tags" class="checkbox checkbox-primary"
                    id="update-tags">
                <label for="update-tags" class="ml-2 text-base-content font-medium">Update Tags</label>
            </div>
            <div class="@if (!$fieldsToUpdate['tags']) opacity-50 pointer-events-none @endif">
                <!-- Add your tag selection UI here - this will depend on how you handle tags -->
                <div class="select-multiple border border-base-300 rounded-lg p-2">
                    <p class="text-sm text-base-content/70 mb-2">Select tags to apply to all transactions</p>

                    <!-- Example of tag selection, modify based on your tag system -->
                    <div class="flex flex-wrap gap-2">
                        @foreach ($availableTags as $tag)
                            <label
                                class="inline-flex items-center gap-1 bg-base-200 px-2 py-1 rounded-lg cursor-pointer">
                                <input type="checkbox" wire:model="selectedTags" value="{{ $tag->id }}"
                                    class="checkbox checkbox-xs" @if (!$fieldsToUpdate['tags']) disabled @endif>
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('selectedTags')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <button type="button" wire:click="cancel" class="btn btn-ghost">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Transactions</button>
        </div>
    </form>
</div>
