<?php

namespace App\Filament\Resources\ReferralLevels;

use App\Filament\Resources\ReferralLevels\Pages\ListReferralLevels;
use App\Filament\Resources\ReferralLevels\Schemas\ReferralLevelForm;
use App\Filament\Resources\ReferralLevels\Tables\ReferralLevelsTable;
use App\Models\ReferralLevel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReferralLevelResource extends Resource
{
    protected static ?string $model = ReferralLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Referrals';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Referral Levels';

    protected static ?string $modelLabel = 'Referral Level';

    protected static ?string $pluralModelLabel = 'Referral Levels';

    public static function form(Schema $schema): Schema
    {
        return ReferralLevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralLevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferralLevels::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
