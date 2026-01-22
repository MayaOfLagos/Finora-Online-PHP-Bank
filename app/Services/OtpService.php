<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * OTP expiry time in minutes.
     */
    protected int $expiryMinutes = 10;

    /**
     * OTP length.
     */
    protected int $otpLength = 6;

    /**
     * Send OTP to user's email.
     */
    public function send(User $user, string $purpose = 'transaction'): array
    {
        // Invalidate any existing OTPs for this user and purpose
        Otp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate new OTP
        $code = $this->generateOtp();
        $expiresAt = now()->addMinutes($this->expiryMinutes);

        // Store OTP
        $otp = Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);

        // Send OTP via email
        $this->sendEmail($user, $code, $purpose);

        return [
            'success' => true,
            'message' => 'OTP sent to your email address.',
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in_seconds' => $this->expiryMinutes * 60,
        ];
    }

    /**
     * Verify OTP.
     */
    public function verify(User $user, string $code, string $purpose = 'transaction'): array
    {
        $otp = Otp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            return [
                'success' => false,
                'message' => 'No valid OTP found. Please request a new one.',
            ];
        }

        if ($otp->code !== $code) {
            // Track failed attempts
            $otp->increment('attempts');

            // Lock after 5 failed attempts
            if ($otp->attempts >= 5) {
                $otp->update(['is_used' => true]);

                return [
                    'success' => false,
                    'message' => 'Too many failed attempts. Please request a new OTP.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
                'attempts_remaining' => 5 - $otp->attempts,
            ];
        }

        // Mark OTP as used
        $otp->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
        ];
    }

    /**
     * Resend OTP.
     */
    public function resend(User $user, string $purpose = 'transaction'): array
    {
        // Check for rate limiting - allow only 1 OTP per minute
        $recentOtp = Otp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('created_at', '>', now()->subMinute())
            ->exists();

        if ($recentOtp) {
            return [
                'success' => false,
                'message' => 'Please wait before requesting a new OTP.',
            ];
        }

        return $this->send($user, $purpose);
    }

    /**
     * Generate random OTP code.
     */
    protected function generateOtp(): string
    {
        return str_pad((string) random_int(0, pow(10, $this->otpLength) - 1), $this->otpLength, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP via email.
     */
    protected function sendEmail(User $user, string $code, string $purpose): void
    {
        $purposeLabels = [
            'transaction' => 'Transaction Verification',
            'wire_transfer' => 'Wire Transfer Verification',
            'internal_transfer' => 'Internal Transfer Verification',
            'domestic_transfer' => 'Domestic Transfer Verification',
            'a2a_transfer' => 'Account Transfer Verification',
            'mobile_deposit' => 'Mobile Deposit Verification',
            'card_activation' => 'Card Activation',
            'pin_change' => 'PIN Change Verification',
            'password_reset' => 'Password Reset',
            'email_verification' => 'Email Verification',
            'two_factor' => 'Two-Factor Authentication',
        ];

        $purposeLabel = $purposeLabels[$purpose] ?? 'Verification';

        // In a real application, use a proper Mailable class
        // For now, we'll use a simple approach
        try {
            Mail::raw(
                "Your Finora Bank {$purposeLabel} Code is: {$code}\n\n".
                "This code will expire in {$this->expiryMinutes} minutes.\n\n".
                "If you did not request this code, please ignore this email and contact support immediately.\n\n".
                "Thank you,\nFinora Bank Security Team",
                function ($message) use ($user, $purposeLabel) {
                    $message->to($user->email)
                        ->subject("Finora Bank - {$purposeLabel} Code");
                }
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the OTP generation
            \Log::error('Failed to send OTP email: '.$e->getMessage());
        }
    }

    /**
     * Clean up expired OTPs.
     */
    public function cleanupExpired(): int
    {
        return Otp::where('expires_at', '<', now())
            ->orWhere('is_used', true)
            ->where('created_at', '<', now()->subDay())
            ->delete();
    }

    /**
     * Get remaining time for active OTP.
     */
    public function getRemainingTime(User $user, string $purpose = 'transaction'): ?array
    {
        $otp = Otp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            return null;
        }

        return [
            'expires_at' => $otp->expires_at->toIso8601String(),
            'expires_in_seconds' => now()->diffInSeconds($otp->expires_at),
        ];
    }
}
