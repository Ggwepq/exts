<?php
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public $transactions;

    public function mount()
    {
        $this->loadTransactions();
    }

    #[On('transactionCreated')]
    public function loadTransactions()
    {
        /*
         * Fetch user tranasction based on date
         */
        $this->transactions = auth()
            ->user()
            ->transactions()
            ->orderBy('created_at', 'DESC')
            ->get()
            ->groupBy(function ($transaction) {
                $date = \Carbon\Carbon::parse($transaction->created_at);

                return $date->format('F j, Y');
            })
            ->all();
    }

    public function placeholder()
    {
        return view('livewire.pages.user.components.placeholder');
    }
}; ?>

<div class="flex-1">
    <div class="flex-1 transition-all duration-300" :class="isOpen ? 'md:mr-0' : ''">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8">
                <div class="max-w-xl">
                    @if (count($transactions))

                        <ul class="list bg-base-100 rounded-box">
                            @foreach ($transactions as $date => $transaction_records)
                                <div class="p-4">
                                    <div class="text-xs opacity-60 ">
                                        {{ $date }}
                                    </div>

                                    @foreach ($transaction_records as $transaction)
                                        <li class="list-row flex justify-between">
                                            <div>
                                                <div class="mb-1">{{ $transaction->name }}</div>
                                                <div class="text-xs uppercase font-semibold opacity-60"></div>
                                            </div>
                                            <div class="text-xs uppercase font-semibold opacity-60">
                                                @if ($transaction->types->name == 'Expense')
                                                    <div class="badge badge-outline badge-error">
                                                        {{ number_format($transaction->amount) }}
                                                    </div>
                                                @else
                                                    <div class="badge badge-outline badge-success">
                                                        {{ number_format($transaction->amount) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </div>
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
</div>
