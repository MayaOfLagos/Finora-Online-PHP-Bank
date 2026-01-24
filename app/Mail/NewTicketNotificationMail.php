<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewTicketNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public string $initialMessage
    ) {}

    public function envelope(): Envelope
    {
        $priority = $this->ticket->priority->label();
        $category = $this->ticket->category?->name ?? 'General';

        return new Envelope(
            subject: "[{$priority}] New Support Ticket #{$this->ticket->ticket_number} - {$category}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support.new-ticket-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
