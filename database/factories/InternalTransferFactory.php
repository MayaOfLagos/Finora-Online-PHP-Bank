<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\BankAccount;
use App\Models\InternalTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InternalTransfer>
 */
class InternalTransferFactory extends Factory
{
    protected $model = InternalTransfer::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'sender_id' => User::factory(),
            'sender_account_id' => BankAccount::factory(),
            'receiver_id' => User::factory(),
            'receiver_account_id' => BankAccount::factory(),
            'reference_number' => InternalTransfer::generateReferenceNumber(),
            'amount' => fake()->numberBetween(10000, 1000000),
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
