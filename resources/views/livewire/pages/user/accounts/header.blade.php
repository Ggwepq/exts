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
                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-base-content">Accounts</h1>
        </div>
    </div>

    <div class="flex gap-2 items-center">
        <!-- Add Button -->
        <label
            class="btn btn-sm shadow-md bg-gradient-to-r from-primary-600 to-primary-600 hover:from-primary-700 hover:to-accent border-0"
            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Accounts', component: 'pages.user.accounts.add', modelId: null})">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-4 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>New</span>
        </label>
    </div>
</div>
