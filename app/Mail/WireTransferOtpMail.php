<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WireTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WireTransferOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public WireTransfer $transfer,
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Wire Transfer Verification Code - Finora Bank',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.wire-transfer-otp',
            with: [
                'otp' => $this->otp,
                'transfer' => $this->transfer,
                'user' => $this->user,
                'expiresIn' => 10, // minutes
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
