<?php

namespace Database\Factories;

use App\Enums\CardTransactionType;
use App\Models\Card;
use App\Models\CardTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardTransaction>
 */
class CardTransactionFactory extends Factory
{
    protected $model = CardTransaction::class;

    public function definition(): array
    {
        return [
            'card_id' => Card::factory(),
            'reference_number' => CardTransaction::generateReferenceNumber(),
            'merchant_name' => fake()->company(),
            'merchant_category' => fake()->randomElement(['Retail', 'Restaurant', 'Gas Station', 'Online', 'Travel']),
            'amount' => fake()->numberBetween(1000, 50000),
            'currency' => 'USD',
            'type' => fake()->randomElement(CardTransactionType::cases()),
            'status' => 'completed',
            'transaction_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CardTransactionType::Purchase,
        ]);
    }

    public function withdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CardTransactionType::Withdrawal,
            'merchant_name' => 'ATM Withdrawal',
            'merchant_category' => 'ATM',
        ]);
    }
}
