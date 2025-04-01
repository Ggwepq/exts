<?php

use Livewire\Attributes\Reactive;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    public string $operation = 'details'; // create, edit, view
    public string $page = ''; // Account, Transaction
    public string $component = '';
    public mixed $modelId = null;

    #[On('showSidebar')]
    public function open(string $operation, string $page, string $component = '', ?int $modelId = null): void
    {
        $this->operation = $operation;
        $this->page = $page;
        $this->component = $component;
        $this->modelId = $modelId;
    }
};

?>

<div>
    <!-- Sidebar -->
    <aside x-show="detailSidebarOpen" x-transition:enter="transition-transform duration-300 ease-in-out"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform duration-300 ease-in-out" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 h-full w-full top-15 md:top-0 md:max-w-md z-30 bg-base-100 border-l border-base-200 shadow-lg"
        x-cloak>
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h2 class="text-xl font-semibold capitalize">
                    {{ ucfirst($operation) }} {{ $page }}
                </h2>
                <button class="btn btn-ghost btn-sm" @click="detailSidebarOpen = false">
                    ✕
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4">
                @if ($component)
                    @livewire($component, $operation === 'create' ? [] : ['modelId' => $modelId], key($operation . '-' . $component . '-' . $modelId . \Str::random(4)))
                @else
                    <div class="h-full flex items-center justify-center">
                        <span class="loading loading-dots loading-xl"></span>
                    </div>
                @endif
            </div>
        </div>
    </aside>
</div>
