<?php

namespace Database\Factories;

use App\Enums\LoginStatus;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoginHistory>
 */
class LoginHistoryFactory extends Factory
{
    protected $model = LoginHistory::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'device_type' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'platform' => fake()->randomElement(['Windows', 'macOS', 'iOS', 'Android', 'Linux']),
            'location' => fake()->city().', '.fake()->country(),
            'status' => LoginStatus::Success,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoginStatus::Failed,
        ]);
    }

    public function suspicious(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LoginStatus::Suspicious,
        ]);
    }
}
