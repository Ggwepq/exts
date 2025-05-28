<?php

namespace Database\Factories;

use App\Models\AccountCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => AccountCategory::factory(),
            'name' => $this->faker->word(),
            'balance' => $this->faker->randomFloat(2, 100, 100000),
        ];
    }
}
