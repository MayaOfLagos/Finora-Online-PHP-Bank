<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PinVerificationController extends Controller
{
    /**
     * Show the PIN verification form.
     */
    public function show()
    {
        return Inertia::render('Auth/VerifyPin');
    }

    /**
     * Verify the transaction PIN.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'numeric', 'digits_between:4,6'],
        ]);

        $user = $request->user();

        // Rate limiting
        $key = 'verify-pin:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'pin' => "Too many verification attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // Check if user has a transaction PIN set
        if (!$user->transaction_pin) {
            throw ValidationException::withMessages([
                'pin' => 'Transaction PIN not set. Please contact support.',
            ]);
        }

        // Verify PIN
        if (!Hash::check($request->pin, $user->transaction_pin)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'pin' => 'Invalid PIN code.',
            ]);
        }

        // Mark as verified
        session(['pin_verified_at' => now()]);

        // Update user's last login
        $user->update([
            'pin_verified_at' => now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        RateLimiter::clear($key);

        return redirect()->intended(route('dashboard'));
    }
}
