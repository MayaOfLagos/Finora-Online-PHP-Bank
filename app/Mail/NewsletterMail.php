<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $template,
        public ?string $customMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'weekly' => 'Weekly Newsletter - Finora Bank',
            'monthly' => 'Monthly Newsletter - Finora Bank',
            'promo' => 'Special Promotion - Finora Bank',
        ];

        return new Envelope(
            subject: $subjects[$this->template] ?? 'Newsletter - Finora Bank',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter',
            with: [
                'user' => $this->user,
                'template' => $this->template,
                'customMessage' => $this->customMessage,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
