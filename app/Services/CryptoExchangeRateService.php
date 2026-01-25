<?php

namespace App\Services;

use App\Models\Cryptocurrency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoExchangeRateService
{
    private const COINGECKO_API = 'https://api.coingecko.com/api/v3';

    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get exchange rate for a cryptocurrency in USD
     * Tries CoinGecko API first, falls back to manual rate
     */
    public function getExchangeRate(Cryptocurrency $cryptocurrency): ?float
    {
        $cacheKey = "crypto_rate_{$cryptocurrency->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($cryptocurrency) {
            // Try CoinGecko API if coingecko_id is set
            if ($cryptocurrency->coingecko_id) {
                $liveRate = $this->fetchLiveRate($cryptocurrency->coingecko_id);
                if ($liveRate !== null) {
                    return $liveRate;
                }
            }

            // Fallback to manual rate
            return $cryptocurrency->exchange_rate_usd;
        });
    }

    /**
     * Get exchange rates for multiple cryptocurrencies
     */
    public function getBulkExchangeRates(array $cryptocurrencies): array
    {
        $rates = [];

        foreach ($cryptocurrencies as $crypto) {
            $rates[$crypto->id] = [
                'rate' => $this->getExchangeRate($crypto),
                'symbol' => $crypto->symbol,
                'name' => $crypto->name,
            ];
        }

        return $rates;
    }

    /**
     * Convert USD amount to crypto amount
     */
    public function convertUsdToCrypto(float $usdAmount, float $exchangeRate): float
    {
        if ($exchangeRate <= 0) {
            return 0;
        }

        return $usdAmount / $exchangeRate;
    }

    /**
     * Convert crypto amount to USD amount
     */
    public function convertCryptoToUsd(float $cryptoAmount, float $exchangeRate): float
    {
        return $cryptoAmount * $exchangeRate;
    }

    /**
     * Fetch live rate from CoinGecko API
     */
    private function fetchLiveRate(string $coingeckoId): ?float
    {
        try {
            $response = Http::timeout(5)->get(self::COINGECKO_API.'/simple/price', [
                'ids' => $coingeckoId,
                'vs_currencies' => 'usd',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data[$coingeckoId]['usd'] ?? null;
            }

            Log::warning('CoinGecko API failed', [
                'coingecko_id' => $coingeckoId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CoinGecko API error', [
                'coingecko_id' => $coingeckoId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Clear cached rate for a cryptocurrency
     */
    public function clearCache(int $cryptocurrencyId): void
    {
        Cache::forget("crypto_rate_{$cryptocurrencyId}");
    }

    /**
     * Clear all cached rates
     */
    public function clearAllCache(): void
    {
        $cryptocurrencies = Cryptocurrency::all();

        foreach ($cryptocurrencies as $crypto) {
            $this->clearCache($crypto->id);
        }
    }
}
