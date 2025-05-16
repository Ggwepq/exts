<div x-data="{ open: false, showSearchBar: false }" class="flex flex-col gap-2">
    <!-- Main Header -->
    <div class="flex flex-col md:items-center justify-between gap-4 md:flex-row">
        <div class="flex items-center gap-3">

            @livewire('pages.user.components.sidebar-button')

            <div class="bg-primary/20 p-3 hidden md:flex">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-8 text-primary">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-base-content">Transactions</h1>
        </div>


        <div class="flex gap-3">
            <label
                class="btn btn-sm shadow-md bg-gradient-to-r from-primary-600 to-primary-600 hover:from-primary-700 hover:to-accent border-0"
                @click="open = !open">
                <svg :class="{ 'rotate-90': open }" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="transition-transform size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
                <span>Menu</span>
            </label>
            <!-- Add Button -->
            <label
                class="btn btn-sm shadow-md bg-gradient-to-r from-primary-600 to-primary-600 hover:from-primary-700 hover:to-accent border-0"
                @click="detailSidebarOpen = true; $dispatch('showSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.add', modelId: 12}), open = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6 mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <!-- <span :class="detailSidebarOpen ? 'hidden' : ''">New</span> -->
                <a>New</a>
            </label>

        </div>
    </div>

    <!-- Subheader / Tools Section -->
    <div x-show="open" x-transition class="flex flex-wrap items-center gap-2 pt-4 ">
        @livewire('pages.user.transactions.search')
        <div class="gap-2">
            @livewire('pages.user.transactions.sort')
            @livewire('pages.user.transactions.filter')
        </div>

    </div>
</div>
