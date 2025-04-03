@props(['imageUrl', 'name' => 'image-viewer', 'show' => false, 'maxWidth' => '4xl', 'withInstructions' => false])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        'full' => 'sm:max-w-full',
    ][$maxWidth];
@endphp

<div x-data="{
    show: @js($show),
    imageUrl: @js($imageUrl),
    scale: 1,
    minScale: 1,
    maxScale: 5,
    posX: 0,
    posY: 0,
    startX: 0,
    startY: 0,
    isDragging: false,

    zoom(amount) {
        this.scale = Math.max(this.minScale, Math.min(this.maxScale, this.scale + amount));
    },

    handleDrag(e) {
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        return { clientX, clientY };
    },

    startDrag(e) {
        if (this.scale > this.minScale) {
            const { clientX, clientY } = this.handleDrag(e);
            this.isDragging = true;
            this.startX = clientX - this.posX;
            this.startY = clientY - this.posY;
        }
    },

    drag(e) {
        if (this.isDragging) {
            const { clientX, clientY } = this.handleDrag(e);
            this.posX = clientX - this.startX;
            this.posY = clientY - this.startY;
        }
    },

    endDrag() {
        this.isDragging = false;
    },

    resetView() {
        this.scale = this.minScale;
        this.posX = 0;
        this.posY = 0;
    }
}" x-init="$watch('show', value => {
    if (value) {
        document.body.classList.add('overflow-y-hidden');
    } else {
        document.body.classList.remove('overflow-y-hidden');
        resetView();
    }
})" x-on:open-image-viewer.window="show = true; imageUrl = $event.detail"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null" x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false" x-show="show" class="fixed inset-0 overflow-hidden px-4 py-6 sm:px-0 z-100"
    :class="{ 'hidden': !show, 'block': show }" role="dialog" aria-modal="true">
    <!-- Backdrop overlay -->
    <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-900 opacity-90"></div>
    </div>

    <!-- Modal container -->
    <div x-show="show"
        class="mb-6 bg-transparent rounded-lg overflow-hidden transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="relative">
            <!-- Controls -->
            <div class="absolute top-2 right-2 flex space-x-2 z-20">
                <button class="btn btn-sm btn-circle btn-primary bg-opacity-50 hover:bg-opacity-70"
                    x-on:click="zoom(0.5)" aria-label="Zoom in">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
                <button class="btn btn-sm btn-circle btn-primary bg-opacity-50 hover:bg-opacity-70"
                    x-on:click="zoom(-0.5)" aria-label="Zoom out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>
                <button class="btn btn-sm btn-circle btn-primary bg-opacity-50 hover:bg-opacity-70"
                    x-on:click="resetView()" aria-label="Reset view">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <a class="btn btn-sm btn-circle btn-primary bg-opacity-50 hover:bg-opacity-70" :href="imageUrl"
                    download aria-label="Download image">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </a>
                <button class="btn btn-sm btn-circle btn-primary bg-opacity-50 hover:bg-opacity-70"
                    x-on:click="show = false" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Image Container -->
            <div class="flex items-center justify-center h-[80vh] w-full overflow-hidden cursor-move"
                x-on:mousedown="startDrag($event)" x-on:mousemove="drag($event)" x-on:mouseup="endDrag()"
                x-on:mouseleave="endDrag()" x-on:touchstart="startDrag($event)" x-on:touchmove="drag($event)"
                x-on:touchend="endDrag()">
                <img :src="imageUrl" class="max-h-full object-contain will-change-transform"
                    :style="`transform: translate(${posX}px, ${posY}px) scale(${scale})`"
                    x-on:dblclick="scale === minScale ? zoom(1) : resetView()" alt="Enlarged view" />
            </div>

            <!-- Usage instructions (optional) -->
            @if ($withInstructions)
                <div
                    class="absolute bottom-2 left-1/2 transform -translate-x-1/2 text-white text-xs bg-base-300 bg-opacity-50 px-3 py-1 rounded-full">
                    Double-click to zoom • Click and drag to pan • Use buttons to control
                </div>
            @endif
        </div>
    </div>
</div>
