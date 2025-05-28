<?php
use App\Models\TransactionCategory;
use App\Models\CategoryGroup;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Carbon\Carbon;

new class extends Component {
    public $categories;
    public $groups;
    public $refreshKey;

    public $category;
    public $transactions;

    public function mount($modelId)
    {
        $this->category = TransactionCategory::findOrFail($modelId);
        $this->loadTransactions();
    }

    public function loadTransactions()
    {
        $transactions = $this->category->transactions->sortByDesc('amount');

        $this->transactions = $transactions
            ->groupBy(function ($transaction) {
                $date = Carbon::parse($transaction->created_at);
                return $date->format('F Y');
            })
            ->all();
    }

    public function formatShortAmount($amount)
    {
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1) . 'M';
        } elseif ($amount >= 1_000) {
            return number_format($amount / 1_000, 1) . 'K';
        }
        return number_format($amount, 2);
    }
}; ?>

<section>
    @php
        $percentage =
            ($category->transactions->where('type_id', 2)->sum('amount') / $category->budgets->limit_amount) * 100;
    @endphp
    <div x-transition class="space-y-2 text-sm  p-4 rounded-lg">
        <div class="flex justify-between">
            <span class="text-base-content/70">Budget Limit</span>
            <span class="text-4xl font-bold">₱{{ number_format($category->budgets->limit_amount ?? 0, 2) }}</span>
        </div>
        <div
            class="flex justify-between @if ($percentage > 0 && $percentage < 50) text-success @elseif($percentage >= 50 && $percentage < 100) text-warning @else text-error @endif">
            <span class="text-base-content/70">Total Spent</span>
            <span
                class="font-medium ">₱{{ number_format($category->transactions->where('type_id', '2')->sum('amount') ?? 0, 2) }}</span>
        </div>

        <progress
            class="progress @if ($percentage > 0 && $percentage < 50) progress-success @elseif($percentage >= 50 && $percentage < 100) progress-warning @else progress-error @endif w-full mt-2"
            value="{{ $category->transactions->where('type_id', '2')->sum('amount') ?? 0 }}"
            max="{{ $category->budgets->limit_amount ?? 1 }}"></progress>
    </div>

    <div class="w-full mt-10">
        @if (count($transactions))
            <ul class="list bg-base-100 space-y-4">
                @foreach ($transactions as $date => $record)
                    @php
                        $totalIncome = $record->where('type_id', '1')->sum('amount');
                        $totalExpense = $record->where('type_id', '2')->sum('amount');
                    @endphp
                    <li
                        class="bg-base-200 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm flex justify-between">
                        <div class="flex items-center gap-2">
                            <!-- <input type="checkbox" class="checkbox " /> -->
                            <div class="flex items-center gap-2"
                                @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.create'}); rightSidebarOpen = true;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4 text-base-content/70">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                {{ $date }}
                            </div>
                        </div>
                        <div
                            class="text-right text-md w-1/2  font-semibold @if ($percentage > 0 && $percentage < 50) text-success @elseif($percentage >= 50 && $percentage < 100) text-warning @else text-error @endif">
                            <span class="truncate inline-block md:hidden w-1/2 md:w-auto">
                                ₱{{ $this->formatShortAmount($totalExpense) }}
                            </span>
                            <span class="truncate hidden md:inline-block w-1/2 md:w-auto">
                                ₱{{ number_format($totalExpense, 2) }}
                            </span>
                        </div>
                    </li>

                    @foreach ($record as $transaction)
                        <li
                            class="group list-row hover:bg-base-200 flex items-center justify-between w-full p2-5 py-4 border border-base-200 mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer">
                            <!-- Red for Expense, Green for Income -->
                            <div class="flex flex-row md:items-center w-full grow"
                                @click="$dispatch('showRightSidebar', {operation: 'view', page: 'Transaction', component: 'pages.user.transactions.view', modelId: {{ $transaction->id }}}); rightSidebarOpen = true;">
                                <!-- Transaction Name -->
                                <div
                                    class="w-1/3 truncate font-bold text-md mb-1.5 mr-2 text-base-content transition-colors duration-200 {{ $transaction->types->name == 'Expense' ? 'group-hover:text-secondary' : 'group-hover:text-primary' }}">
                                    {{ $transaction->name }}
                                </div>

                                <!-- Contribution -->
                                <div class="w-1/3 truncate uppercase font-semibold hidden md:flex">
                                    <progress
                                        class="progress @if ($percentage > 0 && $percentage < 50) progress-success @elseif($percentage >= 50 && $percentage < 100) progress-warning @else progress-error @endif w-full mt-2"
                                        value="{{ $transaction->amount }}"
                                        max="{{ $category->transactions->where('type_id', 2)->sum('amount') }}"></progress>
                                </div>

                                <!-- Amount -->
                                <div class="w-1/3 flex-shrink-0 text-right grow">
                                    <span
                                        class="text-xs uppercase font-semibold badge badge-lg truncate w-3/4 md:w-auto  {{ $transaction->types->name == 'Expense' ? 'badge-secondary ' : 'badge-primary ' }}">
                                        <span class="md:hidden">
                                            {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ $this->formatShortAmount($transaction->amount) }}
                                        </span>
                                        <span class="hidden md:inline">
                                            {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                @endforeach
            </ul>
        @else
            <div class="flex flex-col items-center justify-center p-10 bg-base-200/30  border border-dashed rounded-xl "
                :class="$wire.type_id == 1 ? 'border-primary' : 'border-secondary'">
                <span class="text-base-content text-lg font-medium mb-1">
                    😴 No transactions found
                </span>
            </div>
        @endif
    </div>

</section>
