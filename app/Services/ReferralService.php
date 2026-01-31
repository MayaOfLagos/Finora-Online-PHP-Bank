<?php

namespace App\Services;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\ReferralLevel;
use App\Models\ReferralSetting;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Check if referral program is enabled.
     */
    public function isEnabled(): bool
    {
        return ReferralSetting::isEnabled('referral_enabled');
    }

    /**
     * Check if new user bonus is enabled.
     */
    public function isNewUserBonusEnabled(): bool
    {
        return ReferralSetting::isEnabled('new_user_bonus_enabled');
    }

    /**
     * Get new user bonus amount in cents.
     */
    public function getNewUserBonusAmount(): int
    {
        return (int) ReferralSetting::get('new_user_bonus_amount', 500);
    }

    /**
     * Validate a referral code.
     */
    public function validateReferralCode(string $code): ?User
    {
        if (! $this->isEnabled()) {
            return null;
        }

        return User::where('referral_code', strtoupper(trim($code)))
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get referrer info for display (modal).
     */
    public function getReferrerInfo(string $code): ?array
    {
        $referrer = $this->validateReferralCode($code);

        if (! $referrer) {
            return null;
        }

        $level = $referrer->currentReferralLevel() ?? ReferralLevel::getStartingLevel();
        $showBonus = $this->isNewUserBonusEnabled();
        $bonusAmount = $showBonus ? $this->getNewUserBonusAmount() : 0;

        return [
            'name' => $referrer->first_name,
            'full_name' => $referrer->full_name,
            'avatar_url' => $referrer->avatar_url,
            'show_bonus' => $showBonus,
            'bonus_amount' => $bonusAmount,
            'bonus_formatted' => '$'.number_format($bonusAmount / 100, 2),
        ];
    }

    /**
     * Process a referral after successful registration.
     */
    public function processReferral(User $newUser, string $referralCode): ?Referral
    {
        if (! $this->isEnabled()) {
            Log::info('Referral program disabled, skipping referral processing', ['user_id' => $newUser->id]);

            return null;
        }

        $referrer = $this->validateReferralCode($referralCode);

        if (! $referrer) {
            Log::warning('Invalid referral code during registration', [
                'user_id' => $newUser->id,
                'code' => $referralCode,
            ]);

            return null;
        }

        // Prevent self-referral
        if ($referrer->id === $newUser->id) {
            Log::warning('Self-referral attempted', ['user_id' => $newUser->id]);

            return null;
        }

        // Check if already referred
        if (Referral::where('referred_id', $newUser->id)->exists()) {
            Log::info('User already has a referral record', ['user_id' => $newUser->id]);

            return null;
        }

        return DB::transaction(function () use ($newUser, $referrer, $referralCode) {
            // Get referrer's current level
            $level = $referrer->currentReferralLevel() ?? ReferralLevel::getStartingLevel();

            // Calculate rewards
            $referrerEarned = $level ? $level->calculateReward() : 0;
            $referredEarned = 0;

            // Create referral record
            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $newUser->id,
                'referral_code_used' => strtoupper($referralCode),
                'referrer_level_id' => $level?->id,
                'status' => ReferralStatus::Completed,
                'referrer_earned' => $referrerEarned,
                'referred_earned' => 0,
                'completed_at' => now(),
            ]);

            // Always create reward for referrer
            $referrerReward = null;
            if ($referrerEarned > 0) {
                $referrerReward = $this->createReward(
                    user: $referrer,
                    type: 'referral_inviter',
                    amount: $referrerEarned,
                    description: "Referral bonus for inviting {$newUser->first_name}",
                    referralId: $referral->id,
                    levelId: $level?->id
                );

                // Update referrer's total earnings
                $referrer->increment('total_referral_earnings', $referrerEarned);
            }

            // Create reward for new user ONLY if enabled
            $referredReward = null;
            if ($this->isNewUserBonusEnabled()) {
                $bonusAmount = $this->getNewUserBonusAmount();
                if ($bonusAmount > 0) {
                    $referredReward = $this->createReward(
                        user: $newUser,
                        type: 'referral_signup',
                        amount: $bonusAmount,
                        description: "Welcome bonus for joining via referral from {$referrer->first_name}",
                        referralId: $referral->id
                    );

                    $referral->update([
                        'referred_earned' => $bonusAmount,
                        'referred_reward_id' => $referredReward->id,
                    ]);

                    $referredEarned = $bonusAmount;
                }
            }

            // Update referral with reward IDs
            $referral->update([
                'referrer_reward_id' => $referrerReward?->id,
            ]);

            // Update new user's referred_by field
            $newUser->update([
                'referred_by' => $referrer->id,
                'referred_at' => now(),
            ]);

            // Log the activity
            ActivityLogger::logAccount(
                $newUser,
                'referral_completed',
                "Joined via referral from {$referrer->full_name}",
                ['referral_id' => $referral->id, 'referrer_id' => $referrer->id]
            );

            ActivityLogger::logAccount(
                $referrer,
                'referral_earned',
                "Earned referral bonus for inviting {$newUser->full_name}",
                [
                    'referral_id' => $referral->id,
                    'referred_user_id' => $newUser->id,
                    'amount' => $referrerEarned,
                ]
            );

            Log::info('Referral processed successfully', [
                'referral_id' => $referral->id,
                'referrer_id' => $referrer->id,
                'referred_id' => $newUser->id,
                'referrer_earned' => $referrerEarned,
                'referred_earned' => $referredEarned,
            ]);

            return $referral;
        });
    }

    /**
     * Create a reward record.
     */
    private function createReward(
        User $user,
        string $type,
        int $amount,
        string $description,
        int $referralId,
        ?int $levelId = null
    ): Reward {
        return Reward::create([
            'user_id' => $user->id,
            'title' => $type === 'referral_inviter' ? 'Referral Bonus' : 'Welcome Bonus',
            'description' => $description,
            'points' => (int) ($amount / 10), // Convert cents to points (10 cents = 1 point)
            'type' => 'referral',
            'status' => 'earned',
            'earned_date' => now(),
            'source' => 'Referral Program',
            'metadata' => [
                'referral_id' => $referralId,
                'reward_type' => $type,
                'amount_cents' => $amount,
                'level_id' => $levelId,
            ],
        ]);
    }

    /**
     * Get referral statistics for a user.
     */
    public function getUserStats(User $user): array
    {
        $referrals = $user->referrals();

        return [
            'total_referrals' => $referrals->count(),
            'completed_referrals' => $referrals->where('status', ReferralStatus::Completed)->count(),
            'pending_referrals' => $referrals->where('status', ReferralStatus::Pending)->count(),
            'total_earned' => $user->total_referral_earnings,
            'total_earned_formatted' => '$'.number_format($user->total_referral_earnings / 100, 2),
            'current_level' => $user->currentReferralLevel(),
            'referral_code' => $user->referral_code ?? $user->generateReferralCode(),
        ];
    }

    /**
     * Get global referral statistics (for admin).
     */
    public function getGlobalStats(): array
    {
        return [
            'total_referrals' => Referral::count(),
            'completed_referrals' => Referral::where('status', ReferralStatus::Completed)->count(),
            'pending_referrals' => Referral::where('status', ReferralStatus::Pending)->count(),
            'total_referrer_earnings' => Referral::sum('referrer_earned'),
            'total_referred_earnings' => Referral::sum('referred_earned'),
            'total_earnings' => Referral::sum('referrer_earned') + Referral::sum('referred_earned'),
            'this_month_referrals' => Referral::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'this_month_earnings' => Referral::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('referrer_earned'),
            'top_referrers' => User::withCount(['referrals as completed_referrals' => function ($query) {
                $query->where('status', ReferralStatus::Completed);
            }])
                ->having('completed_referrals', '>', 0)
                ->orderBy('completed_referrals', 'desc')
                ->limit(5)
                ->get(),
        ];
    }
}
