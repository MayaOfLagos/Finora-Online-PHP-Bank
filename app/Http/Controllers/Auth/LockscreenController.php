<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LockscreenController extends Controller
{
    /**
     * Show the lockscreen page.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // If user doesn't have a PIN set, redirect to dashboard
        // (they'll need to set one up through security settings)
        if (!$user->transaction_pin) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Lockscreen', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'lockedAt' => session('locked_at') ? session('locked_at')->diffForHumans() : 'just now',
        ]);
    }

    /**
     * Unlock the session with PIN.
     */
    public function unlock(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'numeric', 'digits_between:4,6'],
        ]);

        $user = $request->user();

        // Rate limiting
        $key = 'lockscreen-unlock:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'pin' => "Too many attempts. Please try again in {$seconds} seconds.",
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

        // Clear rate limiter
        RateLimiter::clear($key);

        // Update last activity and unlock
        session([
            'last_activity_at' => now(),
            'locked_at' => null,
        ]);

        // Get the intended URL or default to dashboard
        $intendedUrl = session('url.intended', route('dashboard'));
        session()->forget('url.intended');

        return redirect($intendedUrl)->with('success', 'Session unlocked.');
    }

    /**
     * Logout from lockscreen.
     */
    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
