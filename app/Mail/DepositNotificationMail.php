<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DepositNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Model $deposit,
        public User $user,
        public string $depositType = 'mobile',
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->depositType) {
            'mobile' => 'Mobile Deposit Initiated',
            'check' => 'Check Deposit Submitted',
            'crypto' => 'Crypto Deposit Registered',
            default => 'Deposit Notification',
        };

        return new Envelope(
            subject: $subject.' - Finora Bank',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.deposit-notification',
            with: [
                'deposit' => $this->deposit,
                'user' => $this->user,
                'depositType' => $this->depositType,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
