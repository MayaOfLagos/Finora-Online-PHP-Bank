<?php

namespace Database\Factories;

use App\Enums\DepositStatus;
use App\Models\BankAccount;
use App\Models\CheckDeposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CheckDeposit>
 */
class CheckDepositFactory extends Factory
{
    protected $model = CheckDeposit::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'bank_account_id' => BankAccount::factory(),
            'reference_number' => CheckDeposit::generateReferenceNumber(),
            'check_number' => fake()->numerify('######'),
            'check_front_image' => 'checks/'.fake()->uuid().'_front.jpg',
            'check_back_image' => 'checks/'.fake()->uuid().'_back.jpg',
            'amount' => fake()->numberBetween(50000, 2000000),
            'currency' => 'USD',
            'status' => DepositStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DepositStatus::Completed,
            'approved_at' => now(),
            'approved_by' => User::factory()->admin(),
        ]);
    }

    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DepositStatus::OnHold,
            'hold_until' => now()->addDays(5),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DepositStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
