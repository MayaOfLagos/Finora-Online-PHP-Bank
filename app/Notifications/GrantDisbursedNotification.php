<?php

namespace App\Notifications;

use App\Models\GrantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GrantDisbursedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public GrantApplication $application,
        public string $accountNumber
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->application->grantProgram->amount_in_dollars, 2);

        return (new MailMessage)
            ->subject('Grant Funds Disbursed - '.$this->application->grantProgram->name)
            ->greeting('Great News!')
            ->line('Your grant funds have been successfully disbursed to your account.')
            ->line('**Grant Program:** '.$this->application->grantProgram->name)
            ->line('**Reference Number:** '.$this->application->disbursement->reference_number)
            ->line('**Amount Credited:** $'.$amount)
            ->line('**Account Number:** '.$this->accountNumber)
            ->line('The funds are now available in your account.')
            ->action('View Transaction', url('/transactions'))
            ->line('Thank you for using Finora Bank!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Grant Funds Disbursed',
            'message' => 'Grant funds of $'.number_format($this->application->grantProgram->amount_in_dollars, 2).' have been credited to your account.',
            'application_id' => $this->application->id,
            'reference_number' => $this->application->disbursement->reference_number,
            'amount' => $this->application->grantProgram->amount,
            'account_number' => $this->accountNumber,
        ];
    }
}
