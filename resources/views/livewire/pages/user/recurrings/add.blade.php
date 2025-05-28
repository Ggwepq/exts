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
    public $transaction_id;
    public $frequency;

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

        // Calculate next due date
        $nextDueDate = match ($this->frequency) {
            'daily' => Carbon::today()->addDay(),
            'weekly' => Carbon::today()->addWeek(),
            'monthly' => Carbon::today()->addMonth(),
        };

        $recurring = RecurringTransaction::create([
            'user_id' => Auth::id(),
            'frequency' => $this->frequency,
            'next_due_date' => $nextDueDate,
            'status' => 'Active',
        ]);

        // Update transaction to link with recurring
        $transaction->update(['recurring_id' => $recurring->id]);

        session()->flash('success', 'Recurring transaction created!');
        $this->reset();
    }
}; ?>

<div class="max-w-xl mx-auto p-4 space-y-4">
    @if (session()->has('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div>
        <label for="transaction" class="block font-medium">Select Transaction</label>
        <select wire:model="transaction_id" class="w-full border rounded p-2">
            <option value="">-- Select Transaction --</option>
            @foreach ($this->transactions as $tx)
                <option value="{{ $tx->id }}">{{ $tx->name }} - ₱{{ number_format($tx->amount, 2) }}</option>
            @endforeach
        </select>
        @error('transaction_id')
            <span class="text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="frequency" class="block font-medium">Frequency</label>
        <select wire:model="frequency" class="w-full border rounded p-2">
            <option value="">-- Select Frequency --</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </select>
        @error('frequency')
            <span class="text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <button wire:click="save" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Save Recurring Transaction
    </button>
</div>
</form>
