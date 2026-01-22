<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use App\Models\CryptoWallet;
use Illuminate\Database\Seeder;

class CryptocurrencySeeder extends Seeder
{
    public function run(): void
    {
        $cryptos = [
            [
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'network' => 'Bitcoin',
                'is_active' => true,
                'wallets' => [
                    ['wallet_address' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 'label' => 'Primary BTC Wallet'],
                ],
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'network' => 'Ethereum',
                'is_active' => true,
                'wallets' => [
                    ['wallet_address' => '0x742d35Cc6634C0532925a3b844Bc9e7595f3A2eD', 'label' => 'Primary ETH Wallet'],
                ],
            ],
            [
                'name' => 'USDT (TRC20)',
                'symbol' => 'USDT',
                'network' => 'TRC20',
                'is_active' => true,
                'wallets' => [
                    ['wallet_address' => 'TN2tfcBw4Y8KGVg9yVJqRgJ5NfNSKBAaaA', 'label' => 'Primary USDT TRC20 Wallet'],
                ],
            ],
            [
                'name' => 'USDT (ERC20)',
                'symbol' => 'USDT',
                'network' => 'ERC20',
                'is_active' => true,
                'wallets' => [
                    ['wallet_address' => '0xdAC17F958D2ee523a2206206994597C13D831ec7', 'label' => 'Primary USDT ERC20 Wallet'],
                ],
            ],
            [
                'name' => 'USDC',
                'symbol' => 'USDC',
                'network' => 'Ethereum',
                'is_active' => true,
                'wallets' => [
                    ['wallet_address' => '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48', 'label' => 'Primary USDC Wallet'],
                ],
            ],
        ];

        foreach ($cryptos as $cryptoData) {
            $wallets = $cryptoData['wallets'];
            unset($cryptoData['wallets']);

            $crypto = Cryptocurrency::create($cryptoData);

            foreach ($wallets as $wallet) {
                CryptoWallet::create([
                    'cryptocurrency_id' => $crypto->id,
                    'wallet_address' => $wallet['wallet_address'],
                    'label' => $wallet['label'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
