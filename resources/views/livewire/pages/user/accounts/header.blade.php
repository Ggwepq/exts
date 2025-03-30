<div class="flex">
    <div class="flex-1">
        <h1 class="text-2xl font-semibold ml-2">Accounts</h1>
    </div>
    <div class="flex-none">
        <label class="btn btn-primary"
            @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Accounts', component: 'pages.user.accounts.add', modelId: null})">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </label>
    </div>
</div>
