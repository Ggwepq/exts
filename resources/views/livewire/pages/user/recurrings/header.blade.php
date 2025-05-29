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
                    d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" />
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-base-content">Recurring</h1>
        </div>
    </div>

    <div class="flex gap-2 items-center">
        <!-- Add Button -->
        <label
            class="btn btn-sm shadow-md bg-gradient-to-r from-primary-600 to-primary-600 hover:from-primary-700 hover:to-accent border-0"
            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Recurring', component: 'pages.user.recurrings.add', modelId: null})">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-4 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>New</span>
        </label>
    </div>
</div>
