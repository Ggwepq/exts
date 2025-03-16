<?php
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public $accounts;
    public $totalBalance;

    public function mount()
    {
        $this->loadAccounts();
    }

    #[On('accountCreated')]
    public function loadAccounts()
    {
        $this->accounts = Account::with('accountCategories')
            ->orderByRaw('category_id IS NOT NULL') // "null" comes first
            ->orderBy(function ($query) {
                $query->select('created_at')->from('account_categories')->whereColumn('account_categories.id', 'accounts.category_id')->limit(1);
            })
            ->get()
            ->groupBy(function ($accounts) {
                $name = $accounts->accountCategories ? $accounts->accountCategories->name : 'None';

                return $name;
            })
            ->toArray();

        $this->getTotal();
    }

    public function getTotal()
    {
        $this->totalBalance = Auth::user()->accounts->sum('balance');
    }
}; ?>

<section class="flex min-h-screen">
    <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6  bg-base-200">

        <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
            <ul class="list bg-base-100 rounded-box shadow-md">
                <div role="alert" class="alert alert-info alert-soft">
                    <span>Total Balance: ₱{{ number_format($totalBalance) }}</span>
                </div>
                @foreach ($accounts as $categoryName => $records)
                    <li class="p-4 pb-2 text-xs opacity-60 tracking-wide">{{ $categoryName }}</li>

                    @foreach ($records as $account)
                        <li class="list-row">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-10">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                            </div>

                            <div>
                                <div class="text-lg font-bold">{{ $account['name'] }}</div>
                            </div>

                            <div>
                                <div class="text-sm uppercase font-semibold badge badge-outline badge-primary">
                                    ₱{{ number_format($account['balance'], 2) }}
                                </div>
                            </div>
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    </div>
    @livewire('pages.user.containers.details-sidebar')
</section>
