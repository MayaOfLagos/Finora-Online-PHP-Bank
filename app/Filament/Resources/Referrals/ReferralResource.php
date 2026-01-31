<?php

namespace App\Filament\Resources\Referrals;

use App\Filament\Resources\Referrals\Pages\ListReferrals;
use App\Filament\Resources\Referrals\Tables\ReferralsTable;
use App\Models\Referral;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Referrals';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'All Referrals';

    protected static ?string $modelLabel = 'Referral';

    protected static ?string $pluralModelLabel = 'Referrals';

    public static function table(Table $table): Table
    {
        return ReferralsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferrals::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereMonth('created_at', now()->month)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function canCreate(): bool
    {
        return false; // Referrals are created automatically
    }
}
