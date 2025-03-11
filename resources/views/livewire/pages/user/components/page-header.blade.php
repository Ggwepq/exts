<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="navbar bg-base-100 z-10 shadow-md">
    <div class="flex-1">
        <h1 class="text-2xl font-semibold ml-2">Transactions</h1>
    </div>
    <div class="">
        <label @click="isOpen = true; console.log(isOpen)" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </label>
    </div>
</div>
