<?php

namespace Database\Factories;

use App\Enums\RepaymentStatus;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanRepayment>
 */
class LoanRepaymentFactory extends Factory
{
    protected $model = LoanRepayment::class;

    public function definition(): array
    {
        $amount = fake()->numberBetween(50000, 500000);

        return [
            'loan_id' => Loan::factory(),
            'reference_number' => LoanRepayment::generateReferenceNumber(),
            'amount' => $amount,
            'principal_portion' => (int) ($amount * 0.7),
            'interest_portion' => (int) ($amount * 0.3),
            'penalty_amount' => 0,
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => RepaymentStatus::Pending,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RepaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RepaymentStatus::Overdue,
            'due_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'penalty_amount' => fake()->numberBetween(1000, 10000),
        ]);
    }
}
