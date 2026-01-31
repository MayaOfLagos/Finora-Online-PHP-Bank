<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Referral extends Model
{
    protected $fillable = [
        'uuid',
        'referrer_id',
        'referred_id',
        'referral_code_used',
        'referrer_level_id',
        'referrer_reward_id',
        'referred_reward_id',
        'referrer_earned',
        'referred_earned',
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
            'referrer_earned' => 'integer',
            'referred_earned' => 'integer',
            'reward_amount' => 'integer',
            'completed_at' => 'datetime',
            'rewarded_at' => 'datetime',
            'status' => ReferralStatus::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Referral $referral) {
            if (empty($referral->uuid)) {
                $referral->uuid = Str::uuid();
            }
        });
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

    /**
     * Get the referral level at the time of referral.
     */
    public function referrerLevel(): BelongsTo
    {
        return $this->belongsTo(ReferralLevel::class, 'referrer_level_id');
    }

    /**
     * Get the reward given to the referrer.
     */
    public function referrerReward(): BelongsTo
    {
        return $this->belongsTo(Reward::class, 'referrer_reward_id');
    }

    /**
     * Get the reward given to the referred user.
     */
    public function referredReward(): BelongsTo
    {
        return $this->belongsTo(Reward::class, 'referred_reward_id');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', ReferralStatus::Pending);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', ReferralStatus::Completed);
    }

    // ==================== STATUS HELPERS ====================

    public function isPending(): bool
    {
        return $this->status === ReferralStatus::Pending;
    }

    public function isCompleted(): bool
    {
        return $this->status === ReferralStatus::Completed;
    }

    public function isCancelled(): bool
    {
        return $this->status === ReferralStatus::Cancelled;
    }

    /**
     * Mark referral as completed with rewards.
     */
    public function markCompleted(int $referrerEarned, int $referredEarned = 0): void
    {
        $this->update([
            'status' => ReferralStatus::Completed,
            'referrer_earned' => $referrerEarned,
            'referred_earned' => $referredEarned,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark referral as cancelled.
     */
    public function markCancelled(?string $notes = null): void
    {
        $this->update([
            'status' => ReferralStatus::Cancelled,
            'notes' => $notes,
        ]);
    }

    // ==================== FORMATTED ATTRIBUTES ====================

    /**
     * Get total earnings for this referral (referrer + referred).
     */
    public function getTotalEarningsAttribute(): int
    {
        return $this->referrer_earned + $this->referred_earned;
    }

    /**
     * Get formatted referrer earnings.
     */
    public function getFormattedReferrerEarnedAttribute(): string
    {
        return '$'.number_format($this->referrer_earned / 100, 2);
    }

    /**
     * Get formatted referred earnings.
     */
    public function getFormattedReferredEarnedAttribute(): string
    {
        return '$'.number_format($this->referred_earned / 100, 2);
    }

    /**
     * Get formatted total earnings.
     */
    public function getFormattedTotalEarningsAttribute(): string
    {
        return '$'.number_format($this->total_earnings / 100, 2);
    }
}
