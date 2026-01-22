<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\DomesticTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DomesticTransfer>
 */
class DomesticTransferFactory extends Factory
{
    protected $model = DomesticTransfer::class;

    public function definition(): array
    {
        $amount = fake()->numberBetween(10000, 2000000);

        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'bank_account_id' => BankAccount::factory(),
            'bank_id' => Bank::factory(),
            'reference_number' => DomesticTransfer::generateReferenceNumber(),
            'beneficiary_name' => fake()->name(),
            'beneficiary_account' => fake()->bankAccountNumber(),
            'amount' => $amount,
            'currency' => 'USD',
            'fee' => (int) ($amount * 0.005),
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
