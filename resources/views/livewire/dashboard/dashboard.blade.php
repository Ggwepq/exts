<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>
<div class="drawer lg:drawer-open font-roboto">
    <input id="left-sidebar-drawer" type="checkbox" class="drawer-toggle" />

    <!-- Page Content -->
    <div class="drawer-content flex flex-col">
        <livewire:dashboard.containers.header />

        <main class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6 bg-base-200">
            @livewire('dashboard.pages.profile')
            <div class="h-16"></div>
        </main>
    </div>

    <!-- Left Sidebar -->
    <livewire:dashboard.containers.left-sidebar />
</div>
