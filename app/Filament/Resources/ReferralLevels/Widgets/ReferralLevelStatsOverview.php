<?php

namespace App\Filament\Resources\ReferralLevels\Widgets;

use App\Models\Referral;
use App\Models\ReferralLevel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReferralLevelStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLevels = ReferralLevel::count();
        $activeLevels = ReferralLevel::active()->count();
        $totalReferrals = Referral::count();
        $totalEarnings = Referral::sum('referrer_earned') + Referral::sum('referred_earned');

        return [
            Stat::make('Total Levels', $totalLevels)
                ->description($activeLevels.' active')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('primary')
                ->chart([2, 3, 5, 4, 6, $totalLevels]),

            Stat::make('Total Referrals', number_format($totalReferrals))
                ->description('All time')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart([10, 15, 20, 25, 30, $totalReferrals]),

            Stat::make('Total Payouts', '$'.number_format($totalEarnings / 100, 2))
                ->description('Rewards distributed')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning')
                ->chart([100, 200, 300, 400, 500, $totalEarnings / 100]),

            Stat::make('Avg. Reward', ReferralLevel::active()->count() > 0
                ? '$'.number_format(ReferralLevel::active()->where('reward_type', 'fixed')->avg('reward_amount') / 100, 2)
                : '$0.00')
                ->description('Per referral (fixed)')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('info'),
        ];
    }
}
