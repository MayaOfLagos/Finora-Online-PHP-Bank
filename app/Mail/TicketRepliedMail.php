<?php

namespace App\Mail;

use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketRepliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public TicketMessage $ticketMessage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Reply on Ticket #' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-replied',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
