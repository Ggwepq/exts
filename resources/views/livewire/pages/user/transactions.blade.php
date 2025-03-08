<?php

use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public $transactions;

    public function mount()
    {
        // Fetch user transactions
        $this->transactions = auth()
            ->user()
            ->transactions->groupBy(function ($transaction) {
                return \Carbon\Carbon::parse($transaction->created_at)->format('M d Y');
            })
            ->all();
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
                @if (count($transactions))
                    <ul class="list bg-base-100 rounded-box shadow-md">

                        @foreach ($transactions as $date => $transaction_records)
                            <li class="p-4 pb-2 text-xs opacity-60 tracking-wide">{{ $date }}</li>
                            @foreach ($transaction_records as $transaction)
                                <li class="list-row">
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
                        @endforeach

                    </ul>
                @else
                    <div class="flex flex-row justify-center">
                        😪 No transactions
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
