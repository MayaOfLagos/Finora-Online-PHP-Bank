<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            ['group' => 'general', 'key' => 'app_name', 'value' => 'Finora Bank', 'type' => 'string'],
            ['group' => 'general', 'key' => 'app_tagline', 'value' => 'Banking Made Simple', 'type' => 'string'],
            ['group' => 'general', 'key' => 'support_email', 'value' => 'support@finorabank.com', 'type' => 'string'],
            ['group' => 'general', 'key' => 'support_phone', 'value' => '+1-800-FINORA', 'type' => 'string'],

            // Transfer Limits
            ['group' => 'transfers', 'key' => 'wire_transfer_daily_limit', 'value' => '10000000', 'type' => 'integer'],
            ['group' => 'transfers', 'key' => 'internal_transfer_daily_limit', 'value' => '5000000', 'type' => 'integer'],
            ['group' => 'transfers', 'key' => 'domestic_transfer_daily_limit', 'value' => '5000000', 'type' => 'integer'],
            ['group' => 'transfers', 'key' => 'account_transfer_daily_limit', 'value' => '10000000', 'type' => 'integer'],

            // Transfer Fees (in cents)
            ['group' => 'fees', 'key' => 'wire_transfer_fee_percent', 'value' => '2.5', 'type' => 'string'],
            ['group' => 'fees', 'key' => 'wire_transfer_min_fee', 'value' => '2500', 'type' => 'integer'],
            ['group' => 'fees', 'key' => 'domestic_transfer_fee_percent', 'value' => '0.5', 'type' => 'string'],
            ['group' => 'fees', 'key' => 'internal_transfer_fee', 'value' => '0', 'type' => 'integer'],

            // Security Settings
            ['group' => 'security', 'key' => 'otp_expiry_minutes', 'value' => '10', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'lockout_duration_minutes', 'value' => '30', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'session_timeout_minutes', 'value' => '30', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'require_2fa_for_transfers', 'value' => '1', 'type' => 'boolean'],

            // Deposit Settings
            ['group' => 'deposits', 'key' => 'check_hold_days', 'value' => '5', 'type' => 'integer'],
            ['group' => 'deposits', 'key' => 'crypto_min_confirmations', 'value' => '3', 'type' => 'integer'],

            // Card Settings
            ['group' => 'cards', 'key' => 'virtual_card_instant_issue', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'cards', 'key' => 'physical_card_delivery_days', 'value' => '7', 'type' => 'integer'],

            // Notification Settings
            ['group' => 'notifications', 'key' => 'email_enabled', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'sms_enabled', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'push_enabled', 'value' => '0', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
