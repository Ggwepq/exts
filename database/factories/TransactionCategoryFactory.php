<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\CategoryGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionCategory>
 */
class TransactionCategoryFactory extends Factory
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
            'group_id' => CategoryGroup::factory(),
            'budget_id' => Budget::factory(),
            'type_id' => $this->faker->randomElement([1, 2]),
            'name' => $this->faker->word(),
        ];
    }
}
