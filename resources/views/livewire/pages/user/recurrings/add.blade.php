<?php
use Livewire\Attributes\Layout;
use App\Models\Transaction;
use App\Models\RecurringTransaction;
use App\Models\Type;
use Carbon\Carbon;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public $transactions;
    public $transaction_id;
    public $frequency;
    public $name;

    public function mount()
    {
        $this->transactions = $this->getTransactionsProperty();
    }

    public function getSelectedTransactionProperty()
    {
        $transaction = collect($this->transactions)->firstWhere('id', $this->transaction_id);
        $this->name = $transaction->name;
        return $transaction;
    }

    public function getTransactionsProperty()
    {
        return Transaction::where('user_id', Auth::id())->whereNull('recurring_id')->where('name', '!=', 'Initial Account Balance')->get();
    }

    public function save()
    {
        $this->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'frequency' => 'required|in:daily,weekly,monthly',
        ]);

        $transaction = Transaction::findOrFail($this->transaction_id);

        $today = Carbon::today('Asia/Manila');

        // Calculate next due date
        $nextDueDate = match ($this->frequency) {
            'daily' => $today->addDay(),
            'weekly' => $today->addWeek(),
            'monthly' => $today->addMonth(),
        };

        $recurring = RecurringTransaction::create([
            'user_id' => Auth::id(),
            'frequency' => $this->frequency,
            'next_due_date' => $nextDueDate,
            'status' => 'Active',
        ]);

        // Update transaction to link with recurring
        $transaction->update(['recurring_id' => $recurring->id]);

        Toaster::success('Recurring Created!');
        $this->transaction_id = null;
        $this->frequency = null;
        $this->dispatch('recurringUpdate');
    }
}; ?>

<section x-data="{ expense: true }">
    <!-- Form -->

    <div class="space-y-10">
        <!-- name -->
        <div class="flex flex-row w-full">
            <div class="flex items-center gap-2 mb-2 ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                </svg>
                <span class="text-xs font-semibold">
                    {{ carbon::now()->format('l, F j Y') }}
                </span>
            </div>
        </div>

        <div class="flex flex-col gap-3 mt-2">

            <div x-data="{
                name: @entangle('name')
            }" class="relative w-full max-w-full">

                <!-- display name (click to edit) -->
                <span class="cursor-pointer font-bold text-3xl block truncate text-center"
                    @click="$dispatch('showRightSidebar', {operation: 'view', page: 'Transaction', component: 'pages.user.transactions.view', modelId: {{ $transaction_id }}}); rightSidebarOpen = true;"
                    x-text="name || 'ㄟ( ▔, ▔ )ㄏ'">
                </span>
            </div>
            @error('name')
                <span class="validator-hint">{{ $message }}</span>
            @enderror
        </div>
        <div class="space-y-5">

            <div class="flex flex-row gap-4 ">
                <div class="dropdown dropdown-center w-full">
                    <label tabindex="0" class="btn btn-md border shadow-sm w-full" aria-label="Select Group">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        <span>{{ $transaction_id ? $this->selectedTransaction->name : 'Transaction' }}</span>
                    </label>
                    <div tabindex="0"
                        class="dropdown-content z-[1] menu mt-4 shadow-lg bg-base-100 rounded-xl w-3/4 border border-base-200">
                        <ul class="ml-2 my-1.5 flex flex-col overflow-auto max-h-40 space-y-1">
                            @foreach ($this->transactions as $group)
                                <li class=" text-6sm  ">
                                    <a wire:click="$set('transaction_id', {{ $group->id }})"
                                        class="flex items-center justify-between px-3 py-2 transition-all duration-200 group
        {{ $transaction_id == $group->id ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                                        <span class="flex space-x-1 items-center group-hover:text-primary">
                                            <span class="truncate ">{{ $group->name }}</span>
                                        </span>

                                        <span class="badge badge-xs badge-primary p-3">
                                            ₱{{ number_format($group->amount) }}
                                        </span>

                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a @click="$dispatch('showRightSidebar', {operation: 'create', page: 'Transaction', component: 'pages.user.transactions.add'}); rightSidebarOpen = true; console.log(rightSidebarOpen)"
                            class="flex items-center justify-center px-3 py-2 transition-all duration-200 group rounded-xl border-4"
                            :class="expense ? 'hover:bg-secondary border-secondary' : 'hover:bg-primary border-primary'">

                            <span class="flex space-x-1 items-center justify-center group-hover:text-primary"
                                :class="expense ? 'group-hover:text-secondary-content' :
                                    'group-hover:text-primary-content'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>New</span>
                            </span>
                        </a>
                    </div>
                    @error('account_id')
                        <span class="validator-hint">{{ $message }}</span>
                    @enderror
                </div>
            </div>


            <div class="dropdown dropdown-center w-full">
                <label tabindex="0" class="btn btn-md border shadow-sm w-full" aria-label="Select Group">
                    <span>{{ $frequency ? ucfirst($frequency) : 'Frequency' }}</span>
                </label>
                <div tabindex="0"
                    class="dropdown-content z-[1] menu mt-4 shadow-lg bg-base-100 rounded-xl w-60 border border-base-200">
                    <ul class="ml-2 my-1.5 flex flex-col overflow-auto max-h-40 space-y-1">
                        @foreach (['daily', 'weekly', 'monthly'] as $type)
                            <li class=" text-6sm  ">
                                <a wire:click="$set('frequency', '{{ $type }}')"
                                    class="flex items-center justify-between px-3 py-2 hover:bg-base-200 transition-all duration-200 group {{ $frequency == $type ? 'bg-gradient-to-r from-primary/100 to-primary/50 text-primary-content' : '' }}">

                                    <span class="flex space-x-1 items-center group-hover:text-primary">
                                        <span class="truncate ">{{ ucfirst($type) }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @error('account_id')
                    <span class="validator-hint">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <button type="submit" wire:click="save" class="btn btn-primary w-full"
            :class="expense ? 'bg-secondary' : 'bg-primary'">Save<span
                wire:loading.class="loading loading-bars loading-lg"></span></button>
    </div>

</section>
