<?php

namespace App\Filament\Resources\Referrals\Widgets;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReferralStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalReferrals = Referral::count();
        $completedReferrals = Referral::where('status', ReferralStatus::Completed)->count();
        $pendingReferrals = Referral::where('status', ReferralStatus::Pending)->count();
        $thisMonthReferrals = Referral::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalReferrerEarnings = Referral::sum('referrer_earned');
        $totalReferredEarnings = Referral::sum('referred_earned');
        $totalPayouts = $totalReferrerEarnings + $totalReferredEarnings;

        $thisMonthEarnings = Referral::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('referrer_earned');

        // Calculate growth
        $lastMonthReferrals = Referral::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $growth = $lastMonthReferrals > 0
            ? round((($thisMonthReferrals - $lastMonthReferrals) / $lastMonthReferrals) * 100, 1)
            : 100;

        return [
            Stat::make('Total Referrals', number_format($totalReferrals))
                ->description($completedReferrals.' completed')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('primary')
                ->chart($this->getChartData('referrals')),

            Stat::make('This Month', number_format($thisMonthReferrals))
                ->description($growth >= 0 ? "+{$growth}% from last month" : "{$growth}% from last month")
                ->descriptionIcon($growth >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger')
                ->chart($this->getChartData('monthly')),

            Stat::make('Total Payouts', '$'.number_format($totalPayouts / 100, 2))
                ->description('$'.number_format($thisMonthEarnings / 100, 2).' this month')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning')
                ->chart($this->getChartData('earnings')),

            Stat::make('Pending', number_format($pendingReferrals))
                ->description('Awaiting completion')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray')
                ->chart([0, 0, 0, 0, 0, $pendingReferrals]),
        ];
    }

    protected function getChartData(string $type): array
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $query = Referral::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year);

            $data[] = match ($type) {
                'referrals' => (clone $query)->count(),
                'monthly' => (clone $query)->count(),
                'earnings' => (clone $query)->sum('referrer_earned') / 100,
                default => 0,
            };
        }

        return $data;
    }
}
