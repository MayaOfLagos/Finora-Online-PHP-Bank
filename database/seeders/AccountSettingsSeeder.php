<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AccountSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountSettings = [
            [
                'group' => 'accounts',
                'key' => 'max_accounts_per_user',
                'value' => '2',
                'type' => 'integer',
            ],
            [
                'group' => 'accounts',
                'key' => 'allow_multiple_currencies',
                'value' => '1',
                'type' => 'boolean',
            ],
            [
                'group' => 'accounts',
                'key' => 'require_kyc_for_account_creation',
                'value' => '0',
                'type' => 'boolean',
            ],
            [
                'group' => 'accounts',
                'key' => 'auto_generate_account_number',
                'value' => '1',
                'type' => 'boolean',
            ],
            [
                'group' => 'accounts',
                'key' => 'default_currency',
                'value' => 'USD',
                'type' => 'string',
            ],
        ];

        foreach ($accountSettings as $setting) {
            Setting::updateOrCreate(
                [
                    'group' => $setting['group'],
                    'key' => $setting['key'],
                ],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                ]
            );
        }

        $this->command->info('Account settings seeded successfully!');
    }
}
