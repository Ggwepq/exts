<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>
<div class="drawer-side z-30">
    <label for="left-sidebar-drawer" class="drawer-overlay"></label>
    <ul class="menu pt-2 w-80 bg-base-100 min-h-full text-base-content">
        <button class="btn btn-ghost bg-base-300 btn-circle z-50 top-0 right-0 mt-4 mr-2 absolute lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <li class="mb-2 font-semibold text-xl">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <div class="mask mask-squircle w-10">
                    <img src="{{ asset('img/logo192.png') }}" alt="Logo">
                </div>
                Gastababy
            </a>
        </li>

        <!-- Example Menu Items -->
        <ul class="text-lg">
            <li><a href="#" class="active">📈 Dashboard</a></li>
            <li><a href="#">💰 Transactions</a></li>
            <li><a href="#">🗃 Categories</a></li>
            <li><a href="#">💵 Budgets</a></li>
            <li><a href="#">♻ Recurring</a></li>
            <li><a href="#">👤 Accounts</a></li>
            <li><a href="#">⚙ Settings</a></li>

        </ul>
    </ul>
</div>
