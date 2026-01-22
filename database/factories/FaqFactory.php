<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\SupportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    protected static int $order = 0;

    public function definition(): array
    {
        return [
            'category_id' => SupportCategory::factory(),
            'question' => fake()->sentence().'?',
            'answer' => fake()->paragraphs(2, true),
            'is_published' => true,
            'sort_order' => ++static::$order,
            'view_count' => fake()->numberBetween(0, 1000),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
