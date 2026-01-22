<?php

namespace Database\Factories;

use App\Models\LoanType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanType>
 */
class LoanTypeFactory extends Factory
{
    protected $model = LoanType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Personal Loan', 'Business Loan', 'Mortgage', 'Auto Loan', 'Education Loan']),
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{2}'),
            'description' => fake()->paragraph(),
            'min_amount' => fake()->randomElement([100000, 500000, 1000000]),
            'max_amount' => fake()->randomElement([5000000, 10000000, 50000000]),
            'min_term_months' => fake()->randomElement([6, 12, 24]),
            'max_term_months' => fake()->randomElement([60, 120, 360]),
            'interest_rate' => fake()->randomFloat(2, 5, 20),
            'is_active' => true,
        ];
    }
}
