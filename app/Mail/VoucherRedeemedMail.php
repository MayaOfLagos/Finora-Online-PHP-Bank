<?php

namespace App\Mail;

use App\Models\BankAccount;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VoucherRedeemedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Voucher $voucher,
        public BankAccount $bankAccount
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Voucher Redeemed Successfully - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.voucher-redeemed',
            with: [
                'userName' => $this->user->name,
                'voucherCode' => $this->voucher->code,
                'amount' => number_format($this->voucher->amount / 100, 2),
                'currency' => $this->voucher->currency ?? 'USD',
                'accountName' => $this->bankAccount->accountType?->name ?? 'Account',
                'accountNumber' => '****' . substr($this->bankAccount->account_number, -4),
                'redeemedAt' => now()->format('F j, Y \a\t g:i A'),
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
