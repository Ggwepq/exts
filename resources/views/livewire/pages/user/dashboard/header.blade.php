<?php
use Livewire\Volt\Component;

new class extends Component {
    // Component logic here
}; ?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        @livewire('pages.user.components.sidebar-button')
        <div class="bg-primary/20 p-3 rounded-lg hidden md:flex">

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-8 text-primary">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5-1.5m-.5 1.5h-9.5m0 0l-.5-1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-base-content">Dashboard</h1>
        </div>
    </div>

    <div class="flex gap-2 items-center">
        <!-- Notification Button -->
        <button class="btn btn-ghost btn-sm bg-base-100 hover:bg-base-200 border border-base-300 shadow-sm">
            <div class="indicator">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="indicator-item badge badge-primary badge-xs">3</span>
            </div>
        </button>
    </div>
</div>
