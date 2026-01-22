<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log user activity
     * 
     * @param string $action
     * @param string $description
     * @param User|null $user
     * @param mixed $subject
     * @param array|null $metadata
     * @return ActivityLog
     */
    public static function log(
        string $action,
        string $description,
        ?User $user = null,
        $subject = null,
        ?array $metadata = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id ?? null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log authentication activities
     */
    public static function logAuth(string $type, ?User $user = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'login' => 'User logged in successfully',
            'logout' => 'User logged out',
            'failed_login' => 'Failed login attempt',
            'password_reset_requested' => 'Password reset requested',
            'password_reset_completed' => 'Password reset completed',
            'password_change' => 'Password changed',
            'email_verified' => 'Email address verified',
            'two_factor_enabled' => 'Two-factor authentication enabled',
            'two_factor_disabled' => 'Two-factor authentication disabled',
            'two_factor_failed' => 'Failed two-factor authentication attempt',
        ];

        return self::log(
            'auth.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $user,
            $user,
            $metadata
        );
    }

    /**
     * Log transaction activities
     */
    public static function logTransaction(string $type, $transaction, ?User $user = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'wire_transfer_created' => 'Initiated wire transfer',
            'wire_transfer_completed' => 'Wire transfer completed',
            'wire_transfer_failed' => 'Wire transfer failed',
            'internal_transfer_created' => 'Created internal transfer',
            'internal_transfer_completed' => 'Internal transfer completed',
            'domestic_transfer_created' => 'Initiated domestic transfer',
            'account_transfer_created' => 'Transfer between own accounts',
            'check_deposit_created' => 'Check deposit submitted',
            'mobile_deposit_created' => 'Mobile deposit submitted',
            'crypto_deposit_created' => 'Crypto deposit initiated',
        ];

        return self::log(
            'transaction.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $user,
            $transaction,
            $metadata
        );
    }

    /**
     * Log security activities
     */
    public static function logSecurity(string $type, ?User $user = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'invalid_pin' => 'Invalid transaction PIN attempted',
            'invalid_password' => 'Invalid password attempted',
            'otp_requested' => 'OTP code requested',
            'otp_verified' => 'OTP code verified successfully',
            'otp_failed' => 'Failed OTP verification',
            'account_locked' => 'Account locked due to suspicious activity',
            'account_unlocked' => 'Account unlocked by administrator',
            'session_expired' => 'User session expired',
            'forced_logout' => 'User force logged out by administrator',
        ];

        return self::log(
            'security.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $user,
            $user,
            $metadata
        );
    }

    /**
     * Log account activities
     */
    public static function logAccount(string $type, $subject = null, ?User $user = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'registered' => 'User account created',
            'profile_updated' => 'Profile information updated',
            'kyc_submitted' => 'KYC documents submitted',
            'kyc_approved' => 'KYC verification approved',
            'kyc_rejected' => 'KYC verification rejected',
            'bank_account_created' => 'New bank account created',
            'bank_account_closed' => 'Bank account closed',
            'beneficiary_added' => 'New beneficiary added',
            'beneficiary_removed' => 'Beneficiary removed',
        ];

        return self::log(
            'account.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $user,
            $subject,
            $metadata
        );
    }

    /**
     * Log loan activities
     */
    public static function logLoan(string $type, $loan, ?User $user = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'loan_applied' => 'Loan application submitted',
            'loan_approved' => 'Loan application approved',
            'loan_rejected' => 'Loan application rejected',
            'loan_disbursed' => 'Loan amount disbursed',
            'loan_repayment' => 'Loan repayment made',
            'loan_completed' => 'Loan fully repaid',
        ];

        return self::log(
            'loan.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $user,
            $loan,
            $metadata
        );
    }

    /**
     * Log card activities
     */
    public static function logCard(string $type, $card, ?User $user = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'card_requested' => 'Card requested',
            'card_approved' => 'Card request approved',
            'card_issued' => 'Card issued',
            'card_activated' => 'Card activated',
            'card_blocked' => 'Card blocked',
            'card_unblocked' => 'Card unblocked',
            'card_transaction' => 'Card transaction processed',
        ];

        return self::log(
            'card.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $user,
            $card,
            $metadata
        );
    }

    /**
     * Log grant activities
     */
    public static function logGrant(string $type, $grant, ?User $user = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'grant_applied' => 'Grant application submitted',
            'grant_approved' => 'Grant application approved',
            'grant_rejected' => 'Grant application rejected',
            'grant_disbursed' => 'Grant amount disbursed',
        ];

        return self::log(
            'grant.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $user,
            $grant,
            $metadata
        );
    }

    /**
     * Log support activities
     */
    public static function logSupport(string $type, $ticket = null, ?User $user = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'ticket_created' => 'Support ticket created',
            'ticket_replied' => 'Replied to support ticket',
            'ticket_status_changed' => 'Support ticket status changed',
            'ticket_closed' => 'Support ticket closed',
            'ticket_reopened' => 'Support ticket reopened',
        ];

        return self::log(
            'support.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $user,
            $ticket,
            $metadata
        );
    }

    /**
     * Log admin activities
     */
    public static function logAdmin(string $type, $subject = null, ?User $admin = null, ?array $metadata = null): ActivityLog
    {
        $descriptions = [
            'user_permission_changed' => 'User permissions modified by admin',
            'funds_added' => 'Funds added by admin',
            'funds_deducted' => 'Funds deducted by admin',
            'account_status_changed' => 'Account status changed by admin',
            'verification_code_generated' => 'Verification code generated by admin',
        ];

        return self::log(
            'admin.'.$type,
            $descriptions[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            $admin,
            $subject,
            $metadata
        );
    }
}
