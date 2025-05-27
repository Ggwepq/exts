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
            'email_verified_at' => \Carbon\Carbon::now(),
            'password' => bcrypt('testuser123'),
        ]);

        // Type
        DB::table('types')->insert([
            [
                'name' => 'Income',
            ],
            [
                'name' => 'Expense',
            ],
        ]);

        // DB::table('category_groups')->insert([
        //     [
        //         'user_id' => 1,
        //         'name' => 'Essential',
        //         'type' => 'Transaction',
        //     ],
        //     [
        //         'user_id' => 1,
        //         'name' => 'Personal',
        //         'type' => 'Account',
        //     ],
        //     [
        //         'user_id' => 1,
        //         'name' => 'Job',
        //         'type' => 'Transaction',
        //     ],
        // ]);
        //
        // DB::table('account_categories')->insert([
        //     [
        //         'user_id' => 1,
        //         'group_id' => 2,
        //         'name' => 'Bank',
        //     ],
        //     [
        //         'user_id' => 1,
        //         'group_id' => 2,
        //         'name' => 'Digital',
        //     ],
        // ]);
        //
        // DB::table('transaction_categories')->insert([
        //     [
        //         'user_id' => 1,
        //         'group_id' => 1,
        //         'name' => 'Food',
        //         'type_id' => 2,
        //     ],
        //     [
        //         'user_id' => 1,
        //         'group_id' => 3,
        //         'name' => 'Salary',
        //         'type_id' => 1,
        //     ],
        //     [
        //         'user_id' => 1,
        //         'group_id' => 3,
        //         'name' => 'Freelance',
        //         'type_id' => 1,
        //     ],
        // ]);
        //
        // $this->call(TransactionAccountSeeder::class);
        $this->call(SettingSeeder::class);
    }
}
