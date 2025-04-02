<?php

use Livewire\Volt\Component;
use Livewire\Attribute\On;

new class extends Component {
    public string $component = '';

    public function mount(string $component)
    {
        $this->component = $component;
    }
}; ?>

<div class="navbar sticky top-0 bg-base-100 z-40 shadow-md border-b border-base-200">
    @livewire('pages.user.components.sidebar-button')
    <!-- Content -->
    <div class="flex-1">
        @if ($component)
            @livewire($component)
        @else
            <div class="h-full flex items-center justify-center">
                <span class="loading loading-spinner loading-md text-primary"></span>
            </div>
        @endif
    </div>
</div>
