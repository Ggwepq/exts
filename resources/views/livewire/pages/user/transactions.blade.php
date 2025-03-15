<?php

use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
}; ?>

<div x-data="{ isOpen: false }">
    @livewire('pages.user.components.page-header')
    <div class="flex min-h-screen">
        @livewire('pages.user.transactions.list-transaction', ['lazy' => true])
        @livewire('pages.user.components.right-sidebar')
    </div>
</div>
