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

        // Check for session idle timeout (lockscreen)
        $enableLockscreen = setting('security', 'enable_lockscreen', true);
        $idleTimeout = (int) setting('security', 'session_idle_timeout', 15); // minutes
        $lastActivity = session('last_activity_at');

        // If lockscreen is enabled and we have last activity recorded and it's expired
        if ($enableLockscreen && $lastActivity && now()->diffInMinutes($lastActivity) > $idleTimeout) {
            // Only redirect to lockscreen if user has a PIN set
            if ($user->transaction_pin) {
                // Store when the session was locked
                session(['locked_at' => now()]);
                
                // Store intended URL
                if (!$request->routeIs('lockscreen.*')) {
                    session(['url.intended' => $request->fullUrl()]);
                }
                
                return redirect()->route('lockscreen.show');
            }
        }

        // Update last activity timestamp
        session(['last_activity_at' => now()]);

        // Check if global email OTP is required (only for initial login)
        $loginRequireEmailOtp = setting('security', 'login_require_email_otp', true);

        // If global setting is disabled, proceed
        if (!$loginRequireEmailOtp) {
            return $next($request);
        }

        // If user has skip_email_otp permission, proceed
        if ($user->skip_email_otp) {
            return $next($request);
        }

        // Check if email OTP is verified in this session (only required once per session)
        if (session('email_otp_verified_at')) {
            return $next($request);
        }

        // If not verified, redirect to email OTP verification page
        return redirect()->route('verify-email-otp.show');
    }
}
