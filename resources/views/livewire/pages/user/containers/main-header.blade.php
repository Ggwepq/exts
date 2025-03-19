<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="navbar sticky top-0 bg-base-100 z-10 shadow-md">
    @livewire('pages.user.components.sidebar-button')
    <div class="flex-1">
        @if (request()->routeIs('dashboard'))
            @livewire('pages.user.dashboard.header')
        @elseif (request()->routeIs('user.transactions'))
            @livewire('pages.user.transactions.header')
        @elseif (request()->routeIs('user.accounts'))
            @livewire('pages.user.accounts.header')
        @endif
    </div>
</div>
