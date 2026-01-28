<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransferCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  object|Model  $transfer  Transfer data (Model or stdClass object)
     */
    public function __construct(
        public object $transfer,
        public User $user,
        public string $transferType = 'internal',
        public string $recipientName = '',
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'internal' => 'Internal Transfer Completed',
            'wire' => 'Wire Transfer Submitted',
            'domestic' => 'Domestic Transfer Submitted',
            'account' => 'Account Transfer Completed',
        ];

        $subject = ($subjects[$this->transferType] ?? 'Transfer Completed').' - '.app_name();

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.transfer-completed',
            with: [
                'transfer' => $this->transfer,
                'user' => $this->user,
                'transferType' => $this->transferType,
                'recipientName' => $this->recipientName,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
