<?php

namespace Database\Seeders;

use App\Models\BeneficiaryFieldSetting;
use Illuminate\Database\Seeder;

class BeneficiaryFieldSettingSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // Wire Transfer Fields
            [
                'field_key' => 'beneficiary_name',
                'field_label' => 'Beneficiary Name',
                'is_enabled' => true,
                'is_required' => true,
                'field_type' => 'text',
                'transfer_type' => 'both',
                'sort_order' => 1,
            ],
            [
                'field_key' => 'beneficiary_account',
                'field_label' => 'Account/IBAN Number',
                'is_enabled' => true,
                'is_required' => true,
                'field_type' => 'text',
                'transfer_type' => 'both',
                'sort_order' => 2,
            ],
            [
                'field_key' => 'beneficiary_bank_name',
                'field_label' => 'Bank Name',
                'is_enabled' => true,
                'is_required' => true,
                'field_type' => 'text',
                'transfer_type' => 'both',
                'sort_order' => 3,
            ],
            [
                'field_key' => 'beneficiary_bank_address',
                'field_label' => 'Bank Address',
                'is_enabled' => true,
                'is_required' => true,
                'field_type' => 'textarea',
                'transfer_type' => 'wire',
                'sort_order' => 4,
            ],
            [
                'field_key' => 'beneficiary_country',
                'field_label' => 'Country',
                'is_enabled' => true,
                'is_required' => true,
                'field_type' => 'select',
                'transfer_type' => 'both',
                'sort_order' => 5,
            ],
            [
                'field_key' => 'swift_code',
                'field_label' => 'SWIFT/BIC Code',
                'is_enabled' => true,
                'is_required' => true,
                'field_type' => 'text',
                'transfer_type' => 'wire',
                'sort_order' => 6,
            ],
            [
                'field_key' => 'routing_number',
                'field_label' => 'Routing Number',
                'is_enabled' => true,
                'is_required' => false,
                'field_type' => 'text',
                'transfer_type' => 'wire',
                'sort_order' => 7,
            ],
        ];

        foreach ($fields as $field) {
            BeneficiaryFieldSetting::updateOrCreate(
                ['field_key' => $field['field_key']],
                $field
            );
        }
    }
}
