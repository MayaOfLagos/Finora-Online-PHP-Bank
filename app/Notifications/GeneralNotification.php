<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    /**
     * Notification data.
     */
    protected array $data;

    /**
     * Whether to send email.
     */
    protected bool $sendMail;

    /**
     * Create a new notification instance.
     *
     * @param  array  $data  Notification data with keys:
     *                       - type: string (transfer, deposit, security, etc.)
     *                       - title: string
     *                       - message: string
     *                       - icon: string (optional, e.g., 'pi-send')
     *                       - color: string (optional, Tailwind classes)
     *                       - href: string (optional, link URL)
     *                       - action_text: string (optional, for email button)
     *                       - action_url: string (optional, for email button)
     * @param  bool  $sendMail  Whether to send email notification
     */
    public function __construct(array $data, bool $sendMail = false)
    {
        $this->data = $data;
        $this->sendMail = $sendMail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Check if email should be sent and user has email notifications enabled
        if ($this->sendMail && $this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Check if email should be sent based on user settings.
     */
    protected function shouldSendEmail(object $notifiable): bool
    {
        // Check global email notification setting
        if (method_exists($notifiable, 'notificationSettings')) {
            $type = $this->data['type'] ?? 'general';
            $setting = $notifiable->notificationSettings()
                ->where('type', $type)
                ->first();

            if ($setting && ! $setting->email) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->data['title'] ?? 'Notification from Finora Bank')
            ->greeting('Hello '.($notifiable->first_name ?? 'there').',')
            ->line($this->data['message'] ?? '');

        if (! empty($this->data['action_url']) && ! empty($this->data['action_text'])) {
            $mail->action($this->data['action_text'], $this->data['action_url']);
        } elseif (! empty($this->data['href'])) {
            $mail->action('View Details', url($this->data['href']));
        }

        $mail->line('Thank you for banking with Finora Bank.');

        return $mail;
    }

    /**
     * Get the array representation of the notification (for database storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->data['type'] ?? 'general',
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? '',
            'icon' => $this->data['icon'] ?? null,
            'color' => $this->data['color'] ?? null,
            'href' => $this->data['href'] ?? null,
            'data' => $this->data['extra'] ?? [],
        ];
    }

    /**
     * Static factory for transfer notifications.
     */
    public static function transferCompleted(string $amount, string $recipient, ?string $href = null): self
    {
        return new self([
            'type' => 'transfer',
            'title' => 'Transfer Completed',
            'message' => "Your transfer of {$amount} to {$recipient} has been completed successfully.",
            'icon' => 'pi-send',
            'color' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
            'href' => $href ?? '/transactions',
        ], true);
    }

    /**
     * Static factory for transfer received notifications.
     */
    public static function transferReceived(string $amount, string $sender, ?string $href = null): self
    {
        return new self([
            'type' => 'transfer_received',
            'title' => 'Transfer Received',
            'message' => "You received a transfer of {$amount} from {$sender}.",
            'icon' => 'pi-download',
            'color' => 'text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400',
            'href' => $href ?? '/transactions',
        ], true);
    }

    /**
     * Static factory for deposit notifications.
     */
    public static function depositReceived(string $amount, string $method, ?string $href = null): self
    {
        return new self([
            'type' => 'deposit',
            'title' => 'Deposit Received',
            'message' => "Your {$method} deposit of {$amount} has been received and is being processed.",
            'icon' => 'pi-download',
            'color' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
            'href' => $href ?? '/deposits',
        ], true);
    }

    /**
     * Static factory for deposit approved notifications.
     */
    public static function depositApproved(string $amount, ?string $href = null): self
    {
        return new self([
            'type' => 'deposit',
            'title' => 'Deposit Approved',
            'message' => "Your deposit of {$amount} has been approved and credited to your account.",
            'icon' => 'pi-check-circle',
            'color' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
            'href' => $href ?? '/deposits',
        ], true);
    }

    /**
     * Static factory for security alert notifications.
     */
    public static function securityAlert(string $title, string $message, ?string $href = null): self
    {
        return new self([
            'type' => 'security',
            'title' => $title,
            'message' => $message,
            'icon' => 'pi-shield',
            'color' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
            'href' => $href ?? '/settings/security',
        ], true);
    }

    /**
     * Static factory for login alert notifications.
     */
    public static function newLogin(string $device, string $location, ?string $href = null): self
    {
        return new self([
            'type' => 'security',
            'title' => 'New Login Detected',
            'message' => "A new login was detected from {$device} in {$location}.",
            'icon' => 'pi-shield',
            'color' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
            'href' => $href ?? '/settings/security',
        ], true);
    }

    /**
     * Static factory for loan status notifications.
     */
    public static function loanStatusUpdate(string $status, string $amount, ?string $message = null, ?string $href = null): self
    {
        $titles = [
            'approved' => 'Loan Approved',
            'rejected' => 'Loan Application Update',
            'disbursed' => 'Loan Disbursed',
            'payment_due' => 'Loan Payment Due',
        ];

        $colors = [
            'approved' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
            'rejected' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
            'disbursed' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
            'payment_due' => 'text-amber-600 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400',
        ];

        return new self([
            'type' => 'loan',
            'title' => $titles[$status] ?? 'Loan Update',
            'message' => $message ?? "Your loan application for {$amount} has been {$status}.",
            'icon' => 'pi-wallet',
            'color' => $colors[$status] ?? 'text-violet-600 bg-violet-100 dark:bg-violet-900/30 dark:text-violet-400',
            'href' => $href ?? '/loans',
        ], true);
    }

    /**
     * Static factory for card notifications.
     */
    public static function cardUpdate(string $status, string $cardType, ?string $message = null, ?string $href = null): self
    {
        $titles = [
            'issued' => 'Card Issued',
            'shipped' => 'Card Shipped',
            'activated' => 'Card Activated',
            'blocked' => 'Card Blocked',
        ];

        return new self([
            'type' => 'card',
            'title' => $titles[$status] ?? 'Card Update',
            'message' => $message ?? "Your {$cardType} card has been {$status}.",
            'icon' => 'pi-credit-card',
            'color' => 'text-purple-600 bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400',
            'href' => $href ?? '/cards',
        ], true);
    }

    /**
     * Static factory for support ticket notifications.
     */
    public static function supportTicketUpdate(string $ticketId, string $status, ?string $message = null, ?string $href = null): self
    {
        return new self([
            'type' => 'support',
            'title' => 'Support Ticket Update',
            'message' => $message ?? "Your support ticket #{$ticketId} has been updated to: {$status}.",
            'icon' => 'pi-comments',
            'color' => 'text-cyan-600 bg-cyan-100 dark:bg-cyan-900/30 dark:text-cyan-400',
            'href' => $href ?? "/support/tickets/{$ticketId}",
        ], true);
    }

    /**
     * Static factory for admin message notifications.
     */
    public static function adminMessage(string $title, string $message, ?string $href = null): self
    {
        return new self([
            'type' => 'admin',
            'title' => $title,
            'message' => $message,
            'icon' => 'pi-megaphone',
            'color' => 'text-indigo-600 bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400',
            'href' => $href,
        ], true);
    }

    /**
     * Static factory for KYC notifications.
     */
    public static function kycUpdate(string $status, ?string $message = null, ?string $href = null): self
    {
        $colors = [
            'approved' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
            'rejected' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
            'pending' => 'text-amber-600 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400',
        ];

        return new self([
            'type' => 'kyc',
            'title' => 'KYC Verification Update',
            'message' => $message ?? "Your KYC verification status has been updated to: {$status}.",
            'icon' => 'pi-id-card',
            'color' => $colors[$status] ?? 'text-amber-600 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400',
            'href' => $href ?? '/settings/profile',
        ], true);
    }
}
