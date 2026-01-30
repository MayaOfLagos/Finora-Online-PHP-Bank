<?php

namespace App\Mail;

use App\Models\KycVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KycApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public KycVerification $kycVerification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'KYC Verification Approved - '.app_name(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.kyc.approved',
            with: [
                'user' => $this->kycVerification->user,
                'kyc' => $this->kycVerification,
                'documentType' => $this->kycVerification->document_type_name,
                'approvedAt' => $this->kycVerification->verified_at?->format('F j, Y \a\t g:i A'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
