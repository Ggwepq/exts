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

        $this->budget = $this->category->budgets()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->first();

        $this->limit_amount = $this->budget->limit_amount ?? 0;
    }

    public function save()
    {
        $this->validate([
            'limit_amount' => 'required|numeric|min:0',
        ]);

        $budget =
            $this->budget ??
            $this->category->budgets()->create([
                'limit_amount' => $this->limit_amount,
            ]);

        $budget->limit_amount = $this->limit_amount;
        $budget->save();

        $this->budget = $budget;

        $this->dispatch('categoryUpdate');
        $this->dispatch('showSidebar', operation: 'view', page: 'Budget', component: 'pages.user.budgets.view', modelId: $this->category->id);
    }
}; ?>
@php
    $totalSpent = $category->transactions
        ->where('type_id', 2)
        ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
        ->sum('amount');

    $limitAmount = $limit_amount;
    $percentage = $limitAmount > 0 ? ($totalSpent / $limitAmount) * 100 : 0;
@endphp

<section>
    <form wire:submit.prevent="save">
        <div class="space-y-4 text-sm p-4 rounded-lg">

            <div class="flex justify-between items-center">
                <span class="text-base-content/70">Budget Limit (This Month)</span>

                <div x-data="{
                    editing: false,
                    limit: @entangle('limit_amount'),
                    formatted() {
                        return Number(this.limit || 0).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
                    }
                }" class="relative text-right w-2/3">
                    <span x-show="!editing" @click="editing = true; $nextTick(() => $refs.limitInput.focus())"
                        class="cursor-pointer font-bold text-3xl block truncate" x-text="formatted()">
                    </span>

                    <input x-show="editing" x-ref="limitInput" x-model="limit" wire:model.lazy="limit_amount"
                        @click.away="editing = false" step="0.01"
                        class="input input-ghost text-right text-3xl font-bold bg-transparent border-none outline-none w-full"
                        placeholder="₱0.00" />
                </div>
            </div>

            <div class="flex justify-between text-base-content">
                <span>Total Spent</span>
                <span class="font-semibold">
                    ₱{{ number_format($totalSpent, 2) }}
                </span>
            </div>

            <progress
                class="progress w-full mt-2
                    @if ($percentage < 50) progress-success
                    @elseif ($percentage < 100) progress-warning
                    @else progress-error @endif"
                value="{{ $totalSpent }}" max="{{ $limitAmount }}">
            </progress>
        </div>

        <div class="form-control mt-4">
            <button type="submit" class="btn btn-primary w-full">Save</button>
        </div>
    </form>
</section>
