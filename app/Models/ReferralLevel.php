<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralLevel extends Model
{
    protected $fillable = [
        'level',
        'name',
        'reward_type',
        'reward_amount',
        'min_referrals_required',
        'color',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'reward_amount' => 'integer',
            'min_referrals_required' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get all referrals made at this level.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_level_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to only active levels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by level number.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('level', 'asc');
    }

    // ==================== HELPERS ====================

    /**
     * Calculate the reward amount for this level.
     *
     * @param  int|null  $baseAmount  Base amount in cents (for percentage calculations)
     * @return int Reward amount in cents
     */
    public function calculateReward(?int $baseAmount = null): int
    {
        if ($this->reward_type === 'percentage') {
            $base = $baseAmount ?? ReferralSetting::get('base_reward_amount', 5000);

            return (int) floor(($this->reward_amount / 100) * $base);
        }

        return $this->reward_amount;
    }

    /**
     * Get formatted reward display.
     */
    public function getFormattedRewardAttribute(): string
    {
        if ($this->reward_type === 'percentage') {
            return ($this->reward_amount / 100).'%';
        }

        return '$'.number_format($this->reward_amount / 100, 2);
    }

    /**
     * Get the level that a user qualifies for based on their referral count.
     */
    public static function getLevelForReferralCount(int $referralCount): ?self
    {
        return static::active()
            ->where('min_referrals_required', '<=', $referralCount)
            ->orderBy('min_referrals_required', 'desc')
            ->first();
    }

    /**
     * Get the starting level (level 1).
     */
    public static function getStartingLevel(): ?self
    {
        return static::active()->where('level', 1)->first();
    }

    /**
     * Generate default levels.
     */
    public static function generateDefaultLevels(int $count = 5): void
    {
        $defaults = [
            ['level' => 1, 'name' => 'Starter', 'reward_amount' => 500, 'min_referrals' => 0, 'color' => '#6B7280'],
            ['level' => 2, 'name' => 'Bronze', 'reward_amount' => 750, 'min_referrals' => 5, 'color' => '#CD7F32'],
            ['level' => 3, 'name' => 'Silver', 'reward_amount' => 1000, 'min_referrals' => 15, 'color' => '#C0C0C0'],
            ['level' => 4, 'name' => 'Gold', 'reward_amount' => 1500, 'min_referrals' => 30, 'color' => '#FFD700'],
            ['level' => 5, 'name' => 'Platinum', 'reward_amount' => 2500, 'min_referrals' => 50, 'color' => '#E5E4E2'],
            ['level' => 6, 'name' => 'Diamond', 'reward_amount' => 4000, 'min_referrals' => 100, 'color' => '#B9F2FF'],
            ['level' => 7, 'name' => 'Elite', 'reward_amount' => 6000, 'min_referrals' => 200, 'color' => '#9400D3'],
            ['level' => 8, 'name' => 'Champion', 'reward_amount' => 8000, 'min_referrals' => 350, 'color' => '#FF4500'],
            ['level' => 9, 'name' => 'Legend', 'reward_amount' => 10000, 'min_referrals' => 500, 'color' => '#DC143C'],
            ['level' => 10, 'name' => 'Titan', 'reward_amount' => 15000, 'min_referrals' => 1000, 'color' => '#000000'],
        ];

        $count = min($count, 10);

        for ($i = 0; $i < $count; $i++) {
            static::updateOrCreate(
                ['level' => $defaults[$i]['level']],
                [
                    'name' => $defaults[$i]['name'],
                    'reward_type' => 'fixed',
                    'reward_amount' => $defaults[$i]['reward_amount'],
                    'min_referrals_required' => $defaults[$i]['min_referrals'],
                    'color' => $defaults[$i]['color'],
                    'is_active' => true,
                ]
            );
        }
    }
}
