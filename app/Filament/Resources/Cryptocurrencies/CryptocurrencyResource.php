<?php

namespace App\Filament\Resources\Cryptocurrencies;

use App\Filament\Resources\Cryptocurrencies\Pages\CreateCryptocurrency;
use App\Filament\Resources\Cryptocurrencies\Pages\EditCryptocurrency;
use App\Filament\Resources\Cryptocurrencies\Pages\ListCryptocurrencies;
use App\Filament\Resources\Cryptocurrencies\Pages\ViewCryptocurrency;
use App\Filament\Resources\Cryptocurrencies\RelationManagers\WalletsRelationManager;
use App\Filament\Resources\Cryptocurrencies\Schemas\CryptocurrencyForm;
use App\Filament\Resources\Cryptocurrencies\Schemas\CryptocurrencyInfolist;
use App\Filament\Resources\Cryptocurrencies\Tables\CryptocurrenciesTable;
use App\Models\Cryptocurrency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CryptocurrencyResource extends Resource
{
    protected static ?string $model = Cryptocurrency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static ?int $navigationGroupSort = 999;

    protected static ?string $navigationLabel = 'Cryptocurrencies';

    protected static ?string $modelLabel = 'Cryptocurrency';

    protected static ?string $pluralModelLabel = 'Cryptocurrencies';

    public static function form(Schema $schema): Schema
    {
        return CryptocurrencyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CryptocurrencyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CryptocurrenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            WalletsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCryptocurrencies::route('/'),
            'create' => CreateCryptocurrency::route('/create'),
            'view' => ViewCryptocurrency::route('/{record}'),
            'edit' => EditCryptocurrency::route('/{record}/edit'),
        ];
    }
}
