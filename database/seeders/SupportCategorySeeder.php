<?php

namespace Database\Seeders;

use App\Models\SupportCategory;
use Illuminate\Database\Seeder;

class SupportCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Account Management',
                'description' => 'Issues related to account settings, profile updates, and general account inquiries.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Transfers & Payments',
                'description' => 'Questions about wire transfers, internal transfers, domestic payments, and transaction issues.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Deposits',
                'description' => 'Inquiries about check deposits, mobile deposits, and cryptocurrency deposits.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Cards',
                'description' => 'Card-related issues including activation, blocking, PIN reset, and transaction disputes.',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Loans',
                'description' => 'Questions about loan applications, repayments, and loan account management.',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Security',
                'description' => 'Security concerns, suspicious activity reports, and fraud prevention.',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Technical Support',
                'description' => 'Technical issues with the mobile app, website, or online banking features.',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'General Inquiry',
                'description' => 'General questions and feedback about Finora Bank services.',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            SupportCategory::create($category);
        }
    }
}
