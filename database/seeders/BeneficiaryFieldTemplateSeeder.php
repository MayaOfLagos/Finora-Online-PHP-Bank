<?php

namespace Database\Seeders;

use App\Models\BeneficiaryFieldTemplate;
use Illuminate\Database\Seeder;

class BeneficiaryFieldTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // Wire Transfer Fields
            [
                'field_key' => 'beneficiary_name',
                'field_label' => 'Beneficiary Name',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'wire',
                'display_order' => 1,
                'placeholder' => 'Enter beneficiary full name',
                'helper_text' => 'Full legal name of the recipient',
            ],
            [
                'field_key' => 'beneficiary_account',
                'field_label' => 'Beneficiary Account Number',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'wire',
                'display_order' => 2,
                'placeholder' => 'Enter account number',
                'helper_text' => 'International account number',
            ],
            [
                'field_key' => 'beneficiary_bank_name',
                'field_label' => 'Bank Name',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'wire',
                'display_order' => 3,
                'placeholder' => 'Enter bank name',
            ],
            [
                'field_key' => 'beneficiary_bank_address',
                'field_label' => 'Bank Address',
                'field_type' => 'textarea',
                'is_required' => false,
                'is_enabled' => true,
                'applies_to' => 'wire',
                'display_order' => 4,
                'placeholder' => 'Enter bank full address',
            ],
            [
                'field_key' => 'beneficiary_country',
                'field_label' => 'Country',
                'field_type' => 'country',
                'is_required' => false,
                'is_enabled' => true,
                'applies_to' => 'wire',
                'display_order' => 5,
                'placeholder' => 'Select country',
            ],
            [
                'field_key' => 'swift_code',
                'field_label' => 'SWIFT/BIC Code',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'wire',
                'display_order' => 6,
                'placeholder' => 'e.g., CHASUS33',
                'helper_text' => '8 or 11 character SWIFT code',
            ],
            [
                'field_key' => 'routing_number',
                'field_label' => 'Routing Number',
                'field_type' => 'text',
                'is_required' => false,
                'is_enabled' => true,
                'applies_to' => 'wire',
                'display_order' => 7,
                'placeholder' => 'Optional routing number',
            ],

            // Domestic Transfer Fields
            [
                'field_key' => 'beneficiary_name',
                'field_label' => 'Beneficiary Name',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'domestic',
                'display_order' => 1,
                'placeholder' => 'Enter beneficiary name',
            ],
            [
                'field_key' => 'beneficiary_account',
                'field_label' => 'Account Number',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'domestic',
                'display_order' => 2,
                'placeholder' => 'Enter account number',
            ],
            [
                'field_key' => 'beneficiary_bank_name',
                'field_label' => 'Bank Name',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'domestic',
                'display_order' => 3,
                'placeholder' => 'Enter bank name',
            ],
            [
                'field_key' => 'routing_number',
                'field_label' => 'Routing Number',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'domestic',
                'display_order' => 4,
                'placeholder' => 'Enter routing number',
            ],

            // Internal Transfer Fields
            [
                'field_key' => 'beneficiary_account',
                'field_label' => 'Recipient Account',
                'field_type' => 'text',
                'is_required' => true,
                'is_enabled' => true,
                'applies_to' => 'internal',
                'display_order' => 1,
                'placeholder' => 'Enter recipient account number',
                'helper_text' => 'Account must be within Finora Bank',
            ],
        ];

        foreach ($fields as $field) {
            BeneficiaryFieldTemplate::updateOrCreate(
                [
                    'field_key' => $field['field_key'],
                    'applies_to' => $field['applies_to'],
                ],
                $field
            );
        }
    }
}

