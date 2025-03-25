<?php
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public $transactions;

    public function mount()
    {
        $this->loadTransactions();
    }

    /**
     * Fetch user tranasction based on date
     */
    #[On('transactionCreated')]
    public function loadTransactions()
    {
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
        return view('livewire.pages.user.components.placeholders.placeholder');
    }
}; ?>

<div>
    <!-- Main Content with Animated Margin -->
    <div class="transition-all duration-300 ease-in-out" :class="{ 'md:mr-[28rem]': detailSidebarOpen }">
        @livewire('pages.user.containers.main-header', ['component' => 'pages.user.transactions.header'])

        <div class="flex-1 overflow-y-auto md:pt-4 pt-4 px-6  bg-base-200">
            <div class="card w-full p-6 bg-base-100 shadow-xl mt-2">
                @if (count($transactions))
                    <ul class="list bg-base-100 rounded-box shadow-md">

                        @foreach ($transactions as $date => $record)
                            <li class="p-4 pb-2 text-xs opacity-60 tracking-wide">{{ $date }}</li>

                            @foreach ($record as $transaction)
                                <li class="list-row hover:bg-base-200"
                                    @click="$dispatch('showSidebar', {operation: 'view', page: 'Transaction', component: 'pages.user.transactions.edit', modelId: {{ $transaction->id }}}); detailSidebarOpen = true;">
                                    <div><img class="size-10 rounded-box"
                                            src="{{ $transaction->image_url ? asset('app/' . $transaction->image_url) : asset('img/default-img.png') }}" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-md mb-2">{{ $transaction->name }}</div>
                                        <div
                                            class="text-[0.70rem] uppercase font-semibold badge badge-outline {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                            {{ $transaction->types->name }}</div>
                                    </div>

                                    <div>
                                        <div
                                            class="text-sm uppercase font-semibold badge {{ $transaction->types->name == 'Expense' ? 'badge-secondary' : 'badge-primary' }}">
                                            {{ $transaction->types->name == 'Expense' ? '-₱' : '+₱' }}{{ $transaction->amount }}
                                        </div>
                                    </div>
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
    @livewire('pages.user.containers.details-sidebar', ['lazy' => true])
</div>
