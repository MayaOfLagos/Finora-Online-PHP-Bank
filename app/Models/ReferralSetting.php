<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ReferralSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Cache duration in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    // ==================== STATIC HELPERS ====================

    /**
     * Get a setting value with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "referral_setting_{$key}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value and clear cache.
     */
    public static function set(string $key, mixed $value, string $type = 'string', ?string $group = 'general', ?string $description = null): void
    {
        $stringValue = static::toStringValue($value, $type);

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget("referral_setting_{$key}");
    }

    /**
     * Check if a boolean setting is enabled.
     */
    public static function isEnabled(string $key): bool
    {
        return (bool) static::get($key, false);
    }

    /**
     * Clear all referral settings cache.
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("referral_setting_{$setting->key}");
        }
    }

    /**
     * Get all settings as an array.
     */
    public static function getAllSettings(): array
    {
        return static::all()->mapWithKeys(function ($setting) {
            return [$setting->key => static::castValue($setting->value, $setting->type)];
        })->toArray();
    }

    /**
     * Initialize default settings.
     */
    public static function initializeDefaults(): void
    {
        $defaults = [
            // Program Settings
            [
                'key' => 'referral_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'program',
                'description' => 'Enable or disable the referral program',
            ],
            // New User Bonus Settings
            [
                'key' => 'new_user_bonus_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'rewards',
                'description' => 'Enable welcome bonus for new users who sign up via referral',
            ],
            [
                'key' => 'new_user_bonus_amount',
                'value' => '500',
                'type' => 'integer',
                'group' => 'rewards',
                'description' => 'Welcome bonus amount in cents for new users',
            ],
            // Base Settings
            [
                'key' => 'base_reward_amount',
                'value' => '5000',
                'type' => 'integer',
                'group' => 'rewards',
                'description' => 'Base amount in cents for percentage calculations',
            ],
            // Timing Settings
            [
                'key' => 'reward_delay_hours',
                'value' => '0',
                'type' => 'integer',
                'group' => 'timing',
                'description' => 'Hours before rewards become claimable (0 = instant)',
            ],
            // Requirements
            [
                'key' => 'min_deposit_for_reward',
                'value' => '0',
                'type' => 'integer',
                'group' => 'requirements',
                'description' => 'Minimum deposit in cents required before claiming rewards',
            ],
        ];

        foreach ($defaults as $setting) {
            static::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    // ==================== PRIVATE HELPERS ====================

    /**
     * Cast a string value to its proper type.
     */
    private static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Convert a value to string for storage.
     */
    private static function toStringValue(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };
    }
}
