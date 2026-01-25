<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class ConfigureSmtp extends Command
{
    protected $signature = 'app:configure-smtp {--preset=mailtrap}';

    protected $description = 'Configure SMTP settings for email sending';

    public function handle(): int
    {
        $preset = $this->option('preset');

        if ($preset === 'mailtrap') {
            return $this->configureMailtrap();
        }

        return $this->interactiveSetup();
    }

    /**
     * Configure Mailtrap SMTP settings
     */
    private function configureMailtrap(): int
    {
        $host = 'sandbox.smtp.mailtrap.io';
        $port = 2525;
        $username = 'd29d07137cdbe6';
        $password = '8e99f156fdd22d';
        $encryption = 'tls';

        $this->info('Configuring Mailtrap SMTP settings...');

        // Store settings in database
        Setting::setValue('mail', 'driver', 'smtp', 'string');
        Setting::setValue('mail', 'host', $host, 'string');
        Setting::setValue('mail', 'port', (string) $port, 'string');
        Setting::setValue('mail', 'username', $username, 'string');
        Setting::setValue('mail', 'password', $password, 'string');
        Setting::setValue('mail', 'encryption', $encryption, 'string');

        // Also update .env file
        $this->updateEnvFile([
            'MAIL_DRIVER' => 'smtp',
            'MAIL_HOST' => $host,
            'MAIL_PORT' => $port,
            'MAIL_USERNAME' => $username,
            'MAIL_PASSWORD' => $password,
            'MAIL_ENCRYPTION' => $encryption,
            'MAIL_FROM_ADDRESS' => 'noreply@finorabank.com',
            'MAIL_FROM_NAME' => 'Finora Bank',
        ]);

        $this->info('✓ Mailtrap SMTP settings configured successfully');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', $host],
                ['Port', $port],
                ['Username', $username],
                ['Encryption', $encryption],
                ['Driver', 'SMTP'],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Interactive SMTP setup
     */
    private function interactiveSetup(): int
    {
        $this->info('=== SMTP Configuration Setup ===');

        $host = $this->ask('SMTP Host', 'smtp.mailtrap.io');
        $port = $this->ask('SMTP Port', '2525');
        $username = $this->ask('SMTP Username');
        $password = $this->secret('SMTP Password');
        $encryption = $this->choice('Encryption', ['tls', 'ssl', 'none'], 0);
        $fromAddress = $this->ask('From Email Address', 'noreply@finorabank.com');
        $fromName = $this->ask('From Name', 'Finora Bank');

        // Confirm settings
        $this->info('');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', $host],
                ['Port', $port],
                ['Username', $username],
                ['Encryption', $encryption],
                ['From Address', $fromAddress],
                ['From Name', $fromName],
            ]
        );

        if (! $this->confirm('Save these settings?')) {
            $this->warn('Configuration cancelled.');

            return self::FAILURE;
        }

        // Store in database
        Setting::setValue('mail', 'driver', 'smtp', 'string');
        Setting::setValue('mail', 'host', $host, 'string');
        Setting::setValue('mail', 'port', (string) $port, 'string');
        Setting::setValue('mail', 'username', $username, 'string');
        Setting::setValue('mail', 'password', $password, 'string');
        Setting::setValue('mail', 'encryption', $encryption, 'string');
        Setting::setValue('mail', 'from_address', $fromAddress, 'string');
        Setting::setValue('mail', 'from_name', $fromName, 'string');

        // Also update .env file
        $this->updateEnvFile([
            'MAIL_DRIVER' => 'smtp',
            'MAIL_HOST' => $host,
            'MAIL_PORT' => $port,
            'MAIL_USERNAME' => $username,
            'MAIL_PASSWORD' => $password,
            'MAIL_ENCRYPTION' => $encryption,
            'MAIL_FROM_ADDRESS' => $fromAddress,
            'MAIL_FROM_NAME' => $fromName,
        ]);

        $this->info('✓ SMTP settings configured successfully');

        return self::SUCCESS;
    }

    /**
     * Update .env file with new values
     */
    private function updateEnvFile(array $settings): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->warn('.env file not found. Skipping .env update.');

            return;
        }

        $content = file_get_contents($envPath);

        foreach ($settings as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}=\"{$value}\"";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($envPath, $content);
    }
}
