<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1;

        $bdo = Account::create([
            'user_id' => $userId,
            'category_id' => 1,
            'name' => 'BDO',
            'balance' => 0,
        ]);

        $gcash = Account::create([
            'user_id' => $userId,
            'category_id' => 2,
            'name' => 'Gcash',
            'balance' => 0,
        ]);

        $transactions = [
            [
                'user_id' => $userId,
                'account_id' => $bdo->id,
                'category_id' => null,
                'type_id' => 1, // Income
                'name' => 'Initial Account Balance',
                'amount' => 7500,
            ],
            [
                'user_id' => $userId,
                'account_id' => $gcash->id,
                'category_id' => null,
                'type_id' => 1, // Income
                'name' => 'Initial Account Balance',
                'amount' => 10000,
            ],
        ];

        foreach ($transactions as $transaction) {
            $newTransaction = Transaction::create([
                'user_id' => $transaction['user_id'],
                'account_id' => $transaction['account_id'],
                'category_id' => $transaction['category_id'],
                'type_id' => $transaction['type_id'],
                'name' => $transaction['name'],
                'amount' => $transaction['amount'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Update account balance based on type (Income/Expense)
            $account = Account::find($transaction['account_id']);
            if ($transaction['type_id'] == 1) {
                $account->balance += $transaction['amount'];
            } else {
                $account->balance -= $transaction['amount'];
            }
            $account->save();
        }
    }
}
