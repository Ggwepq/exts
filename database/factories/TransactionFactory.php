<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'account_id' => Account::factory(),
            'category_id' => TransactionCategory::factory(),
            'recurring_id' => TransactionCategory::factory(),
            'type_id' => $this->faker->randomElement([1, 2]),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'amount' => $this->faker->randomFloat(2, 10, 10000),
            'image_url' => $this->faker->optional()->imageUrl(640, 480, 'business', true),
        ];
    }
}
