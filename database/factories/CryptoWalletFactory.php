<?php

namespace Database\Factories;

use App\Models\Cryptocurrency;
use App\Models\CryptoWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CryptoWallet>
 */
class CryptoWalletFactory extends Factory
{
    protected $model = CryptoWallet::class;

    public function definition(): array
    {
        return [
            'cryptocurrency_id' => Cryptocurrency::factory(),
            'wallet_address' => fake()->regexify('[a-zA-Z0-9]{34,42}'),
            'label' => fake()->optional()->words(2, true),
            'is_active' => true,
        ];
    }
}
