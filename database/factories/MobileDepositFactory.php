<?php

namespace Database\Factories;

use App\Enums\DepositStatus;
use App\Enums\PaymentGateway;
use App\Models\BankAccount;
use App\Models\MobileDeposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MobileDeposit>
 */
class MobileDepositFactory extends Factory
{
    protected $model = MobileDeposit::class;

    public function definition(): array
    {
        $amount = fake()->numberBetween(10000, 500000);

        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'bank_account_id' => BankAccount::factory(),
            'reference_number' => MobileDeposit::generateReferenceNumber(),
            'gateway' => fake()->randomElement(PaymentGateway::cases()),
            'gateway_transaction_id' => fake()->uuid(),
            'amount' => $amount,
            'currency' => 'USD',
            'fee' => (int) ($amount * 0.029 + 30),
            'status' => DepositStatus::Pending,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DepositStatus::Completed,
            'completed_at' => now(),
            'gateway_response' => ['status' => 'success', 'transaction_id' => fake()->uuid()],
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => PaymentGateway::Stripe,
        ]);
    }

    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => PaymentGateway::PayPal,
        ]);
    }
}
