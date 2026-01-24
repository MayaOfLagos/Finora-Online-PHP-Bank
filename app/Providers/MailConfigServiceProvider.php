<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureMailSettings();
    }

    /**
     * Configure mail settings from the database.
     */
    protected function configureMailSettings(): void
    {
        // Skip if settings table doesn't exist (during migrations)
        if (! $this->settingsTableExists()) {
            return;
        }

        try {
            // Get mail settings from database
            $mailer = Setting::getValue('mail', 'mailer', null);
            $host = Setting::getValue('mail', 'host', null);
            $port = Setting::getValue('mail', 'port', null);
            $username = Setting::getValue('mail', 'username', null);
            $password = Setting::getValue('mail', 'password', null);
            $encryption = Setting::getValue('mail', 'encryption', null);
            $fromAddress = Setting::getValue('mail', 'from_address', null);
            $fromName = Setting::getValue('mail', 'from_name', null);

            // Only override if settings are configured in database
            if ($mailer && $mailer !== '') {
                Config::set('mail.default', $mailer);
            }

            // Configure SMTP settings if any are provided
            if ($host && $host !== '') {
                Config::set('mail.mailers.smtp.host', $host);
            }

            if ($port && $port !== '') {
                Config::set('mail.mailers.smtp.port', (int) $port);
            }

            if ($username && $username !== '') {
                Config::set('mail.mailers.smtp.username', $username);
            }

            if ($password && $password !== '') {
                Config::set('mail.mailers.smtp.password', $password);
            }

            if ($encryption !== null) {
                // Handle empty string as null (no encryption)
                $encryptionValue = $encryption === '' ? null : $encryption;
                Config::set('mail.mailers.smtp.encryption', $encryptionValue);
            }

            // Configure from address
            if ($fromAddress && $fromAddress !== '') {
                Config::set('mail.from.address', $fromAddress);
            }

            if ($fromName && $fromName !== '') {
                Config::set('mail.from.name', $fromName);
            }

        } catch (\Exception $e) {
            // Silently fail - use default .env settings
            report($e);
        }
    }

    /**
     * Check if the settings table exists.
     */
    protected function settingsTableExists(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Exception $e) {
            return false;
        }
    }
}
