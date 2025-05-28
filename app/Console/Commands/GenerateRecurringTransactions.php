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
            foreach ($recurring->transactions as $transaction) {
                Transaction::create([
                    'user_id' => $transaction->user_id,
                    'account_id' => $transaction->account_id,
                    'category_id' => $transaction->category_id,
                    'recurring_id' => $recurring->id,
                    'type_id' => $transaction->type_id,
                    'name' => $transaction->name,
                    'description' => $transaction->description,
                    'amount' => $transaction->amount,
                    'image_url' => $transaction->image_url,
                ]);

                // Calculate next due date based on frequency
                $nextDate = match ($recurring->frequency) {
                    'daily' => Carbon::parse($recurring->next_due_date)->addDay(),
                    'weekly' => Carbon::parse($recurring->next_due_date)->addWeek(),
                    'monthly' => Carbon::parse($recurring->next_due_date)->addMonth(),
                    default => null,
                };

                $recurring->update(['next_due_date' => $nextDate]);

            }
        }

        $this->info('Recurring transactions processed successfully.');
    }
}
