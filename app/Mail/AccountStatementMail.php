<?php

namespace App\Mail;

use App\Models\BankAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AccountStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public BankAccount $account,
        public Collection $transactions,
        public string $pdfContent,
        public ?Carbon $dateFrom = null,
        public ?Carbon $dateTo = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account Statement - '.config('app.name', 'Finora Bank'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'NGN' => '₦',
        ];

        return new Content(
            view: 'emails.account-statement',
            with: [
                'user' => $this->user,
                'account' => $this->account,
                'transactionCount' => $this->transactions->count(),
                'dateFrom' => $this->dateFrom,
                'dateTo' => $this->dateTo,
                'generatedAt' => now(),
                'currencySymbol' => $currencySymbols[$this->account->currency] ?? '$',
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
        $filename = sprintf(
            'Statement_%s_%s.pdf',
            substr($this->account->account_number, -4),
            now()->format('Ymd')
        );

        return [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
