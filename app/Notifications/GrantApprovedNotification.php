<?php

namespace App\Notifications;

use App\Models\GrantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GrantApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public GrantApplication $application
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Grant Application Approved - '.$this->application->grantProgram->name)
            ->greeting('Congratulations!')
            ->line('Your grant application has been approved.')
            ->line('**Grant Program:** '.$this->application->grantProgram->name)
            ->line('**Reference Number:** '.$this->application->reference_number)
            ->line('**Grant Amount:** $'.number_format($this->application->grantProgram->amount_in_dollars, 2))
            ->line('The funds will be disbursed to your account shortly.')
            ->action('View Application', url('/grants/applications/'.$this->application->uuid))
            ->line('Thank you for using Finora Bank!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Grant Approved',
            'message' => 'Your grant application for '.$this->application->grantProgram->name.' has been approved.',
            'application_id' => $this->application->id,
            'reference_number' => $this->application->reference_number,
            'amount' => $this->application->grantProgram->amount,
        ];
    }
}
