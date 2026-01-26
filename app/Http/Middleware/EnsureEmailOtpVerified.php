<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if global email OTP is required
        $loginRequireEmailOtp = setting('security', 'login_require_email_otp', true);

        // If global setting is disabled, proceed
        if (!$loginRequireEmailOtp) {
            return $next($request);
        }

        // If user has skip_email_otp permission, proceed
        if ($user->skip_email_otp) {
            return $next($request);
        }

        // Check if email OTP is verified in this session
        if (session('email_otp_verified_at') && session('email_otp_verified_at') > now()->subMinutes(30)) {
            return $next($request);
        }

        // If not verified, redirect to email OTP verification page
        return redirect()->route('verify-email-otp.show');
    }
}
