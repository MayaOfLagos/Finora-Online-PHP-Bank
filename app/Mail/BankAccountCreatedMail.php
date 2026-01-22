<?php

namespace App\Mail;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BankAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public BankAccount $account,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Bank Account Created - Finora Bank',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bank-account-created',
            with: [
                'user' => $this->user,
                'account' => $this->account,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
