<?php

use Illuminate\Support\Facades\Auth;
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public $accounts;
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
        $this->accounts = \App\Models\Account::where('user_id', Auth::id())
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
    }
}; ?>
<div class="drawer-side z-50">
    <label for="left-sidebar-drawer" class="drawer-overlay"></label>
    <div class="menu pt-2 w-55 bg-base-100 min-h-full text-base-content flex flex-col">
        <button class="btn btn-ghost bg-base-300  btn-circle z-50 top-0 right-0 mt-4 mr-2 absolute lg:hidden"><svg
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" aria-hidden="true" class="h-5 inline-block w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg></button>
        <div class="mb-2 p-1  font-semibold text-xl">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <div class="mask mask-squircle w-10 mr-1">
                    <img src="{{ asset('img/sample-logo.png') }}" alt="Logo">
                </div>
                Gastababy
            </a>
        </div>

        <!-- Menu Items -->
        <div>
            <ul class="text-md">
                <!-- Dashboard -->
                <li class="">
                    <a aria-current="page"
                        class="{{ request()->routeIs('dashboard') ? 'bg-base-200 font-semibold' : 'font-normal' }}"
                        href="{{ route('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z">
                            </path>
                        </svg>
                        Dashboard
                        @if (request()->routeIs('dashboard'))
                            <span class="absolute inset-y-0 left-0 w-1 rounded-tr-md rounded-br-md bg-primary "
                                aria-hidden="true"></span>
                        @endif
                    </a>
                </li>

                <!-- Transactions -->
                <li class="">
                    <a class="{{ request()->routeIs('user.transactions') ? 'bg-base-200 font-semibold' : 'font-normal' }}"
                        href="{{ route('user.transactions') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        Transactions

                        @if (request()->routeIs('user.transactions'))
                            <span class="absolute inset-y-0 left-0 w-1 rounded-tr-md rounded-br-md bg-primary "
                                aria-hidden="true"></span>
                        @endif
                    </a>
                </li>

                <!-- Accounts -->
                <li class="">
                    <a class="{{ request()->routeIs('user.accounts') ? 'bg-base-200 font-semibold' : 'font-normal' }}"
                        href="{{ route('user.accounts') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        Accounts

                        @if (request()->routeIs('user.accounts'))
                            <span class="absolute inset-y-0 left-0 w-1 rounded-tr-md rounded-br-md bg-primary "
                                aria-hidden="true"></span>
                        @endif
                    </a>
                </li>

                <!-- Save Goals -->
                <li class="">
                    <a class="{{ request()->routeIs('user.goals') ? 'bg-base-200 font-semibold' : 'font-normal' }}"
                        href="/app/leads">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3">
                            </path>
                        </svg>
                        Save Goals
                        @if (request()->routeIs('user.goals'))
                            <span class="absolute inset-y-0 left-0 w-1 rounded-tr-md rounded-br-md bg-primary "
                                aria-hidden="true"></span>
                        @endif
                    </a>
                </li>

                <!-- Categories -->
                <li class="">
                    <a class="{{ request()->routeIs('user.categories') ? 'bg-base-200 font-semibold' : 'font-normal' }}"
                        href="/app/leads">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
                        </svg>
                        Categories
                        @if (request()->routeIs('user.categories'))
                            <span class="absolute inset-y-0 left-0 w-1 rounded-tr-md rounded-br-md bg-primary "
                                aria-hidden="true"></span>
                        @endif
                    </a>
                </li>

                <!-- Recurring -->
                <li class="">
                    <a class="{{ request()->routeIs('user.recurring') ? 'bg-base-200 font-semibold' : 'font-normal' }}"
                        href="/app/leads">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" />
                        </svg>
                        Recurring
                        @if (request()->routeIs('user.recurring'))
                            <span class="absolute inset-y-0 left-0 w-1 rounded-tr-md rounded-br-md bg-primary "
                                aria-hidden="true"></span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="divider"></div>
        </div>

        <!-- Account Wallets -->

        <div class="grow">
            <ul>
                <li>
                    <details open>
                        <summary>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                            Account
                        </summary>
                        <ul>
                            <li>
                                @foreach ($accounts as $categoryName => $records)
                                    <details open>
                                        <summary class="p-4 pb-2 text-xs opacity-60 tracking-wide">
                                            {{ $categoryName }}
                                        </summary>
                                        @foreach ($records as $account)
                                            <ul>
                                                <li>
                                                    <div>
                                                        <div class="">{{ $account['name'] }}</div>
                                                    </div>

                                                </li>
                                            </ul>
                                        @endforeach
                                    </details>
                                @endforeach
                            </li>
                        </ul>
                    </details>
                </li>
            </ul>
        </div>

        <!-- User Buttons -->
        <div class="sticky bottom-0 w-full bg-base-100">
            <div class="divider mb-0"></div>
            <div class="dropdown dropdown-top w-full">
                <div tabindex="0" role="button"
                    class="bg-base-200 hover:bg-base-100 rounded-box mx-2 mb-2 flex cursor-pointer items-center gap-2.5 px-3 py-2 transition-all">
                    <div class="avatar">
                        <div class="bg-base-200 mask mask-squircle w-8">
                            <img class="avatar"
                                src="{{ auth()->user()->profile_image_url ? asset('app/' . auth()->user()->profile_image_url) : asset('img/user-img.jpg') }}"
                                alt="Profile photo">
                        </div>
                    </div>
                    <div class="grow min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->first_name }}
                            {{ auth()->user()->last_name[0] }}.</p>
                        <p class="text-base-content/60 text-xs truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                </div>
                <ul tabindex="0"
                    class="dropdown-content menu menu-sm bg-base-200 rounded-box w-52 p-2 shadow-lg mb-2 max-h-96 overflow-y-auto z-50">
                    <li>
                        <a href="{{ route('profile') }}" wire:navigate class="!rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="!rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <a wire:click="logout" class="!rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5">
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
