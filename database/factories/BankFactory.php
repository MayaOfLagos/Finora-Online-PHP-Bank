<?php

namespace Database\Factories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bank>
 */
class BankFactory extends Factory
{
    protected $model = Bank::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Bank',
            'code' => fake()->unique()->regexify('[A-Z]{4}'),
            'routing_number' => fake()->numerify('#########'),
            'swift_code' => fake()->regexify('[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}[A-Z0-9]{3}'),
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
