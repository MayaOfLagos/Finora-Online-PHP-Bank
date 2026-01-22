<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $subject,
        public string $message,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-notification',
            with: [
                'user' => $this->user,
                'message' => $this->message,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
