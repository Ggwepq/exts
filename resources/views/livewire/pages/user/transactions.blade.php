<?php

use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public $transactions;

    public function mount()
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

<div x-data="{ isOpen: false }">
    @livewire('pages.user.components.page-header')
    <div class="flex min-h-screen">
        @livewire('pages.user.transactions.list-transaction', ['lazy' => true])
        @livewire('pages.user.components.right-sidebar')
    </div>
</div>
