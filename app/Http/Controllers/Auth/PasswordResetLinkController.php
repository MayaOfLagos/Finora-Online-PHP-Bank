<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ReCaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        $recaptchaService = app(ReCaptchaService::class);

        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
            'recaptcha' => $recaptchaService->getConfig(forAdmin: false),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
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

            if (! $recaptchaResult['success']) {
                return back()->withErrors([
                    'recaptcha_token' => $recaptchaResult['message'] ?? 'Security verification failed. Please try again.',
                ]);
            }
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Log password reset request
        if ($status == Password::RESET_LINK_SENT) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                ActivityLogger::logAuth('password_reset_requested', $user, [
                    'ip_address' => $request->ip(),
                ]);
            }
        }

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
