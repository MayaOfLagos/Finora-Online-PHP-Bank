<?php

namespace App\Filament\Resources\Rewards;

use App\Filament\Resources\Rewards\Pages\CreateReward;
use App\Filament\Resources\Rewards\Pages\EditReward;
use App\Filament\Resources\Rewards\Pages\ListRewards;
use App\Filament\Resources\Rewards\Pages\ViewReward;
use App\Filament\Resources\Rewards\Schemas\RewardForm;
use App\Filament\Resources\Rewards\Schemas\RewardInfolist;
use App\Filament\Resources\Rewards\Tables\RewardsTable;
use App\Models\Reward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Finance Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RewardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RewardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RewardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRewards::route('/'),
            'create' => CreateReward::route('/create'),
            'view' => ViewReward::route('/{record}'),
            'edit' => EditReward::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
