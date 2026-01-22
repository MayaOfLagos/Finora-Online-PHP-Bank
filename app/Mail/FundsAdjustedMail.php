<?php

namespace App\Mail;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FundsAdjustedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public BankAccount $account,
        public string $action,
        public float $amount,
        public string $reason,
        public float $previousBalance,
        public float $newBalance,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Balance Updated - Finora Bank',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.funds-adjusted',
            with: [
                'user' => $this->user,
                'account' => $this->account,
                'action' => $this->action,
                'amount' => $this->amount,
                'reason' => $this->reason,
                'previousBalance' => $this->previousBalance,
                'newBalance' => $this->newBalance,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
