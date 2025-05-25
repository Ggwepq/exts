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

    #[On('showRightSidebar')]
    public function open(string $operation, string $page, string $component = '', mixed $modelId = null): void
    {
        $this->oldComponent = $component;
        $this->operation = $operation;
        $this->page = $page;
        $this->component = $component;
        $this->modelId = $modelId;
    }
};

?>

<div>
    <div x-show="rightSidebarOpen" x-transition.opacity class="fixed inset-0 bg-black/30  z-60"
        @click="rightSidebarOpen = false" x-cloak>
    </div>
    <!-- Sidebar -->
    <aside x-show="rightSidebarOpen" x-transition:enter="transition-transform duration-300 ease-in-out"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform duration-300 ease-in-out" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 h-full w-full top-0 md:max-w-1/3 z-70 bg-gradient-to-b from-base-100 to-base-100/95 border-l border-base-200 shadow-lg ">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary/10 to-primary/5 p-4 border-b border-base-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/20 p-2">
                            @if ($operation == 'edit' || $operation == 'details')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5 text-primary">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            @elseif($operation == 'create')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5 text-primary">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5 text-primary">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                            @endif
                        </div>
                        <h2 class="text-xl font-bold text-base-content capitalize">
                            {{ ucfirst($operation) }} {{ $page }}
                        </h2>
                    </div>
                    <div>
                        @if ($operation == 'view')
                            <button
                                class="btn btn-ghost btn-sm bg-base-100 hover:bg-base-200 border border-base-300 shadow-sm"
                                @click="$dispatch('showRightSidebar', {operation: 'edit', page: '{{ $page }}', component: 'pages.user.{{ \Illuminate\Support\Str::plural(strtolower($page)) }}.edit', modelId: {{ $modelId }}}); rightSidebarOpen = true;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                <span class="hidden md:flex">Edit</span>
                            </button>
                        @elseif ($operation == 'edit')
                            <button
                                class="btn btn-ghost btn-sm bg-base-100 hover:bg-base-200 border border-base-300 shadow-sm"
                                @click="$dispatch('showRightSidebar', {operation: 'view', page: '{{ $page }}', component: 'pages.user.{{ \Illuminate\Support\Str::plural(strtolower($page)) }}.view', modelId: {{ $modelId }}}); rightSidebarOpen = true;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                                <span class="hidden md:flex">Cancel</span>
                            </button>
                        @endif
                        <button
                            class="btn btn-ghost btn-sm bg-base-100 hover:bg-base-200 border border-base-300 shadow-sm "
                            @click="detailSidebarOpen = false">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-5">
                @if ($component)
                    @livewire($component, $operation === 'create' ? [] : ['modelId' => $modelId], key($operation . '-' . $component . '-' . \Str::random(4)))
                @else
                    <div class="h-full flex flex-col items-center justify-center gap-4">
                        <span class="loading loading-spinner loading-lg text-primary"></span>
                        <p class="text-base-content/60">Loading content...</p>
                    </div>
                @endif
            </div>
        </div>
    </aside>
</div>
