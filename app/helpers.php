<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get a setting value from the database.
     *
     * @param  string  $group  The setting group
     * @param  string  $key  The setting key
     * @param  mixed  $default  Default value if setting not found
     */
    function setting(string $group, string $key, mixed $default = null): mixed
    {
        return Setting::getValue($group, $key, $default);
    }
}

if (! function_exists('app_name')) {
    /**
     * Get the application name from settings.
     */
    function app_name(): string
    {
        return setting('general', 'app_name', config('app.name', 'Finora Bank'));
    }
}

if (! function_exists('copyright_text')) {
    /**
     * Get the copyright text.
     *
     * @param  bool  $includeYear  Include the copyright year
     */
    function copyright_text(bool $includeYear = true): string
    {
        $text = setting('branding', 'copyright_text', app_name());
        $year = setting('branding', 'copyright_year', date('Y'));

        return $includeYear ? "© {$year} {$text}" : $text;
    }
}

if (! function_exists('support_email')) {
    /**
     * Get the support email address.
     */
    function support_email(): string
    {
        return setting('general', 'support_email', 'support@finorabank.com');
    }
}

if (! function_exists('support_phone')) {
    /**
     * Get the support phone number.
     */
    function support_phone(): string
    {
        return setting('general', 'support_phone', '+1-800-FINORA');
    }
}
