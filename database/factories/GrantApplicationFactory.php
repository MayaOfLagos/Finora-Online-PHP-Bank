<?php

namespace Database\Factories;

use App\Enums\GrantApplicationStatus;
use App\Models\GrantApplication;
use App\Models\GrantProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GrantApplication>
 */
class GrantApplicationFactory extends Factory
{
    protected $model = GrantApplication::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'grant_program_id' => GrantProgram::factory(),
            'reference_number' => GrantApplication::generateReferenceNumber(),
            'status' => GrantApplicationStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GrantApplicationStatus::Approved,
            'approved_at' => now(),
            'approved_by' => User::factory()->admin(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GrantApplicationStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
