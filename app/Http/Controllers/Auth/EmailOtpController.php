<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailOtpMail;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class EmailOtpController extends Controller
{
    /**
     * Show the email OTP verification form.
     */
    public function show()
    {
        return Inertia::render('Auth/VerifyEmailOtp', [
            'email' => auth()->user()->email,
        ]);
    }

    /**
     * Send OTP to user's email.
     */
    public function send(Request $request)
    {
        $user = $request->user();

        // Rate limiting
        $key = 'send-email-otp:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'otp' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // Generate 6-digit OTP
        $otp = random_int(100000, 999999);

        // Store OTP in session
        session([
            'email_otp' => $otp,
            'email_otp_expires_at' => now()->addMinutes(setting('security', 'otp_expiry_minutes', 10)),
        ]);

        // Send OTP via email
        Mail::to($user->email)->send(new EmailOtpMail($otp, $user));

        // Log OTP request
        ActivityLogger::logSecurity('otp_requested', $user, [
            'type' => 'email',
            'ip_address' => $request->ip(),
        ]);

        RateLimiter::hit($key, 60);

        return back()->with('success', 'OTP has been sent to your email.');
    }

    /**
     * Verify the OTP code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'numeric', 'digits:6'],
        ]);

        $user = $request->user();

        // Rate limiting
        $key = 'verify-email-otp:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'otp' => "Too many verification attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $storedOtp = session('email_otp');
        $expiresAt = session('email_otp_expires_at');

        // Check if OTP exists
        if (! $storedOtp) {
            throw ValidationException::withMessages([
                'otp' => 'OTP not found. Please request a new one.',
            ]);
        }

        // Check if OTP expired
        if (now()->greaterThan($expiresAt)) {
            session()->forget(['email_otp', 'email_otp_expires_at']);
            throw ValidationException::withMessages([
                'otp' => 'OTP has expired. Please request a new one.',
            ]);
        }

        // Verify OTP
        if ($request->otp != $storedOtp) {
            RateLimiter::hit($key, 60);

            // Log failed OTP verification
            ActivityLogger::logSecurity('otp_failed', $user, [
                'type' => 'email',
                'ip_address' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP code.',
            ]);
        }

        // Clear OTP from session
        session()->forget(['email_otp', 'email_otp_expires_at']);

        // Mark as verified
        session(['email_otp_verified_at' => now()]);

        // Log successful OTP verification
        ActivityLogger::logSecurity('otp_verified', $user, [
            'type' => 'email',
            'ip_address' => $request->ip(),
        ]);

        // Update user's last login
        $user->update([
            'email_otp_verified_at' => now(),
        ]);

        RateLimiter::clear($key);

        // Check if PIN verification is required
        $loginRequirePin = setting('security', 'login_require_pin', true);

        if ($loginRequirePin && ! session('pin_verified_at')) {
            return redirect()->route('verify-pin.show');
        }

        return redirect()->intended(route('dashboard'));
    }
}
