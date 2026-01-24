<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KycDocumentTemplate>
 */
class KycDocumentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Passport',
            'National ID Card',
            'Driver\'s License',
            'Utility Bill',
            'Bank Statement',
            'Tax Certificate',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'instructions' => $this->faker->paragraph(),
            'is_required' => $this->faker->boolean(80),
            'requires_front_image' => true,
            'requires_back_image' => $this->faker->boolean(50),
            'requires_selfie' => $this->faker->boolean(30),
            'requires_document_number' => $this->faker->boolean(80),
            'accepted_formats' => ['jpg', 'jpeg', 'png', 'pdf'],
            'max_file_size' => 5120,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate the template is required.
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    /**
     * Indicate the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
