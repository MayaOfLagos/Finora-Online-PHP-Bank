<?php

namespace App\Mail;

use App\Models\KycVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KycRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public KycVerification $kycVerification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'KYC Verification Update - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.kyc.rejected',
            with: [
                'user' => $this->kycVerification->user,
                'kyc' => $this->kycVerification,
                'documentType' => $this->kycVerification->document_type_name,
                'rejectionReason' => $this->kycVerification->rejection_reason,
                'rejectedAt' => $this->kycVerification->verified_at?->format('F j, Y \a\t g:i A'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
