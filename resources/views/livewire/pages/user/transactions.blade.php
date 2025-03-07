<?php

use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public $transactions;
    public function mount()
    {
        // Fetch user transactions
        $this->transactions = auth()->user()->transactions;
    }

    public function placeholder()
    {
        return view('livewire.pages.user.components.placeholder');
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8  shadow sm:rounded-lg">
            <div class="max-w-xl">
                @if ($transactions)
                    <ul class="list bg-base-100 rounded-box shadow-md">

                        <li class="p-4 pb-2 text-xs opacity-60 tracking-wide">{{ today()->format('M-d-Y') }}</li>

                        @foreach ($transactions as $transaction)
                            <li class="list-row">
                                <div>
                                    <!-- <img class="size-10 rounded-box" -->
                                    <!--     src="https://img.daisyui.com/images/profile/demo/1@94.webp" /> -->
                                </div>
                                <div>
                                    <div>{{ $transaction->name }}</div>
                                    <div class="text-xs uppercase font-semibold opacity-60">{{ $transaction->type }}
                                    </div>
                                </div>
                                <button class="btn btn-square btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                    </svg>
                                </button>
                            </li>
                        @endforeach

                    </ul>
                @else
                    <div class="flex justify-items-center">
                        No transactions
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
