<?php

namespace Database\Factories;

use App\Enums\DepositStatus;
use App\Models\BankAccount;
use App\Models\Cryptocurrency;
use App\Models\CryptoDeposit;
use App\Models\CryptoWallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CryptoDeposit>
 */
class CryptoDepositFactory extends Factory
{
    protected $model = CryptoDeposit::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'bank_account_id' => BankAccount::factory(),
            'cryptocurrency_id' => Cryptocurrency::factory(),
            'crypto_wallet_id' => CryptoWallet::factory(),
            'reference_number' => CryptoDeposit::generateReferenceNumber(),
            'crypto_amount' => fake()->randomFloat(8, 0.001, 10),
            'usd_amount' => fake()->numberBetween(50000, 5000000),
            'transaction_hash' => fake()->optional()->sha256(),
            'status' => DepositStatus::Pending,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DepositStatus::Completed,
            'transaction_hash' => fake()->sha256(),
            'verified_at' => now(),
            'verified_by' => User::factory()->admin(),
        ]);
    }
}
