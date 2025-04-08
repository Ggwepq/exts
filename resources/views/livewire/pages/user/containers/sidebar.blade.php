<?php

use Illuminate\Support\Facades\Auth;
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public $categorizedAccounts;
    public $uncategorizedAccounts;
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function mount()
    {
        $accounts = \App\Models\Account::where('user_id', Auth::id())
            ->with('accountCategories')
            ->orderByRaw('category_id IS NOT NULL') // "null" comes first
            ->orderBy(function ($query) {
                $query->select('created_at')->from('account_categories')->whereColumn('account_categories.id', 'accounts.category_id')->limit(1);
            })
            ->get();

        // Handle categorized accounts
        $this->categorizedAccounts = $accounts
            ->filter(function ($account) {
                return $account->accountCategories !== null;
            })
            ->groupBy(function ($account) {
                return $account->accountCategories->name;
            })
            ->toArray();

        // Get uncategorized accounts
        $this->uncategorizedAccounts = $accounts
            ->filter(function ($account) {
                return $account->accountCategories === null;
            })
            ->toArray();
    }
}; ?>
<div class="drawer-side z-50">
    <label for="left-sidebar-drawer" class="drawer-overlay"></label>
    <div
        class="menu pt-2 w-64 bg-gradient-to-b from-base-100 to-base-100/95 min-h-full text-base-content flex flex-col border-r border-base-200 shadow-md">
        <button class="btn btn-ghost bg-base-300 btn-circle z-50 top-0 right-0 mt-4 mr-2 absolute lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" aria-hidden="true" class="h-5 inline-block w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="px-4 py-3 mb-2">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="mask mask-squircle w-10 bg-primary/10 flex items-center justify-center p-1">
                    <img src="{{ asset('img/sample-logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                </div>
                <span class="font-bold text-xl text-primary">Gastababy</span>
            </a>
        </div>

        <!-- Menu Items -->
        <div class="px-2">
            <ul class="space-y-1.5">
                <!-- Dashboard -->
                <li>
                    <a aria-current="page"
                        class="{{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary font-semibold shadow-sm border border-primary/10' : 'hover:bg-base-200 font-normal' }} flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200"
                        href="{{ route('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5-1.5m-.5 1.5h-9.5m0 0l-.5-1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                        </svg>
                        Dashboard
                    </a>
                </li>

                <!-- Transactions -->
                <li>
                    <a class="{{ request()->routeIs('user.transactions') ? 'bg-primary/10 text-primary font-semibold shadow-sm border border-primary/10' : 'hover:bg-base-200 font-normal' }} flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200"
                        href="{{ route('user.transactions') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        Transactions
                    </a>
                </li>

                <!-- Accounts -->
                <li>
                    <a class="{{ request()->routeIs('user.accounts') ? 'bg-primary/10 text-primary font-semibold shadow-sm border border-primary/10' : 'hover:bg-base-200 font-normal' }} flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200"
                        href="{{ route('user.accounts') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        Accounts
                    </a>
                </li>

                <!-- Save Goals -->
                <li>
                    <a class="{{ request()->routeIs('user.goals') ? 'bg-primary/10 text-primary font-semibold shadow-sm border border-primary/10' : 'hover:bg-base-200 font-normal' }} flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200"
                        href="/app/leads">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3">
                            </path>
                        </svg>
                        Save Goals
                    </a>
                </li>

                <!-- Categories -->
                <li>
                    <a class="{{ request()->routeIs('user.categories') ? 'bg-primary/10 text-primary font-semibold shadow-sm border border-primary/10' : 'hover:bg-base-200 font-normal' }} flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200"
                        href="/app/leads">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
                        </svg>
                        Categories
                    </a>
                </li>

                <!-- Recurring -->
                <li>
                    <a class="{{ request()->routeIs('user.recurring') ? 'bg-primary/10 text-primary font-semibold shadow-sm border border-primary/10' : 'hover:bg-base-200 font-normal' }} flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200"
                        href="/app/leads">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" />
                        </svg>
                        Recurring
                    </a>
                </li>
            </ul>

            <div class="divider my-3"></div>
        </div>

        <!-- Account Wallets -->
        <div class="grow overflow-y-auto px-3">
            <div class="bg-primary/5 rounded-lg px-4 py-2 mb-2 flex items-center justify-between">
                <span class="text-sm font-medium text-primary">ACCOUNTS</span>
                <span
                    class="badge badge-sm badge-primary text-xs">{{ count($uncategorizedAccounts) + array_sum(array_map('count', $categorizedAccounts)) }}</span>
            </div>
            <ul class="mt-1 space-y-1.5">
                <ul class="ml-3 mt-2 space-y-1">
                    <!-- Display uncategorized accounts first, directly without a nested dropdown -->
                    @if (count($uncategorizedAccounts) > 0)
                        @foreach ($uncategorizedAccounts as $account)
                            <li>
                                <a
                                    class="flex items-center justify-between px-3 py-2 text-sm rounded-md hover:bg-base-200 transition-all duration-200 group">
                                    <span class="truncate group-hover:text-primary">{{ $account['name'] }}</span>
                                    <span
                                        class="badge badge-sm badge-primary text-xs">₱{{ number_format($account['balance'], 0) }}</span>
                                </a>
                            </li>
                        @endforeach

                        @if (count($categorizedAccounts) > 0)
                            <div class="divider my-1 h-px"></div>
                        @endif
                    @endif

                    <!-- Display categorized accounts -->
                    @foreach ($categorizedAccounts as $categoryName => $records)
                        <li>
                            <details open>
                                <summary
                                    class="px-3 py-2 text-xs font-medium flex items-center justify-between cursor-pointer rounded hover:bg-base-200 transition-all duration-200 bg-base-200/50">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor"
                                            class="w-3.5 h-3.5 text-primary/70">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        <span>{{ $categoryName }}</span>
                                    </div>
                                    <span class="badge badge-xs badge-ghost">{{ count($records) }}</span>
                                </summary>
                                <ul class="ml-2 mt-1.5 space-y-1">
                                    @foreach ($records as $account)
                                        <li>
                                            <a
                                                class="flex items-center justify-between px-3 py-2 text-sm rounded-md hover:bg-base-200 transition-all duration-200 group">
                                                <span
                                                    class="truncate group-hover:text-primary">{{ $account['name'] }}</span>
                                                <span
                                                    class="badge badge-sm badge-primary text-xs">₱{{ number_format($account['balance'], 0) }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </ul>
        </div>

        <!-- User Buttons -->
        <div class="sticky bottom-0 w-full bg-base-100 shadow-[0_-2px_4px_rgba(0,0,0,0.05)]">
            <div class="divider my-0 h-px"></div>
            <div class="dropdown dropdown-top w-full">
                <div tabindex="0" role="button"
                    class="flex items-center gap-3 p-3 m-2 rounded-lg hover:bg-base-200 cursor-pointer transition-all border bg-gradient-to-t from-primary/20 to-primary/10 border-base-200">
                    <div class="avatar">
                        <div class="mask mask-squircle w-10 h-10 shadow-sm border border-base-200">
                            <img src="{{ auth()->user()->profile_image_url ? asset('app/' . auth()->user()->profile_image_url) : asset('img/user-img.jpg') }}"
                                alt="Profile photo">
                        </div>
                    </div>
                    <div class="grow min-w-0">
                        <p class="font-medium text-sm truncate">{{ auth()->user()->first_name }}
                            {{ auth()->user()->last_name[0] }}.</p>
                        <p class="text-base-content/60 text-xs truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                </div>
                <ul tabindex="0"
                    class="dropdown-content menu bg-base-100 rounded-lg w-56 p-2 shadow-lg mb-2 border border-base-200">
                    <li>
                        <a href="{{ route('profile') }}" wire:navigate
                            class="flex items-center gap-2 px-4 py-2 hover:bg-base-200 rounded-lg transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a
                            class="flex items-center gap-2 px-4 py-2 hover:bg-base-200 rounded-lg transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            </svg>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <a wire:click="logout" class="flex items-center gap-2 px-4 py-2 text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                            </svg>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
