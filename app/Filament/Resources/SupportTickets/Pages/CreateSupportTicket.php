<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Mail\PushNotificationMail;
use App\Mail\TicketCreatedMail;
use App\Services\ActivityLogger;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateSupportTicket extends CreateRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function afterCreate(): void
    {
        $ticket = $this->record;

        // Send email notification to user
        Mail::to($ticket->user->email)
            ->send(new TicketCreatedMail($ticket));

        // Send push notification
        Mail::to($ticket->user->email)
            ->send(new PushNotificationMail(
                $ticket->user,
                'Support Ticket Created',
                "Your support ticket #{$ticket->ticket_number} has been created successfully. Our team will review it shortly."

            ));

        // Create activity log
        ActivityLogger::logSupport(
            'ticket_created',
            $ticket,
            $ticket->user,
            [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'priority' => $ticket->priority->value,
            ]
        );
    }
}
