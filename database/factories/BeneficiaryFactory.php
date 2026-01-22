<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Beneficiary>
 */
class BeneficiaryFactory extends Factory
{
    protected $model = Beneficiary::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'beneficiary_user_id' => User::factory(),
            'beneficiary_account_id' => BankAccount::factory(),
            'nickname' => fake()->optional()->firstName(),
            'is_verified' => true,
            'is_favorite' => fake()->boolean(20),
            'transfer_limit' => fake()->optional()->randomElement([100000000, 500000000, 1000000000]),
            'last_used_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function favorite(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_favorite' => true,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }
}
