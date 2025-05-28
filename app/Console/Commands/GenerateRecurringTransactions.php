<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    protected $signature = 'transactions:generate-recurring';

    protected $description = 'Generate transactions for due recurring entries';

    public function handle()
    {
        $today = Carbon::today('Asia/Manila');

        $recurrings = RecurringTransaction::where('status', 'Active')
            ->whereDate('next_due_date', '<=', $today)
            ->get();

        foreach ($recurrings as $recurring) {
            $transaction = [
                'user_id' => $recurring->user_id,
                'account_id' => $recurring->transactions->account_id,
                'category_id' => $recurring->transactions->category_id,
                'recurring_id' => $recurring->id,
                'type_id' => $recurring->transactions->type_id,
                'name' => $recurring->transactions->name,
                'description' => $recurring->transactions->description,
                'amount' => $recurring->transactions->amount,
                'image_url' => $recurring->transactions->image_url,
            ];
            Transaction::create($transaction);

            // Calculate next due date based on frequency
            $nextDate = match ($recurring->frequency) {
                'daily' => Carbon::parse($recurring->next_due_date)->addDay(),
                'weekly' => Carbon::parse($recurring->next_due_date)->addWeek(),
                'monthly' => Carbon::parse($recurring->next_due_date)->addMonth(),
                default => null,
            };

            $recurring->update(['next_due_date' => $nextDate]);
        }

        $this->info('Recurring transactions processed successfully.');
    }
}
