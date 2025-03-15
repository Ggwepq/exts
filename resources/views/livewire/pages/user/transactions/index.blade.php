<?php

use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public function mount() {}
}; ?>

<div>
    <div class="flex min-h-screen">
        @livewire('pages.user.transactions.list', ['lazy' => true])
        @livewire('pages.user.containers.details-sidebar')
    </div>
</div>
