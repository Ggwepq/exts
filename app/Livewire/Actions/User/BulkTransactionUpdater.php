<?php

namespace App\Livewire\Actions\User;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Masmerise\Toaster\Toaster;

class BulkTransactionUpdater
{
    public function execute(array $transactionIds, array $updateData, ?array $tags = null): void
    {
        DB::transaction(function () use ($transactionIds, $updateData, $tags) {
            foreach ($transactionIds as $id) {
                $transaction = Transaction::find($id);
                if (! $transaction) {
                    continue;
                }

                $oldAccount = $transaction->accounts;
                $newAccount = isset($updateData['account_id']) ? Account::find($updateData['account_id']) : $oldAccount;

                $oldType = $transaction->type_id;
                $newType = isset($updateData['type_id']) ? (int) $updateData['type_id'] : $oldType;
                $amount = $transaction->amount;

                // Simulate new balance
                $simulated = $newAccount->balance;

                if ($oldAccount->id !== $newAccount->id) {
                    $simulated += $newType === 1 ? $amount : -$amount;
                } elseif ($oldType !== $newType) {
                    $simulated += ($oldType === 2 ? $amount : -$amount);
                    $simulated += ($newType === 1 ? $amount : -$amount);
                }

                if ($simulated < 0) {
                    Toaster::error("Insufficient Balance for {$newAccount->name}");

                    continue;
                }

                // Adjust balances
                if ($oldAccount->id !== $newAccount->id) {
                    if ($oldType === 1) {
                        $oldAccount->balance -= $amount;
                    } else {
                        $oldAccount->balance += $amount;
                    }

                    if ($newType === 1) {
                        $newAccount->balance += $amount;
                    } else {
                        $newAccount->balance -= $amount;
                    }

                    $oldAccount->save();
                    $newAccount->save();
                } elseif ($oldType !== $newType) {
                    if ($oldType === 1) {
                        $newAccount->balance -= $amount;
                    } else {
                        $newAccount->balance += $amount;
                    }

                    if ($newType === 1) {
                        $newAccount->balance += $amount;
                    } else {
                        $newAccount->balance -= $amount;
                    }

                    $newAccount->save();
                }

                // Save transaction
                $transaction->update($updateData);

                Toaster::success('Transaction Updated!');

                if (! empty($tags)) {
                    $transaction->tags()->sync($tags);
                }
            }
        });
    }
}
