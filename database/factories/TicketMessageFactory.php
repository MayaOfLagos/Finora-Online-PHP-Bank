<?php

namespace Database\Factories;

use App\Enums\TicketMessageType;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    protected $model = TicketMessage::class;

    public function definition(): array
    {
        return [
            'support_ticket_id' => SupportTicket::factory(),
            'user_id' => User::factory(),
            'message' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'type' => TicketMessageType::Reply,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TicketMessageType::Internal,
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TicketMessageType::System,
            'user_id' => null,
            'message' => fake()->randomElement([
                'Ticket has been assigned to support agent.',
                'Priority has been updated.',
                'Status changed to In Progress.',
            ]),
        ]);
    }
}
