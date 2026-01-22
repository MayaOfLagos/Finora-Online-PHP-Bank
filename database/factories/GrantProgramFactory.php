<?php

namespace Database\Factories;

use App\Enums\GrantStatus;
use App\Models\GrantProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GrantProgram>
 */
class GrantProgramFactory extends Factory
{
    protected $model = GrantProgram::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Small Business Grant',
                'Education Support Grant',
                'Emergency Relief Fund',
                'Home Improvement Grant',
                'Healthcare Assistance',
            ]),
            'description' => fake()->paragraphs(2, true),
            'amount' => fake()->randomElement([500000, 1000000, 2500000, 5000000]),
            'currency' => 'USD',
            'eligibility_criteria' => [
                'min_account_age_months' => fake()->numberBetween(3, 12),
                'min_balance' => fake()->numberBetween(10000, 100000),
                'kyc_level' => fake()->numberBetween(1, 3),
            ],
            'required_documents' => [
                'ID Document',
                'Proof of Address',
                'Bank Statement',
            ],
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'max_recipients' => fake()->numberBetween(50, 500),
            'status' => GrantStatus::Active,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GrantStatus::Closed,
            'end_date' => now()->subDay(),
        ]);
    }
}
