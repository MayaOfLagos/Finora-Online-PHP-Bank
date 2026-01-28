<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if KYC is required globally
        $kycRequired = (bool) Setting::getValue('security', 'kyc_required', true);

        if (! $kycRequired) {
            return $next($request);
        }

        $user = $request->user();

        // If user is verified, allow access
        if ($user && $user->is_verified) {
            return $next($request);
        }

        // Check if user has approved KYC
        if ($user) {
            $hasApprovedKyc = $user->kycVerifications()
                ->where('status', 'approved')
                ->exists();

            if ($hasApprovedKyc) {
                return $next($request);
            }
        }

        // Redirect to KYC page with message
        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return Inertia::location(route('kyc.index', ['kyc_required' => true]));
        }

        return redirect()->route('kyc.index')
            ->with('error', 'Please complete KYC verification to access this feature.');
    }
}
