<?php
use App\Models\TransactionCategory;
use App\Models\CategoryGroup;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;

new class extends Component {
    public $category;
    public $budget;

    public $limit_amount;

    public function mount($modelId)
    {
        $this->category = TransactionCategory::findOrFail($modelId);
        $this->budget = $this->category->budgets;

        $this->limit_amount = $this->budget->limit_amount ?? 0;
    }

    public function save()
    {
        $this->budget->limit_amount = $this->limit_amount;
        $this->budget->save();
        $this->dispatch('categoryUpdate');
        $this->dispatch('showSidebar', operation: 'view', page: 'Budget', component: 'pages.user.budgets.view', modelId: $this->category->id);
    }
}; ?>
@php
    $totalSpent = $category->transactions->where('type_id', 2)->sum('amount');
    $limitAmount = $category->budgets->limit_amount ?? 0;
    $percentage = $limitAmount > 0 ? ($totalSpent / $limitAmount) * 100 : 0;
@endphp

<section>
    <form wire:submit="save">
        <div x-transition class="space-y-2 text-sm p-4 rounded-lg">
            <div class="flex justify-between">
                <span class="text-base-content/70">Budget Limit</span>

                <!-- Editable Budget Limit -->

                <div x-data="{
                    editing: false,
                    limit: @entangle('limit_amount'),
                    formatted() {
                        const num = parseFloat(this.limit || 0);
                        return num.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
                    }
                }" class="relative text-right">
                    <span x-show="!editing" @click="editing = true; $nextTick(() => $refs.limitInput.focus())"
                        class="cursor-pointer font-bold text-4xl block truncate text-base-content" x-text="formatted()">
                    </span>

                    <input x-show="editing" x-ref="limitInput" x-model="limit" wire:model.lazy="limit_amount"
                        @click.away="editing = false" type="number" step="0.01" placeholder="Enter limit"
                        class="input input-ghost text-right text-4xl font-bold bg-transparent outline-none border-none" />
                </div>
            </div>

            <div
                class="flex justify-between @if ($percentage > 0 && $percentage < 50) text-success @elseif($percentage >= 50 && $percentage < 100) text-warning @else text-error @endif">
                <span class="text-base-content/70">Total Spent</span>
                <span class="font-medium">₱{{ number_format($totalSpent, 2) }}</span>
            </div>

            <progress
                class="progress @if ($percentage > 0 && $percentage < 50) progress-success @elseif($percentage >= 50 && $percentage < 100) progress-warning @else progress-error @endif w-full mt-2"
                value="{{ $totalSpent }}" max="{{ $limitAmount }}"></progress>
        </div>

        <div class="form-control">
            <button type="submit" class="btn w-full">Save</button>
        </div>
    </form>
</section>
