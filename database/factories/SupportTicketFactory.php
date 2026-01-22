<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'category_id' => SupportCategory::factory(),
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'subject' => fake()->sentence(),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'status' => TicketStatus::Open,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Closed,
            'resolved_at' => now()->subHour(),
            'closed_at' => now(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TicketPriority::High,
        ]);
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::InProgress,
            'assigned_to' => User::factory()->admin(),
        ]);
    }
}
