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

<div class="navbar sticky z-40">
    <!-- Content -->
    <div class="flex-1">

        <div class="bg-gradient-to-r from-primary/10 to-primary/5 p-5 shadow-md">
            @if ($component)
                @livewire($component)
            @else
                <div class="h-full flex items-center justify-center">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                </div>
            @endif
        </div>
    </div>
</div>
