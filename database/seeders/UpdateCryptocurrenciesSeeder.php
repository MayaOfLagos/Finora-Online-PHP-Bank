<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use Illuminate\Database\Seeder;

class UpdateCryptocurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cryptocurrencies = [
            [
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'network' => 'Bitcoin',
                'coingecko_id' => 'bitcoin',
                'exchange_rate_usd' => 95000.00, // Fallback rate
                'description' => 'The first and most popular cryptocurrency',
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'network' => 'Ethereum',
                'coingecko_id' => 'ethereum',
                'exchange_rate_usd' => 3500.00,
                'description' => 'Decentralized platform for smart contracts',
            ],
            [
                'name' => 'Tether',
                'symbol' => 'USDT',
                'network' => 'TRC20',
                'coingecko_id' => 'tether',
                'exchange_rate_usd' => 1.00,
                'description' => 'Stablecoin pegged to US Dollar (TRC20 network)',
            ],
            [
                'name' => 'USD Coin',
                'symbol' => 'USDC',
                'network' => 'ERC20',
                'coingecko_id' => 'usd-coin',
                'exchange_rate_usd' => 1.00,
                'description' => 'Stablecoin pegged to US Dollar (ERC20 network)',
            ],
            [
                'name' => 'Binance Coin',
                'symbol' => 'BNB',
                'network' => 'BSC',
                'coingecko_id' => 'binancecoin',
                'exchange_rate_usd' => 650.00,
                'description' => 'Binance Smart Chain native token',
            ],
        ];

        foreach ($cryptocurrencies as $cryptoData) {
            Cryptocurrency::updateOrCreate(
                ['symbol' => $cryptoData['symbol']],
                $cryptoData
            );
        }

        $this->command->info('Cryptocurrencies updated with exchange rates and CoinGecko IDs!');
    }
}
