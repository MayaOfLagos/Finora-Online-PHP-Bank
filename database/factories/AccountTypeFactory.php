<?php

namespace Database\Factories;

use App\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountType>
 */
class AccountTypeFactory extends Factory
{
    protected $model = AccountType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Savings', 'Checking', 'Business', 'Premium']),
            'code' => fake()->unique()->regexify('[A-Z]{3}'),
            'description' => fake()->sentence(),
            'minimum_balance' => fake()->randomElement([0, 10000, 50000, 100000]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
