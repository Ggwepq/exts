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

    #[On('accountUpdate')]
    public function loadAccounts()
    {
        $user = Account::where('user_id', Auth::id());

        $this->accounts = $user
            ->with('accountCategories')
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
        $this->getBalance();
    }

    public function getTotal()
    {
        $this->totalBalance = Auth::user()->accounts->sum('balance');
    }

    public function getBalance()
    {
        $expense = Auth::user()->transactions->where('type_id', 2)->sum('amount');
        $income = Auth::user()->transactions->where('type_id', 1)->sum('amount');
        $total = $expense . '/' . $income;
    }
}; ?>

<section>
    <div class="transition-all duration-300 ease-in-out" :class="{ 'md:mr-[23rem]': detailSidebarOpen }">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.accounts.header'])
        <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                @if ($accounts)
                    <!-- Total Balance Banner -->
                    <div
                        class="bg-gradient-to-r from-primary/20 to-primary/5 p-4 mb-6 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="bg-primary/30 p-2.5 ">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6 text-primary-content">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-medium text-base-content/70">Total Balance</h2>
                                <p class="text-xl font-bold text-base-content">₱{{ number_format($totalBalance) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <ul class="list bg-base-100 space-y-4">
                        @foreach ($accounts as $categoryName => $records)
                            <li @click="Toaster.success('CLiekceddasdf')"
                                class="bg-base-200/50 text-sm font-medium py-2 px-4 mb-2 sticky top-0 z-10 backdrop-blur-sm shadow-sm">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4 text-base-content/70">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    {{ $categoryName }}
                                </div>
                            </li>

                            @foreach ($records as $account)
                                <li class="group list-row hover:bg-base-200 flex items-center justify-between w-full px-5 py-4 border border-base-200  mb-3 mx-0.5 transition-all duration-200 hover:shadow-md cursor-pointer"
                                    @click="$dispatch('showSidebar', {operation: 'edit', page: 'Account', component: 'pages.user.accounts.edit', modelId: {{ $account['id'] }}}); detailSidebarOpen = true;">
                                    <div class="flex items-center gap-4">
                                        <div class="bg-primary/10 p-2.5 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5 text-primary">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p
                                                class="text-lg font-bold group-hover:text-primary transition-colors duration-200">
                                                {{ $account['name'] }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <div
                                            class="text-sm uppercase font-semibold badge badge-lg whitespace-nowrap badge-primary">
                                            ₱{{ number_format($account['balance'], 2) }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                @else
                    <div class="flex flex-col items-center justify-center p-10 bg-base-200/30 ">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-16 text-base-300 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        <span class="text-base-content/60 text-lg font-medium mb-1">No accounts found</span>
                        <p class="text-base-content/40 text-sm mb-4">Add an account to start tracking your finances</p>
                        <button class="btn btn-sm btn-primary"
                            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Accounts', component: 'pages.user.accounts.add', modelId: null})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Your First Account
                        </button>
                    </div>
                @endif
            </div>
        </div>
        @livewire('pages.user.containers.details-sidebar', ['lazy' => true])
    </div>
</section>
