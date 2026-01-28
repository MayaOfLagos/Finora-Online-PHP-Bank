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

        // Determine KYC status for user
        $kycStatus = 'not_started';
        if ($user) {
            if ($user->is_verified) {
                $kycStatus = 'approved';
            } else {
                $latestKyc = $user->kycVerifications()->latest()->first();
                if ($latestKyc) {
                    $kycStatus = $latestKyc->status->value;
                }
            }
        }

        // Get notifications for the authenticated user
        $notifications = [];
        $unreadNotificationsCount = 0;
        if ($user) {
            $notifications = $user->notifications()
                ->take(10)
                ->get()
                ->map(fn ($n) => $this->formatNotification($n))
                ->toArray();
            $unreadNotificationsCount = $user->unreadNotifications()->count();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'currency' => $user?->getPrimaryCurrency() ?? 'USD',
                'isImpersonating' => $isImpersonating,
                'impersonatorId' => $impersonatorId,
                'kyc_status' => $kycStatus,
            ],
            'notifications' => $notifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'settings' => [
                'general' => [
                    'site_name' => Setting::getValue('general', 'site_name', 'Finora Bank'),
                    'app_name' => Setting::getValue('general', 'app_name', 'Finora Bank'),
                    'app_tagline' => Setting::getValue('general', 'app_tagline', 'Banking Made Simple'),
                    'support_email' => Setting::getValue('general', 'support_email', 'support@finorabank.com'),
                    'support_phone' => Setting::getValue('general', 'support_phone', '+1-800-FINORA'),
                    'site_email' => Setting::getValue('general', 'site_email', 'info@finorabank.com'),
                    'site_phone' => Setting::getValue('general', 'site_phone', '+1 (800) 555-0199'),
                    'site_address' => Setting::getValue('general', 'site_address', "123 Financial District\nNew York, NY 10004"),
                ],
                'security' => [
                    'kyc_required' => (bool) Setting::getValue('security', 'kyc_required', true),
                ],
                'branding' => [
                    'logo_light' => $this->getStorageUrl(Setting::getValue('branding', 'site_logo', '')),
                    'logo_dark' => $this->getStorageUrl(Setting::getValue('branding', 'site_logo_dark', '')),
                    'favicon' => $this->getStorageUrl(Setting::getValue('branding', 'site_favicon', '')),
                    'copyright_text' => Setting::getValue('branding', 'site_copyright_text', 'Finora Bank'),
                    'copyright_year' => Setting::getValue('branding', 'site_copyright_year', date('Y')),
                    'footer_extra_text' => Setting::getValue('branding', 'site_footer_text', 'Member FDIC | Equal Housing Lender'),
                    'site_footer_text' => Setting::getValue('branding', 'site_footer_text', 'Your trusted partner for modern banking. We\'re committed to providing secure, innovative financial solutions for individuals and businesses worldwide.'),
                    'site_copyright_text' => Setting::getValue('branding', 'site_copyright_text', 'Finora Bank'),
                    'site_copyright_year' => Setting::getValue('branding', 'site_copyright_year', date('Y')),
                ],
                'seo' => [
                    'meta_title' => Setting::getValue('seo', 'meta_title', ''),
                    'meta_description' => Setting::getValue('seo', 'meta_description', ''),
                    'meta_keywords' => Setting::getValue('seo', 'meta_keywords', ''),
                    'og_title' => Setting::getValue('seo', 'og_title', ''),
                    'og_description' => Setting::getValue('seo', 'og_description', ''),
                    'og_image' => $this->getStorageUrl(Setting::getValue('seo', 'og_image', '')),
                    'twitter_card' => Setting::getValue('seo', 'twitter_card', 'summary_large_image'),
                    'twitter_site' => Setting::getValue('seo', 'twitter_site', ''),
                    'google_analytics' => Setting::getValue('seo', 'google_analytics', ''),
                    'google_tag_manager' => Setting::getValue('seo', 'google_tag_manager', ''),
                    'facebook_pixel' => Setting::getValue('seo', 'facebook_pixel', ''),
                    'robots_txt' => Setting::getValue('seo', 'robots_txt', "User-agent: *\nAllow: /"),
                    'custom_head_code' => Setting::getValue('seo', 'custom_head_code', ''),
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

    /**
     * Format a notification for the frontend.
     */
    private function formatNotification(\Illuminate\Notifications\DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $type = $data['type'] ?? class_basename($notification->type);

        // Map notification types to icons and colors
        $typeConfig = $this->getNotificationTypeConfig($type);

        return [
            'id' => $notification->id,
            'type' => $type,
            'title' => $data['title'] ?? $typeConfig['defaultTitle'],
            'message' => $data['message'] ?? $data['body'] ?? '',
            'icon' => $data['icon'] ?? $typeConfig['icon'],
            'color' => $data['color'] ?? $typeConfig['color'],
            'href' => $data['href'] ?? $data['action_url'] ?? null,
            'read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }

    /**
     * Get configuration for notification type.
     */
    private function getNotificationTypeConfig(string $type): array
    {
        $configs = [
            'transfer' => [
                'icon' => 'pi-send',
                'color' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
                'defaultTitle' => 'Transfer Update',
            ],
            'transfer_received' => [
                'icon' => 'pi-download',
                'color' => 'text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400',
                'defaultTitle' => 'Transfer Received',
            ],
            'deposit' => [
                'icon' => 'pi-download',
                'color' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
                'defaultTitle' => 'Deposit Update',
            ],
            'withdrawal' => [
                'icon' => 'pi-upload',
                'color' => 'text-orange-600 bg-orange-100 dark:bg-orange-900/30 dark:text-orange-400',
                'defaultTitle' => 'Withdrawal Update',
            ],
            'loan' => [
                'icon' => 'pi-wallet',
                'color' => 'text-violet-600 bg-violet-100 dark:bg-violet-900/30 dark:text-violet-400',
                'defaultTitle' => 'Loan Update',
            ],
            'card' => [
                'icon' => 'pi-credit-card',
                'color' => 'text-purple-600 bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400',
                'defaultTitle' => 'Card Update',
            ],
            'grant' => [
                'icon' => 'pi-dollar',
                'color' => 'text-teal-600 bg-teal-100 dark:bg-teal-900/30 dark:text-teal-400',
                'defaultTitle' => 'Grant Update',
            ],
            'security' => [
                'icon' => 'pi-shield',
                'color' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
                'defaultTitle' => 'Security Alert',
            ],
            'support' => [
                'icon' => 'pi-comments',
                'color' => 'text-cyan-600 bg-cyan-100 dark:bg-cyan-900/30 dark:text-cyan-400',
                'defaultTitle' => 'Support Update',
            ],
            'kyc' => [
                'icon' => 'pi-id-card',
                'color' => 'text-amber-600 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400',
                'defaultTitle' => 'KYC Update',
            ],
            'reward' => [
                'icon' => 'pi-star-fill',
                'color' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400',
                'defaultTitle' => 'Reward Update',
            ],
            'promo' => [
                'icon' => 'pi-gift',
                'color' => 'text-rose-600 bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400',
                'defaultTitle' => 'Special Offer',
            ],
            'admin' => [
                'icon' => 'pi-megaphone',
                'color' => 'text-indigo-600 bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400',
                'defaultTitle' => 'Admin Message',
            ],
            'system' => [
                'icon' => 'pi-info-circle',
                'color' => 'text-gray-600 bg-gray-100 dark:bg-gray-700/50 dark:text-gray-400',
                'defaultTitle' => 'System Update',
            ],
        ];

        return $configs[strtolower($type)] ?? [
            'icon' => 'pi-bell',
            'color' => 'text-indigo-600 bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400',
            'defaultTitle' => 'Notification',
        ];
    }
}
