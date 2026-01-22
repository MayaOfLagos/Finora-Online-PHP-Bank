<?php

namespace Database\Factories;

use App\Enums\CardRequestStatus;
use App\Models\CardRequest;
use App\Models\CardType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardRequest>
 */
class CardRequestFactory extends Factory
{
    protected $model = CardRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'card_type_id' => CardType::factory(),
            'reference_number' => CardRequest::generateReferenceNumber(),
            'shipping_address' => fake()->address(),
            'status' => CardRequestStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CardRequestStatus::Approved,
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CardRequestStatus::Shipped,
            'tracking_number' => fake()->regexify('[A-Z0-9]{12}'),
            'shipped_at' => now(),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CardRequestStatus::Delivered,
            'tracking_number' => fake()->regexify('[A-Z0-9]{12}'),
            'shipped_at' => now()->subDays(5),
            'delivered_at' => now(),
        ]);
    }
}
