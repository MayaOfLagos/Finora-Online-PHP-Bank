<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Services\ReCaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        $recaptchaService = app(ReCaptchaService::class);
        
        return Inertia::render('Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
            'recaptcha' => $recaptchaService->getConfig(forAdmin: false),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Verify reCAPTCHA first
        $recaptchaService = app(ReCaptchaService::class);
        
        if ($recaptchaService->isEnforcedForUser()) {
            $recaptchaResult = $recaptchaService->verify(
                $request->input('recaptcha_token'),
                $request->ip()
            );
            
            if (!$recaptchaResult['success']) {
                return back()->withErrors([
                    'recaptcha_token' => $recaptchaResult['message'] ?? 'Security verification failed. Please try again.',
                ]);
            }
        }
        
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Log successful login
            $user = Auth::user();

            if ($user && ! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact support.',
                ]);
            }

            // Record login history
            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
                'status' => 'success',
            ]);

            // Check if Email OTP verification is required
            $loginRequireEmailOtp = setting('security', 'login_require_email_otp', true);
            
            if ($loginRequireEmailOtp && !$user->skip_email_otp) {
                // Clear previous verification sessions
                session()->forget(['email_otp_verified_at', 'pin_verified_at']);
                
                return redirect()->route('verify-email-otp.show');
            }

            // Check if PIN verification is required
            $loginRequirePin = setting('security', 'login_require_pin', true);
            
            if ($loginRequirePin) {
                // Clear previous PIN verification
                session()->forget('pin_verified_at');
                
                return redirect()->route('verify-pin.show');
            }

            // If no verification required, update last login and proceed
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('logout', true);
    }
}
