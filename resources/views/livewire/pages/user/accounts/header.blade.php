<?php
use Livewire\Volt\Component;

new class extends Component {
    // Component logic here
}; ?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        @livewire('pages.user.components.sidebar-button')
        <div class="bg-primary/20 p-3 rounded-lg hidden md:flex">

            <svg class="size-8 text-primary" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 8H5m12 0a1 1 0 0 1 1 1v2.6M17 8l-4-4M5 8a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.6M5 8l4-4 4 4m6 4h-4a2 2 0 1 0 0 4h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1Z" />
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-base-content">Wallets</h1>
        </div>
    </div>

    <div class="flex gap-2 items-center">
        <!-- Add Button -->
        <label
            class="btn btn-sm shadow-md bg-gradient-to-r from-primary-600 to-primary-600 hover:from-primary-700 hover:to-accent border-0"
            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Wallet', component: 'pages.user.accounts.add', modelId: null})">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-4 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>New</span>
        </label>
    </div>
</div>
