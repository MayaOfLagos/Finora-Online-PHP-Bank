<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Profile;
use App\Models\Setting;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(Profile::class)
            ->multiFactorAuthentication([
                // App authentication (Google Authenticator, Authy, etc.)
                AppAuthentication::make()
                    ->brandName(config('app.name', 'Finora Bank'))
                    ->recoverable()
                    ->recoveryCodeCount(8),

                // Email authentication (OTP via email)
                EmailAuthentication::make()
                    ->codeExpiryMinutes(10),
            ])
            ->brandName($this->getBrandName())
            ->colors([
                'primary' => Color::Emerald,
                'danger' => Color::Red,
                'warning' => Color::Orange,
                'success' => Color::Green,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Widgets are auto-discovered with sort order
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa() // Enable SPA mode for instant page loads
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications() // Enable real-time notifications
            ->databaseNotificationsPolling('30s') // Poll every 30 seconds
            ->globalSearch(false) // Can enable later if needed
            ->navigationGroups([
                'User Management',
                'Transfers',
                'Deposits',
                'Loans',
                'Cards',
                'Grants',
                'Support',
                'Settings',
            ]);

        // Apply branding from settings
        $this->applyBranding($panel);

        return $panel;
    }

    protected function getBrandName(): string
    {
        try {
            return Setting::getValue('general', 'site_name', 'Finora Bank').' Admin';
        } catch (\Exception $e) {
            return 'Finora Bank Admin';
        }
    }

    protected function applyBranding(Panel $panel): void
    {
        try {
            // Apply Logo
            $logo = Setting::getValue('branding', 'site_logo', '');
            if ($logo && Storage::disk('public')->exists($logo)) {
                $panel->brandLogo(Storage::url($logo));
            }

            // Apply Dark Mode Logo
            $logoDark = Setting::getValue('branding', 'site_logo_dark', '');
            if ($logoDark && Storage::disk('public')->exists($logoDark)) {
                $panel->darkModeBrandLogo(Storage::url($logoDark));
            }

            // Apply Favicon
            $favicon = Setting::getValue('branding', 'site_favicon', '');
            if ($favicon && Storage::disk('public')->exists($favicon)) {
                $panel->favicon(Storage::url($favicon));
            }
        } catch (\Exception $e) {
            // Silently fail if settings table doesn't exist yet (during migrations)
        }
    }
}
