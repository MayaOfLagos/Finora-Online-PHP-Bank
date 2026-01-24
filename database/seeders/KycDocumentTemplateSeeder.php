<?php

namespace Database\Seeders;

use App\Models\KycDocumentTemplate;
use Illuminate\Database\Seeder;

class KycDocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Passport',
                'slug' => 'passport',
                'description' => 'Valid international passport with a clear photo and readable information.',
                'instructions' => 'Please upload clear photos of your passport\'s data page. Ensure all text is readable and the photo is clearly visible. The passport must not be expired.',
                'is_required' => false,
                'requires_front_image' => true,
                'requires_back_image' => false,
                'requires_selfie' => true,
                'requires_document_number' => true,
                'accepted_formats' => ['jpg', 'jpeg', 'png', 'pdf'],
                'max_file_size' => 5120,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'National ID Card',
                'slug' => 'national-id-card',
                'description' => 'Government-issued national identification card.',
                'instructions' => 'Upload clear photos of both the front and back of your national ID card. Ensure all text and photos are clearly visible.',
                'is_required' => false,
                'requires_front_image' => true,
                'requires_back_image' => true,
                'requires_selfie' => true,
                'requires_document_number' => true,
                'accepted_formats' => ['jpg', 'jpeg', 'png', 'pdf'],
                'max_file_size' => 5120,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Driver\'s License',
                'slug' => 'drivers-license',
                'description' => 'Valid driver\'s license issued by your country.',
                'instructions' => 'Upload clear photos of both sides of your driver\'s license. The license must not be expired and all information must be readable.',
                'is_required' => false,
                'requires_front_image' => true,
                'requires_back_image' => true,
                'requires_selfie' => false,
                'requires_document_number' => true,
                'accepted_formats' => ['jpg', 'jpeg', 'png', 'pdf'],
                'max_file_size' => 5120,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Utility Bill (Address Proof)',
                'slug' => 'utility-bill',
                'description' => 'Recent utility bill showing your name and address (electricity, water, gas, or internet).',
                'instructions' => 'Upload a utility bill dated within the last 3 months showing your full name and current address. The bill must clearly show the service provider, your name, and address.',
                'is_required' => false,
                'requires_front_image' => true,
                'requires_back_image' => false,
                'requires_selfie' => false,
                'requires_document_number' => false,
                'accepted_formats' => ['jpg', 'jpeg', 'png', 'pdf'],
                'max_file_size' => 5120,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Bank Statement',
                'slug' => 'bank-statement',
                'description' => 'Recent bank statement showing your name and address.',
                'instructions' => 'Upload a bank statement from the last 3 months showing your full name and current address. You may redact sensitive financial information, but the bank name, your name, and address must be visible.',
                'is_required' => false,
                'requires_front_image' => true,
                'requires_back_image' => false,
                'requires_selfie' => false,
                'requires_document_number' => false,
                'accepted_formats' => ['jpg', 'jpeg', 'png', 'pdf'],
                'max_file_size' => 5120,
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            KycDocumentTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
