<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanApplication>
 */
class LoanApplicationFactory extends Factory
{
    protected $model = LoanApplication::class;

    public function definition(): array
    {
        $amount = fake()->numberBetween(1000000, 10000000);
        $termMonths = fake()->randomElement([12, 24, 36, 48, 60]);
        $interestRate = fake()->randomFloat(2, 8, 15);
        $monthlyPayment = $this->calculateMonthlyPayment($amount, $interestRate, $termMonths);
        $totalPayable = $monthlyPayment * $termMonths;

        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'loan_type_id' => LoanType::factory(),
            'reference_number' => LoanApplication::generateReferenceNumber(),
            'amount' => $amount,
            'term_months' => $termMonths,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'total_payable' => $totalPayable,
            'purpose' => fake()->sentence(),
            'status' => LoanStatus::Pending,
        ];
    }

    protected function calculateMonthlyPayment(int $principal, float $annualRate, int $months): int
    {
        $monthlyRate = ($annualRate / 100) / 12;
        if ($monthlyRate == 0) {
            return (int) ($principal / $months);
        }
        $payment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);

        return (int) $payment;
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanStatus::Approved,
            'approved_at' => now(),
            'approved_by' => User::factory()->admin(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoanStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
