<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'currency' => $user?->getPrimaryCurrency() ?? 'USD',
            ],
            'settings' => [
                'general' => [
                    'app_name' => Setting::getValue('general', 'app_name', 'Finora Bank'),
                    'app_tagline' => Setting::getValue('general', 'app_tagline', 'Banking Made Simple'),
                    'support_email' => Setting::getValue('general', 'support_email', 'support@finorabank.com'),
                    'support_phone' => Setting::getValue('general', 'support_phone', '+1-800-FINORA'),
                ],
                'branding' => [
                    'logo_light' => Setting::getValue('branding', 'logo_light', ''),
                    'logo_dark' => Setting::getValue('branding', 'logo_dark', ''),
                    'favicon' => Setting::getValue('branding', 'favicon', ''),
                    'copyright_text' => Setting::getValue('branding', 'copyright_text', 'Finora Bank'),
                    'copyright_year' => Setting::getValue('branding', 'copyright_year', date('Y')),
                    'footer_extra_text' => Setting::getValue('branding', 'footer_extra_text', 'Member FDIC | Equal Housing Lender'),
                ],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'transfer' => fn () => $request->session()->get('transfer'),
                'nextStep' => fn () => $request->session()->get('nextStep'),
                'deposit' => fn () => $request->session()->get('deposit'),
            ],
        ];
    }
}
