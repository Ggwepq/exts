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
                    <div class="list bg-base-100 rounded-box shadow-md">

                        @foreach ($transactions as $date => $transaction_records)
                            <div class="p-4">
                                <div class="text-xs opacity-60 ">
                                    {{ $date }}
                                </div>

                                @foreach ($transaction_records as $transaction)
                                    <li class="list-row flex justify-between">
                                        <div>
                                            <div class="mb-1">{{ $transaction->name }}</div>
                                            <div class="text-xs uppercase font-semibold opacity-60">
                                                @if ($transaction->type == 'Expense')
                                                    <div class="badge badge-outline badge-error">
                                                        {{ $transaction->type }}
                                                    </div>
                                                @else
                                                    <div class="badge badge-outline badge-success">
                                                        {{ $transaction->type }}
                                                    </div>
                                                @endif
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
                            </div>
                        @endforeach

                    </div>
                @else
                    <div class="flex flex-row justify-center">
                        😪 No transactions
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
