<?php

namespace Database\Factories;

use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankAccount>
 */
class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'account_type_id' => AccountType::factory(),
            'account_number' => BankAccount::generateAccountNumber(),
            'balance' => fake()->numberBetween(10000, 10000000),
            'currency' => 'USD',
            'is_primary' => false,
            'is_active' => true,
            'opened_at' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'closed_at' => now(),
        ]);
    }

    public function withBalance(int $amountInCents): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $amountInCents,
        ]);
    }
}
