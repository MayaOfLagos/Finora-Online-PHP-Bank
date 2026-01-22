<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement([
                'login',
                'logout',
                'transfer_initiated',
                'transfer_completed',
                'password_changed',
                'pin_changed',
                'profile_updated',
                'card_blocked',
                'loan_applied',
            ]),
            'description' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'metadata' => null,
        ];
    }

    public function withSubject(string $type, int $id): static
    {
        return $this->state(fn (array $attributes) => [
            'subject_type' => $type,
            'subject_id' => $id,
        ]);
    }
}
