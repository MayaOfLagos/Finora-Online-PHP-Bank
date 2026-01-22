<?php

namespace Database\Factories;

use App\Models\Cryptocurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cryptocurrency>
 */
class CryptocurrencyFactory extends Factory
{
    protected $model = Cryptocurrency::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Bitcoin', 'Ethereum', 'USDT', 'USDC', 'Litecoin']),
            'symbol' => fake()->unique()->randomElement(['BTC', 'ETH', 'USDT', 'USDC', 'LTC']),
            'network' => fake()->randomElement(['Bitcoin', 'Ethereum', 'TRC20', 'ERC20']),
            'icon' => null,
            'is_active' => true,
        ];
    }
}
