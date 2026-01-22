<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\AccountTransfer;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountTransfer>
 */
class AccountTransferFactory extends Factory
{
    protected $model = AccountTransfer::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'from_account_id' => BankAccount::factory(),
            'to_account_id' => BankAccount::factory(),
            'reference_number' => AccountTransfer::generateReferenceNumber(),
            'amount' => fake()->numberBetween(10000, 500000),
            'currency' => 'USD',
            'description' => fake()->optional()->sentence(),
            'status' => TransferStatus::Pending,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransferStatus::Completed,
            'pin_verified_at' => now()->subMinutes(2),
            'otp_verified_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);
    }
}
