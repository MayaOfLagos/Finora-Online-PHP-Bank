<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $isImpersonating = session()->has('impersonator_id');
        $impersonatorId = session('impersonator_id');
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'currency' => $user?->getPrimaryCurrency() ?? 'USD',
                'isImpersonating' => $isImpersonating,
                'impersonatorId' => $impersonatorId,
            ],
            'settings' => [
                'general' => [
                    'app_name' => Setting::getValue('general', 'app_name', 'Finora Bank'),
                    'app_tagline' => Setting::getValue('general', 'app_tagline', 'Banking Made Simple'),
                    'support_email' => Setting::getValue('general', 'support_email', 'support@finorabank.com'),
                    'support_phone' => Setting::getValue('general', 'support_phone', '+1-800-FINORA'),
                ],
                'branding' => [
                    'logo_light' => $this->getStorageUrl(Setting::getValue('branding', 'site_logo', '')),
                    'logo_dark' => $this->getStorageUrl(Setting::getValue('branding', 'site_logo_dark', '')),
                    'favicon' => $this->getStorageUrl(Setting::getValue('branding', 'site_favicon', '')),
                    'copyright_text' => Setting::getValue('branding', 'site_copyright_text', 'Finora Bank'),
                    'copyright_year' => Setting::getValue('branding', 'site_copyright_year', date('Y')),
                    'footer_extra_text' => Setting::getValue('branding', 'site_footer_text', 'Member FDIC | Equal Housing Lender'),
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

    /**
     * Convert storage path to accessible URL
     */
    private function getStorageUrl(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // If it's already a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Convert to public URL
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
        } catch (\Exception $e) {
            // Silently fail if file doesn't exist
        }

        return '';
    }
}

