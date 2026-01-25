<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransferOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public Model $transfer,
        public User $user,
        public string $transferType = 'domestic',
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->transferType) {
            'wire' => 'Wire Transfer Verification Code',
            'domestic' => 'Domestic Transfer Verification Code',
            'internal' => 'Internal Transfer Verification Code',
            default => 'Transfer Verification Code',
        };

        return new Envelope(
            subject: $subject.' - Finora Bank',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.transfer-otp',
            with: [
                'otp' => $this->otp,
                'transfer' => $this->transfer,
                'user' => $this->user,
                'transferType' => $this->transferType,
                'expiresIn' => 10, // minutes
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
