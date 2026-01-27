<?php

namespace App\Filament\Auth;

use App\Services\ReCaptchaService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Locked;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    #[Locked]
    public ?string $recaptchaToken = null;

    public function authenticate(): ?LoginResponse
    {
        // Verify reCAPTCHA if enabled for admin
        $recaptchaService = app(ReCaptchaService::class);

        if ($recaptchaService->isEnforcedForAdmin()) {
            $recaptchaResult = $recaptchaService->verify(
                $this->recaptchaToken,
                request()->ip()
            );

            if (!$recaptchaResult['success']) {
                Notification::make()
                    ->title('Security Verification Failed')
                    ->body($recaptchaResult['message'] ?? 'Please complete the reCAPTCHA verification.')
                    ->danger()
                    ->send();

                // Reset the token
                $this->recaptchaToken = null;
                $this->dispatch('recaptcha-reset');

                return null;
            }
        }

        // Call parent authenticate method
        return parent::authenticate();
    }

    /**
     * Get reCAPTCHA configuration for the view
     */
    public function getRecaptchaConfig(): array
    {
        $recaptchaService = app(ReCaptchaService::class);
        return $recaptchaService->getConfig(forAdmin: true);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Sign In';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Sign in to your account';
    }
}
