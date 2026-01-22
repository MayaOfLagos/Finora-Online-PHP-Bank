<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PushNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $title,
        public string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.push-notification',
            with: [
                'user' => $this->user,
                'title' => $this->title,
                'message' => $this->message,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
