<?php

namespace App\Filament\Resources\Referrals\Pages;

use App\Filament\Resources\Referrals\ReferralResource;
use App\Filament\Resources\Referrals\Widgets\ReferralStatsOverview;
use App\Filament\Resources\Referrals\Widgets\TopReferrersWidget;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListReferrals extends ListRecords
{
    protected static string $resource = ReferralResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Referral Management';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Track all referrals, rewards, and top performers';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReferralStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TopReferrersWidget::class,
        ];
    }
}
