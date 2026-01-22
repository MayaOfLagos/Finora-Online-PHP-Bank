<?php

namespace App\Filament\Resources\ExchangeMoney;

use App\Filament\Resources\ExchangeMoney\Pages\CreateExchangeMoney;
use App\Filament\Resources\ExchangeMoney\Pages\EditExchangeMoney;
use App\Filament\Resources\ExchangeMoney\Pages\ListExchangeMoney;
use App\Filament\Resources\ExchangeMoney\Pages\ViewExchangeMoney;
use App\Filament\Resources\ExchangeMoney\Schemas\ExchangeMoneyForm;
use App\Filament\Resources\ExchangeMoney\Schemas\ExchangeMoneyInfolist;
use App\Filament\Resources\ExchangeMoney\Tables\ExchangeMoneyTable;
use App\Models\ExchangeMoney;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ExchangeMoneyResource extends Resource
{
    protected static ?string $model = ExchangeMoney::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Finance Management';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Currency Exchange';

    protected static ?string $modelLabel = 'Currency Exchange';

    protected static ?string $pluralModelLabel = 'Currency Exchanges';

    public static function form(Schema $schema): Schema
    {
        return ExchangeMoneyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExchangeMoneyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExchangeMoneyTable::configure($table);
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
            'index' => ListExchangeMoney::route('/'),
            'create' => CreateExchangeMoney::route('/create'),
            'view' => ViewExchangeMoney::route('/{record}'),
            'edit' => EditExchangeMoney::route('/{record}/edit'),
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
