<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cryptocurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'network',
        'icon',
        'exchange_rate_usd',
        'coingecko_id',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate_usd' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get current exchange rate (live or manual)
     */
    public function getCurrentRate(): ?float
    {
        return app(\App\Services\CryptoExchangeRateService::class)->getExchangeRate($this);
    }

    /**
     * Convert USD to crypto amount
     */
    public function convertUsdToCrypto(float $usdAmount): float
    {
        $rate = $this->getCurrentRate();
        if (! $rate) {
            return 0;
        }

        return app(\App\Services\CryptoExchangeRateService::class)->convertUsdToCrypto($usdAmount, $rate);
    }

    // ==================== RELATIONSHIPS ====================

    public function wallets(): HasMany
    {
        return $this->hasMany(CryptoWallet::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(CryptoDeposit::class);
    }
}
