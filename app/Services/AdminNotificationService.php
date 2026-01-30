<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    /**
     * Get all admin users (super_admin, admin, staff)
     */
    public static function getAdminUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereIn('role', [
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::Staff,
        ])->where('is_active', true)->get();
    }

    /**
     * Send a notification to all admins
     */
    public static function notifyAdmins(
        string $title,
        string $body,
        string $icon = 'heroicon-o-bell',
        string $color = 'primary',
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        try {
            $admins = self::getAdminUsers();

            foreach ($admins as $admin) {
                $notification = Notification::make()
                    ->title($title)
                    ->body($body)
                    ->icon($icon)
                    ->color($color);

                if ($actionUrl && $actionLabel) {
                    $notification->actions([
                        NotificationAction::make('view')
                            ->label($actionLabel)
                            ->url($actionUrl)
                            ->button(),
                    ]);
                }

                $notification->sendToDatabase($admin);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ==================== TRANSFER NOTIFICATIONS ====================

    /**
     * Notify admins about a new wire transfer
     */
    public static function wireTransferInitiated(Model $transfer, User $user): void
    {
        $amount = number_format($transfer->amount / 100, 2);
        $currency = $transfer->currency ?? 'USD';

        self::notifyAdmins(
            title: '🌐 New Wire Transfer',
            body: "{$user->name} initiated a wire transfer of {$currency} {$amount} to {$transfer->beneficiary_name}",
            icon: 'heroicon-o-globe-alt',
            color: 'info',
            actionUrl: route('filament.admin.resources.wire-transfers.index'),
            actionLabel: 'View Transfers',
        );
    }

    /**
     * Notify admins about a completed wire transfer
     */
    public static function wireTransferCompleted(Model $transfer, User $user): void
    {
        $amount = number_format($transfer->amount / 100, 2);
        $currency = $transfer->currency ?? 'USD';

        self::notifyAdmins(
            title: '✅ Wire Transfer Completed',
            body: "{$user->name}'s wire transfer of {$currency} {$amount} has been completed",
            icon: 'heroicon-o-check-circle',
            color: 'success',
            actionUrl: route('filament.admin.resources.wire-transfers.index'),
            actionLabel: 'View Transfers',
        );
    }

    /**
     * Notify admins about a new domestic transfer
     */
    public static function domesticTransferInitiated(Model $transfer, User $user): void
    {
        $amount = number_format($transfer->amount / 100, 2);
        $currency = $transfer->currency ?? 'USD';

        self::notifyAdmins(
            title: '🏦 New Domestic Transfer',
            body: "{$user->name} initiated a domestic transfer of {$currency} {$amount} to {$transfer->beneficiary_name}",
            icon: 'heroicon-o-building-library',
            color: 'info',
            actionUrl: route('filament.admin.resources.domestic-transfers.index'),
            actionLabel: 'View Transfers',
        );
    }

    /**
     * Notify admins about a completed domestic transfer
     */
    public static function domesticTransferCompleted(Model $transfer, User $user): void
    {
        $amount = number_format($transfer->amount / 100, 2);
        $currency = $transfer->currency ?? 'USD';

        self::notifyAdmins(
            title: '✅ Domestic Transfer Completed',
            body: "{$user->name}'s domestic transfer of {$currency} {$amount} has been completed",
            icon: 'heroicon-o-check-circle',
            color: 'success',
            actionUrl: route('filament.admin.resources.domestic-transfers.index'),
            actionLabel: 'View Transfers',
        );
    }

    /**
     * Notify admins about a completed internal transfer
     */
    public static function internalTransferCompleted(Model $transfer, User $user): void
    {
        $amount = number_format($transfer->amount / 100, 2);
        $currency = $transfer->currency ?? 'USD';

        self::notifyAdmins(
            title: '💸 Internal Transfer Completed',
            body: "{$user->name} completed an internal transfer of {$currency} {$amount}",
            icon: 'heroicon-o-arrows-right-left',
            color: 'success',
            actionUrl: route('filament.admin.resources.internal-transfers.index'),
            actionLabel: 'View Transfers',
        );
    }

    // ==================== DEPOSIT NOTIFICATIONS ====================

    /**
     * Notify admins about a new check deposit
     */
    public static function checkDepositSubmitted(Model $deposit, User $user): void
    {
        $amount = number_format($deposit->amount / 100, 2);
        $currency = $deposit->currency ?? 'USD';

        self::notifyAdmins(
            title: '📝 New Check Deposit',
            body: "{$user->name} submitted a check deposit of {$currency} {$amount} for approval",
            icon: 'heroicon-o-document-check',
            color: 'warning',
            actionUrl: route('filament.admin.resources.check-deposits.index'),
            actionLabel: 'Review Deposit',
        );
    }

    /**
     * Notify admins about a new crypto deposit
     */
    public static function cryptoDepositRegistered(Model $deposit, User $user): void
    {
        $amount = number_format($deposit->crypto_amount, 8);
        $symbol = $deposit->cryptocurrency?->symbol ?? 'CRYPTO';

        self::notifyAdmins(
            title: '₿ New Crypto Deposit',
            body: "{$user->name} registered a crypto deposit of {$amount} {$symbol} for verification",
            icon: 'heroicon-o-currency-dollar',
            color: 'warning',
            actionUrl: route('filament.admin.resources.crypto-deposits.index'),
            actionLabel: 'Verify Deposit',
        );
    }

    /**
     * Notify admins about a completed mobile deposit
     */
    public static function mobileDepositCompleted(Model $deposit, User $user): void
    {
        $amount = number_format($deposit->amount / 100, 2);
        $currency = $deposit->currency ?? 'USD';
        $gateway = ucfirst($deposit->gateway ?? 'Payment Gateway');

        self::notifyAdmins(
            title: '📱 Mobile Deposit Completed',
            body: "{$user->name} completed a {$gateway} deposit of {$currency} {$amount}",
            icon: 'heroicon-o-device-phone-mobile',
            color: 'success',
            actionUrl: route('filament.admin.resources.mobile-deposits.index'),
            actionLabel: 'View Deposits',
        );
    }

    /**
     * Notify admins about a manual deposit submission
     */
    public static function manualDepositSubmitted(Model $deposit, User $user): void
    {
        $amount = number_format($deposit->amount / 100, 2);
        $currency = $deposit->currency ?? 'USD';
        $gateway = ucfirst($deposit->gateway ?? 'Manual');

        self::notifyAdmins(
            title: '💳 Manual Deposit Submitted',
            body: "{$user->name} submitted a {$gateway} deposit of {$currency} {$amount} for approval",
            icon: 'heroicon-o-banknotes',
            color: 'warning',
            actionUrl: route('filament.admin.resources.mobile-deposits.index'),
            actionLabel: 'Review Deposit',
        );
    }

    // ==================== LOAN NOTIFICATIONS ====================

    /**
     * Notify admins about a new loan application
     */
    public static function loanApplicationSubmitted(Model $application, User $user): void
    {
        $amount = number_format($application->amount / 100, 2);
        $loanType = $application->loanType?->name ?? 'Loan';

        self::notifyAdmins(
            title: '📋 New Loan Application',
            body: "{$user->name} submitted a {$loanType} application for \${$amount}",
            icon: 'heroicon-o-document-text',
            color: 'warning',
            actionUrl: route('filament.admin.resources.loan-applications.index'),
            actionLabel: 'Review Application',
        );
    }

    // ==================== GRANT NOTIFICATIONS ====================

    /**
     * Notify admins about a new grant application
     */
    public static function grantApplicationSubmitted(Model $application, User $user): void
    {
        $programName = $application->grantProgram?->name ?? 'Grant Program';

        self::notifyAdmins(
            title: '🎁 New Grant Application',
            body: "{$user->name} applied for {$programName}",
            icon: 'heroicon-o-gift',
            color: 'warning',
            actionUrl: route('filament.admin.resources.grant-applications.index'),
            actionLabel: 'Review Application',
        );
    }

    // ==================== CARD NOTIFICATIONS ====================

    /**
     * Notify admins about a new card request
     */
    public static function cardRequested(Model $card, User $user): void
    {
        $cardType = $card->cardType?->name ?? ($card->is_virtual ? 'Virtual Card' : 'Physical Card');

        self::notifyAdmins(
            title: '💳 New Card Request',
            body: "{$user->name} requested a new {$cardType}",
            icon: 'heroicon-o-credit-card',
            color: 'info',
            actionUrl: route('filament.admin.resources.cards.index'),
            actionLabel: 'View Cards',
        );
    }

    /**
     * Notify admins about a card status change
     */
    public static function cardStatusChanged(Model $card, User $user, string $newStatus): void
    {
        self::notifyAdmins(
            title: '💳 Card Status Changed',
            body: "{$user->name} changed their card status to {$newStatus}",
            icon: 'heroicon-o-credit-card',
            color: $newStatus === 'frozen' ? 'danger' : 'success',
            actionUrl: route('filament.admin.resources.cards.index'),
            actionLabel: 'View Cards',
        );
    }

    // ==================== KYC NOTIFICATIONS ====================

    /**
     * Notify admins about a new KYC submission
     */
    public static function kycDocumentSubmitted(Model $verification, User $user): void
    {
        $documentType = $verification->template?->name ?? $verification->document_type_name ?? 'Document';

        self::notifyAdmins(
            title: '📄 New KYC Document',
            body: "{$user->name} submitted {$documentType} for verification",
            icon: 'heroicon-o-identification',
            color: 'warning',
            actionUrl: route('filament.admin.resources.users.index'),
            actionLabel: 'Review KYC',
        );
    }

    // ==================== SUPPORT NOTIFICATIONS ====================

    /**
     * Notify admins about a new support ticket
     */
    public static function supportTicketCreated(Model $ticket, User $user): void
    {
        $priority = $ticket->priority?->label() ?? 'Normal';

        self::notifyAdmins(
            title: '🎫 New Support Ticket',
            body: "{$user->name} created a {$priority} priority ticket: {$ticket->subject}",
            icon: 'heroicon-o-ticket',
            color: $ticket->priority?->value === 'urgent' ? 'danger' : 'warning',
            actionUrl: route('filament.admin.resources.support-tickets.index'),
            actionLabel: 'View Ticket',
        );
    }

    /**
     * Notify admins about a ticket reply
     */
    public static function supportTicketReplied(Model $ticket, User $user): void
    {
        self::notifyAdmins(
            title: '💬 Ticket Reply',
            body: "{$user->name} replied to ticket #{$ticket->ticket_number}",
            icon: 'heroicon-o-chat-bubble-left-right',
            color: 'info',
            actionUrl: route('filament.admin.resources.support-tickets.index'),
            actionLabel: 'View Ticket',
        );
    }

    // ==================== WITHDRAWAL NOTIFICATIONS ====================

    /**
     * Notify admins about a new withdrawal request
     */
    public static function withdrawalRequested(Model $withdrawal, User $user): void
    {
        $amount = number_format($withdrawal->amount / 100, 2);
        $currency = $withdrawal->currency ?? 'USD';

        self::notifyAdmins(
            title: '💰 New Withdrawal Request',
            body: "{$user->name} requested a withdrawal of {$currency} {$amount}",
            icon: 'heroicon-o-banknotes',
            color: 'warning',
            actionUrl: route('filament.admin.resources.withdrawals.index'),
            actionLabel: 'Review Withdrawal',
        );
    }

    // ==================== USER NOTIFICATIONS ====================

    /**
     * Notify admins about a new user registration
     */
    public static function userRegistered(User $user): void
    {
        self::notifyAdmins(
            title: '👤 New User Registration',
            body: "{$user->name} ({$user->email}) has registered",
            icon: 'heroicon-o-user-plus',
            color: 'success',
            actionUrl: route('filament.admin.resources.users.index'),
            actionLabel: 'View User',
        );
    }

    /**
     * Notify admins about a user login
     */
    public static function userLoggedIn(User $user): void
    {
        // Only notify for important users or suspicious activity
        // This is optional and can be enabled per needs
    }

    /**
     * Notify admins about profile update
     */
    public static function userProfileUpdated(User $user, array $changes = []): void
    {
        $changesText = ! empty($changes) ? ' ('.implode(', ', array_keys($changes)).')' : '';

        self::notifyAdmins(
            title: '✏️ Profile Updated',
            body: "{$user->name} updated their profile{$changesText}",
            icon: 'heroicon-o-user',
            color: 'info',
            actionUrl: route('filament.admin.resources.users.index'),
            actionLabel: 'View User',
        );
    }

    /**
     * Notify admins about PIN reset
     */
    public static function pinReset(User $user): void
    {
        self::notifyAdmins(
            title: '🔐 PIN Reset',
            body: "{$user->name} reset their transaction PIN",
            icon: 'heroicon-o-key',
            color: 'warning',
            actionUrl: route('filament.admin.resources.users.index'),
            actionLabel: 'View User',
        );
    }

    // ==================== TAX REFUND NOTIFICATIONS ====================

    /**
     * Notify admins about a new tax refund request
     */
    public static function taxRefundRequested(Model $refund, User $user): void
    {
        $amount = number_format($refund->amount / 100, 2);
        $currency = $refund->currency ?? 'USD';

        self::notifyAdmins(
            title: '📑 New Tax Refund Request',
            body: "{$user->name} requested a tax refund of {$currency} {$amount}",
            icon: 'heroicon-o-receipt-percent',
            color: 'warning',
            actionUrl: route('filament.admin.resources.tax-refunds.index'),
            actionLabel: 'Review Request',
        );
    }

    // ==================== EXCHANGE MONEY NOTIFICATIONS ====================

    /**
     * Notify admins about a currency exchange
     */
    public static function currencyExchangeCompleted(Model $exchange, User $user): void
    {
        $fromAmount = number_format($exchange->from_amount / 100, 2);
        $toAmount = number_format($exchange->to_amount / 100, 2);

        self::notifyAdmins(
            title: '💱 Currency Exchange',
            body: "{$user->name} exchanged {$exchange->from_currency} {$fromAmount} to {$exchange->to_currency} {$toAmount}",
            icon: 'heroicon-o-arrows-right-left',
            color: 'success',
            actionUrl: route('filament.admin.resources.exchange-money.index'),
            actionLabel: 'View Exchanges',
        );
    }

    // ==================== MONEY REQUEST NOTIFICATIONS ====================

    /**
     * Notify admins about a money request
     */
    public static function moneyRequestCreated(Model $request, User $user): void
    {
        $amount = number_format($request->amount / 100, 2);
        $currency = $request->currency ?? 'USD';

        self::notifyAdmins(
            title: '📩 New Money Request',
            body: "{$user->name} requested {$currency} {$amount} from another user",
            icon: 'heroicon-o-inbox-arrow-down',
            color: 'info',
            actionUrl: route('filament.admin.resources.money-requests.index'),
            actionLabel: 'View Requests',
        );
    }

    // ==================== VOUCHER NOTIFICATIONS ====================

    /**
     * Notify admins about a voucher redemption
     */
    public static function voucherRedeemed(Model $voucher, User $user): void
    {
        $amount = number_format($voucher->amount / 100, 2);
        $currency = $voucher->currency ?? 'USD';

        self::notifyAdmins(
            title: '🎟️ Voucher Redeemed',
            body: "{$user->name} redeemed a voucher worth {$currency} {$amount}",
            icon: 'heroicon-o-ticket',
            color: 'success',
            actionUrl: route('filament.admin.resources.vouchers.index'),
            actionLabel: 'View Vouchers',
        );
    }
}
