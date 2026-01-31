<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'status',
        'reward_amount',
        'reward_currency',
        'completed_at',
        'rewarded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount' => 'integer',
            'completed_at' => 'datetime',
            'rewarded_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user who made the referral.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred.
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    // ==================== STATUS HELPERS ====================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRewarded(): bool
    {
        return $this->status === 'rewarded';
    }

    /**
     * Mark referral as completed (referred user completed required action).
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark referral as rewarded (referrer received reward).
     */
    public function markRewarded(int $rewardAmount, string $currency = 'USD'): void
    {
        $this->update([
            'status' => 'rewarded',
            'reward_amount' => $rewardAmount,
            'reward_currency' => $currency,
            'rewarded_at' => now(),
        ]);
    }

    // ==================== FORMATTED ATTRIBUTES ====================

    /**
     * Get formatted reward amount.
     */
    public function getFormattedRewardAttribute(): string
    {
        return number_format($this->reward_amount / 100, 2).' '.$this->reward_currency;
    }
}
