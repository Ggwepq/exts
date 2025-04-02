<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <!-- Header Placeholder -->
    <div class="navbar sticky top-0 bg-base-100 z-40 shadow-md">
        <div class="flex-none">
            <div class="skeleton h-10 w-10 m-2 rounded-md"></div>
        </div>
        <div class="flex-1">
            <div class="flex flex-row w-full">
                <div class="flex-grow">
                    <div class="skeleton h-8 w-32 m-2"></div>
                </div>
                <div class="flex-none">
                    <div class="skeleton h-10 w-10 m-2 rounded-md"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8  shadow sm:rounded-lg">
            <div class="max-w-xl">
                <ul class="list p-4 bg-base-100 rounded-box shadow-md">
                    <li class="skeleton h-5 w-50"></li>
                    <li class="list-row flex items-center gap-4 justify-between">
                        <div class="flex flex-col gap-2">
                            <div class="skeleton h-6 w-30"></div>
                            <div class="skeleton h-7 w-40"></div>
                        </div>
                        <button class="btn btn-square btn-ghost skeleton"></button>
                    </li>
                    <li class="list-row flex items-center gap-4 justify-between">
                        <div class="flex flex-col gap-2">
                            <div class="skeleton h-6 w-30"></div>
                            <div class="skeleton h-7 w-40"></div>
                        </div>
                        <button class="btn btn-square btn-ghost skeleton"></button>
                    </li>
                    <li class="list-row flex items-center gap-4 justify-between">
                        <div class="flex flex-col gap-2">
                            <div class="skeleton h-6 w-30"></div>
                            <div class="skeleton h-7 w-40"></div>
                        </div>
                        <button class="btn btn-square btn-ghost skeleton"></button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
