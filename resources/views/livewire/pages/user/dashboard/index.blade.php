<?php

use App\Models\Transaction;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $transactions = [
        'totalIncome' => 0,
        'totalExpense' => 0,
        'incomeCount' => 0,
        'expenseCount' => 0
    ];
    
    public $totalAccountBalance = 0;

    public function mount()
    {
        $user = Auth::user();
        
        $this->transactions = [
            'totalIncome' => Transaction::where('user_id', $user->id)->where('type_id', 1)->where('name', '!=', 'Initial Account Balance')->sum('amount'),
            'totalExpense' => Transaction::where('user_id', $user->id)->where('type_id', 2)->sum('amount'),
            'incomeCount' => Transaction::where('user_id', $user->id)->where('type_id', 1)->where('name', 'not like', 'Initial Account Balance')->count(),
            'expenseCount' => Transaction::where('user_id', $user->id)->where('type_id', 2)->count(),
        ];
        
        $this->totalAccountBalance = $user->accounts->sum('balance');
    }
}; ?>

<div>
    @livewire('pages.user.containers.main-header', ['component' => 'pages.user.dashboard.header'])
    <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200">
        <div class="grid lg:grid-cols-2 mt-2 md:grid-cols-2 grid-cols-1 gap-4">
            <!-- Income Card -->
            <div class="card p-6 bg-base-100 shadow-lg border border-base-200 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="bg-primary/10 p-4 rounded-lg mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-8 h-8 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm text-base-content/70 font-medium mb-1">Income ({{ $transactions['incomeCount'] }})</div>
                        <div class="text-3xl font-bold text-primary">₱{{ number_format($transactions['totalIncome']) }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Expense Card -->
            <div class="card p-6 bg-base-100 shadow-lg border border-base-200 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="bg-secondary/10 p-4 rounded-lg mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-8 h-8 text-secondary">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm text-base-content/70 font-medium mb-1">Expenses ({{ $transactions['expenseCount'] }})</div>
                        <div class="text-3xl font-bold text-secondary">₱{{ number_format($transactions['totalExpense']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Balance Summary Card -->
        <div class="card p-6 bg-base-100 shadow-lg border border-base-200 mt-4 hover:shadow-xl transition-all duration-300">
            <h2 class="text-xl font-bold mb-4">Balance Summary</h2>
            <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4">
                <!-- Net Balance -->
                <div class="stat bg-base-200/50 rounded-xl p-4">
                    <div class="stat-title text-base-content/70">Net Balance</div>
                    <div class="stat-value text-lg text-base-content">
                        ₱{{ number_format($totalAccountBalance) }}
                    </div>
                </div>
                
                <!-- Income -->
                <div class="stat bg-primary/5 rounded-xl p-4">
                    <div class="stat-title text-base-content/70">Total Income</div>
                    <div class="stat-value text-lg text-primary">
                        ₱{{ number_format($transactions['totalIncome']) }}
                    </div>
                </div>
                
                <!-- Expense -->
                <div class="stat bg-secondary/5 rounded-xl p-4">
                    <div class="stat-title text-base-content/70">Total Expenses</div>
                    <div class="stat-value text-lg text-secondary">
                        ₱{{ number_format($transactions['totalExpense']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
