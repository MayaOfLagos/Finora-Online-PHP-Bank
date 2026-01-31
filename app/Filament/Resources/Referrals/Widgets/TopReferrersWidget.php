<?php

namespace App\Filament\Resources\Referrals\Widgets;

use App\Enums\ReferralStatus;
use App\Models\User;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopReferrersWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Referrers';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->withCount(['referrals as completed_referrals_count' => function ($query) {
                        $query->where('status', ReferralStatus::Completed);
                    }])
                    ->having('completed_referrals_count', '>', 0)
                    ->orderBy('completed_referrals_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->badge()
                    ->color(fn ($rowLoop) => match ($rowLoop->index) {
                        0 => 'warning', // Gold
                        1 => 'gray',    // Silver
                        2 => 'danger',  // Bronze
                        default => 'info',
                    }),
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->size(40),
                TextColumn::make('full_name')
                    ->label('User')
                    ->searchable(['first_name', 'last_name'])
                    ->description(fn (User $record) => $record->email),
                TextColumn::make('referral_code')
                    ->label('Referral Code')
                    ->badge()
                    ->color('gray')
                    ->copyable(),
                TextColumn::make('completed_referrals_count')
                    ->label('Referrals')
                    ->badge()
                    ->color('success'),
                TextColumn::make('total_referral_earnings')
                    ->label('Total Earned')
                    ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2))
                    ->badge()
                    ->color('warning'),
            ])
            ->paginated(false)
            ->striped();
    }
}
