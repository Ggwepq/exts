<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Default User ID=1
        DB::table('users')->insert([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'password' => bcrypt('testuser123'),
        ]);

        // Type
        DB::table('types')->insert([
            [
                'name' => 'Income'
            ],
            [
                'name' => 'Expense'
            ],
        ]);

        // Category Group Default
        DB::table('category_groups')->insert([
            [
                'group_name' => 'None',
                'type' => 'Both'
            ],
        ]);

        // Account Category Default
        DB::table('account_categories')->insert([
            [
                'group_id' => 1,
                'name' => 'None',
            ],
        ]);

        // Transaction Category Default
        DB::table('transaction_categories')->insert([
            // Income Category Default
            [
                'group_id' => 1,
                'type_id' => 1,
                'name' => 'None',
            ],
            // Expense Category Default
            [
                'group_id' => 1,
                'type_id' => 2,
                'name' => 'None',
            ],
        ]);

        // Account Default
        DB::table('accounts')->insert([
            [
                'user_id' => 1,
                'category_id' => 1,
                'name' => 'Test Account',
                'balance' => 1500,
            ],
        ]);
    }
}
