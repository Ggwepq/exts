<?php

use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {}; ?>

<section>
    <!-- Sidebar Backdrop (Mobile Only) -->
    <div x-show="isOpen" x-transition.duration.500 @click.away="isOpen = false"
        class="fixed inset-y-0 bg-base-300 z-40 md:hidden" wire:click="closeDetail"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:leave="transition-opacity ease-linear duration-300">
    </div>

    <!-- Sidebar -->
    <div x-show="isOpen" x-transition.duration.500 @click.away="isOpen = false"
        class="fixed md:sticky top-0 start-0 bottom-0 right-0 w-full md:w-96 bg-base-100 border-l border-base-200 shadow-lg z-50 md:relative md:transform-none transform transition-transform duration-300"
        :class="isOpen ? 'translate-x-0' : 'translate-x-full'">

        <div class="p-6 h-full overflow-y-auto">
            @if (request()->routeIs('user.transactions'))
                @livewire('pages.user.transactions.add')
            @elseif (request()->routeIs('user.accounts'))
                @livewire('pages.user.accounts.add')
            @endif
        </div>
    </div>
</section>
