<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default test user
        $testUserId = DB::table('users')->insertGetId([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('testuser123'),
            'remember_token' => Str::random(10),
        ]);

        // Ensure default types exist
        DB::table('types')->insertOrIgnore([
            ['name' => 'Income'],
            ['name' => 'Expense'],
        ]);

        $types = DB::table('types')->pluck('id', 'name');

        // Create category groups for transaction categories
        $groups = \App\Models\CategoryGroup::factory(3)->create([
            'user_id' => $testUserId,
            'type' => 'Transaction',
        ]);

        // Create a budget
        $budget = \App\Models\Budget::factory()->create([
            'user_id' => $testUserId,
            'limit_amount' => 10000,
            'end_date' => Carbon::now()->addMonths(3)->endOfMonth()->format('Y-m-d'),
            'status' => 'Active',
        ]);

        // Create several transaction categories across different groups and types
        $transactionCategories = collect();

        foreach (['Income', 'Expense'] as $typeName) {
            foreach ($groups as $group) {
                $transactionCategories->push(
                    \App\Models\TransactionCategory::factory()->create([
                        'user_id' => $testUserId,
                        'group_id' => $group->id,
                        'budget_id' => $budget->id,
                        'type_id' => $types[$typeName],
                    ])
                );
            }
        }

        // Create an account for the test user
        $accounts = \App\Models\Account::factory(3)->create([
            'user_id' => $testUserId,
        ])->each(function ($account) {
            $account->update(['balance' => fake()->randomFloat(2, 1000, 50000)]);
        });

        // Create 30 random transactions with various types, dates, and categories
        for ($i = 0; $i < 30; $i++) {
            $randomTypeName = $types->keys()->random(); // 'Income' or 'Expense'
            $filteredCategories = $transactionCategories->filter(function ($cat) use ($types, $randomTypeName) {
                return $cat->type_id === $types[$randomTypeName];
            });

            \App\Models\Transaction::factory()->create([
                'user_id' => $testUserId,
                'account_id' => $accounts->random()->id,
                'category_id' => $filteredCategories->random()->id,
                'recurring_id' => $filteredCategories->random()->id,
                'type_id' => $types[$randomTypeName],
                'created_at' => $fakerDate = fake()->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $fakerDate,
            ]);
        }

        $this->call(SettingSeeder::class);
    }
}
