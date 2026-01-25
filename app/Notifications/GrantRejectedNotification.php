<?php

namespace App\Notifications;

use App\Models\GrantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GrantRejectedNotification extends Notification implements ShouldQueue
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
            ->subject('Grant Application Update - '.$this->application->grantProgram->name)
            ->greeting('Hello '.$notifiable->first_name.',')
            ->line('We regret to inform you that your grant application has been rejected.')
            ->line('**Grant Program:** '.$this->application->grantProgram->name)
            ->line('**Reference Number:** '.$this->application->reference_number)
            ->line('**Reason:** '.($this->application->rejection_reason ?? 'Not specified'))
            ->line('You are welcome to apply for other grant programs that match your eligibility.')
            ->action('Browse Grants', url('/grants'))
            ->line('Thank you for using Finora Bank.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Grant Application Rejected',
            'message' => 'Your grant application for '.$this->application->grantProgram->name.' has been rejected.',
            'application_id' => $this->application->id,
            'reference_number' => $this->application->reference_number,
            'rejection_reason' => $this->application->rejection_reason,
        ];
    }
}
