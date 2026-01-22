<?php

namespace Database\Factories;

use App\Models\CardType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardType>
 */
class CardTypeFactory extends Factory
{
    protected $model = CardType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Visa Classic', 'Visa Gold', 'Mastercard Platinum', 'Virtual Card']),
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{2}'),
            'is_virtual' => fake()->boolean(20),
            'is_credit' => fake()->boolean(30),
            'default_limit' => fake()->randomElement([100000000, 500000000, 1000000000]),
            'is_active' => true,
        ];
    }

    public function virtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_virtual' => true,
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_credit' => true,
        ]);
    }
}
