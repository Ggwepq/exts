<?php

use App\Models\Transaction;
use Livewire\Volt\Component;

new class extends Component {
    public $transactions;

    public function mount()
    {
        $transactions = auth()->user()->transactions;

        $this->transactions = [
            'totalExpense' => $transactions->where('type_id', 1)->sum('amount'),
            'totalIncome' => $transactions->where('type_id', 2)->sum('amount'),
            'expenseCount' => count($transactions->where('type_id', 1)),
            'incomeCount' => count($transactions->where('type_id', 2)),
        ];
    }
}; ?>

<div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6  bg-base-200">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    </div>

    <div class="grid lg:grid-cols-4 mt-2 md:grid-cols-2 grid-cols-1 gap-6">
        <div class="stats shadow">
            <div class="stat bg-base-100">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <div class="stat-title">Income {{ $transactions['incomeCount'] }}</div>
                <div class="stat-value text-primary">{{ number_format($transactions['totalIncome']) }}</div>
            </div>
        </div>
        <div class="stats shadow">
            <div class="stat bg-base-100">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <div class="stat-title ">Expense {{ $transactions['expenseCount'] }}</div>
                <div class="stat-value text-primary">{{ number_format($transactions['totalExpense']) }}</div>
            </div>
        </div>
    </div>

</div>
