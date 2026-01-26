<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePinVerified
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

        // Check if global PIN verification is required
        $loginRequirePin = setting('security', 'login_require_pin', true);

        // If global setting is disabled, proceed
        if (!$loginRequirePin) {
            return $next($request);
        }

        // Check if PIN is verified in this session
        if (session('pin_verified_at') && session('pin_verified_at') > now()->subMinutes(30)) {
            return $next($request);
        }

        // If not verified, redirect to PIN verification page
        return redirect()->route('verify-pin.show');
    }
}
