<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Enums\KycStatus;
use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KycVerification>
 */
class KycVerificationFactory extends Factory
{
    protected $model = KycVerification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_type' => fake()->randomElement(DocumentType::cases()),
            'document_number' => fake()->regexify('[A-Z0-9]{10}'),
            'document_front_path' => 'kyc/documents/'.fake()->uuid().'_front.jpg',
            'document_back_path' => 'kyc/documents/'.fake()->uuid().'_back.jpg',
            'selfie_path' => 'kyc/selfies/'.fake()->uuid().'.jpg',
            'status' => KycStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => KycStatus::Approved,
            'verified_at' => now(),
            'verified_by' => User::factory()->admin(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => KycStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
