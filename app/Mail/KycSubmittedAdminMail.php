<?php

namespace App\Mail;

use App\Models\KycVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KycSubmittedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public KycVerification $kycVerification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New KYC Verification Submitted - '.app_name(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.kyc.submitted-admin',
            with: [
                'user' => $this->kycVerification->user,
                'kyc' => $this->kycVerification,
                'documentType' => $this->kycVerification->document_type_name,
                'submittedAt' => $this->kycVerification->created_at->format('F j, Y \a\t g:i A'),
                'adminUrl' => config('app.url').'/admin/kyc',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
