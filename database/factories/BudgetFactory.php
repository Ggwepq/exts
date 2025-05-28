<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
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
            'limit_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'end_date' => Carbon::parse(
                $this->faker->dateTimeBetween('+1 month', '+6 months')
            )->endOfMonth()->format('Y-m-d'),
            'status' => $this->faker->randomElement(['Active']),
        ];
    }
}
