<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Enums\TransferStep;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\WireTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WireTransfer>
 */
class WireTransferFactory extends Factory
{
    protected $model = WireTransfer::class;

    public function definition(): array
    {
        $amount = fake()->numberBetween(100000, 5000000);
        $fee = (int) ($amount * 0.025);

        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'bank_account_id' => BankAccount::factory(),
            'reference_number' => WireTransfer::generateReferenceNumber(),
            'beneficiary_name' => fake()->name(),
            'beneficiary_account' => fake()->bankAccountNumber(),
            'beneficiary_bank_name' => fake()->company().' Bank',
            'beneficiary_bank_address' => fake()->address(),
            'swift_code' => fake()->regexify('[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}[A-Z0-9]{3}'),
            'routing_number' => fake()->numerify('#########'),
            'amount' => $amount,
            'currency' => 'USD',
            'exchange_rate' => 1.000000,
            'fee' => $fee,
            'total_amount' => $amount + $fee,
            'purpose' => fake()->randomElement(['Business Payment', 'Family Support', 'Investment', 'Education']),
            'status' => TransferStatus::Pending,
            'current_step' => TransferStep::Pin,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransferStatus::Completed,
            'current_step' => TransferStep::Completed,
            'pin_verified_at' => now()->subMinutes(10),
            'imf_verified_at' => now()->subMinutes(8),
            'tax_verified_at' => now()->subMinutes(6),
            'cot_verified_at' => now()->subMinutes(4),
            'otp_verified_at' => now()->subMinutes(2),
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransferStatus::Failed,
            'failed_reason' => fake()->sentence(),
        ]);
    }

    public function pendingImf(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransferStatus::Processing,
            'current_step' => TransferStep::Imf,
            'pin_verified_at' => now(),
        ]);
    }
}
