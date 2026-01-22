<?php

namespace Database\Factories;

use App\Models\KnowledgeBaseArticle;
use App\Models\SupportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KnowledgeBaseArticle>
 */
class KnowledgeBaseArticleFactory extends Factory
{
    protected $model = KnowledgeBaseArticle::class;

    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'category_id' => SupportCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(5, true),
            'is_published' => true,
            'view_count' => fake()->numberBetween(0, 5000),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
