<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\BankAccount;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $principalAmount = fake()->numberBetween(1000000, 10000000);
        $termMonths = fake()->randomElement([12, 24, 36, 48, 60]);
        $interestRate = fake()->randomFloat(2, 8, 15);

        return [
            'uuid' => fake()->uuid(),
            'loan_application_id' => LoanApplication::factory()->approved(),
            'user_id' => User::factory(),
            'bank_account_id' => BankAccount::factory(),
            'principal_amount' => $principalAmount,
            'outstanding_balance' => $principalAmount,
            'interest_rate' => $interestRate,
            'monthly_payment' => (int) ($principalAmount / $termMonths * 1.1),
            'next_payment_date' => now()->addMonth(),
            'final_payment_date' => now()->addMonths($termMonths),
            'status' => LoanStatus::Active,
            'disbursed_at' => now(),
        ];
    }

    public function paidOff(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanStatus::Closed,
            'outstanding_balance' => 0,
            'closed_at' => now(),
        ]);
    }
}
