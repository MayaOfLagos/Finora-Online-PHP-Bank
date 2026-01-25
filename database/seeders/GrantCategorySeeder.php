<?php

namespace Database\Seeders;

use App\Models\GrantCategory;
use Illuminate\Database\Seeder;

class GrantCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Education',
                'description' => 'Grants for educational purposes, including tuition assistance, scholarships, and training programs.',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Business Development',
                'description' => 'Support for small businesses, startups, and entrepreneurs looking to expand or start new ventures.',
                'color' => '#10B981',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Healthcare',
                'description' => 'Medical assistance grants for healthcare expenses, treatments, and health-related initiatives.',
                'color' => '#EF4444',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Housing',
                'description' => 'Grants for home purchase, renovation, or rental assistance programs.',
                'color' => '#F59E0B',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Community Development',
                'description' => 'Funding for community projects, infrastructure, and social initiatives.',
                'color' => '#8B5CF6',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Technology & Innovation',
                'description' => 'Grants supporting technological advancement, research, and innovation projects.',
                'color' => '#06B6D4',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Agriculture',
                'description' => 'Support for farmers, agricultural projects, and rural development initiatives.',
                'color' => '#84CC16',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Relief',
                'description' => 'Financial assistance for emergencies, disasters, and urgent needs.',
                'color' => '#DC2626',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            GrantCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
