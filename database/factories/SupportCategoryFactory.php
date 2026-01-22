<?php

namespace Database\Factories;

use App\Models\SupportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportCategory>
 */
class SupportCategoryFactory extends Factory
{
    protected $model = SupportCategory::class;

    protected static int $order = 0;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Account Issues',
                'Transfers',
                'Cards',
                'Loans',
                'Technical Support',
                'Billing',
                'Security',
                'General Inquiry',
            ]),
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => ++static::$order,
        ];
    }
}
