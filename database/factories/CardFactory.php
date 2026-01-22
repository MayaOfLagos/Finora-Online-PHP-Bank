<?php

namespace Database\Factories;

use App\Enums\CardStatus;
use App\Models\BankAccount;
use App\Models\Card;
use App\Models\CardType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        $issuedAt = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'bank_account_id' => BankAccount::factory(),
            'card_type_id' => CardType::factory(),
            'card_number' => fake()->creditCardNumber(),
            'card_holder_name' => fake()->name(),
            'expiry_month' => str_pad(fake()->numberBetween(1, 12), 2, '0', STR_PAD_LEFT),
            'expiry_year' => (string) fake()->numberBetween(date('Y') + 1, date('Y') + 5),
            'cvv' => fake()->numerify('###'),
            'pin' => '1234',
            'spending_limit' => fake()->randomElement([100000000, 500000000, 1000000000]),
            'daily_limit' => fake()->randomElement([50000000, 100000000, 200000000]),
            'status' => CardStatus::Active,
            'is_virtual' => false,
            'issued_at' => $issuedAt,
            'expires_at' => now()->addYears(3),
        ];
    }

    public function virtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_virtual' => true,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CardStatus::Blocked,
            'blocked_at' => now(),
        ]);
    }

    public function frozen(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CardStatus::Frozen,
        ]);
    }
}
