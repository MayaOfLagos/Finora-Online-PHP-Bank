<?php

namespace App\Filament\Resources\CryptoDeposits;

use App\Filament\Resources\CryptoDeposits\Pages\CreateCryptoDeposit;
use App\Filament\Resources\CryptoDeposits\Pages\EditCryptoDeposit;
use App\Filament\Resources\CryptoDeposits\Pages\ListCryptoDeposits;
use App\Filament\Resources\CryptoDeposits\Schemas\CryptoDepositForm;
use App\Filament\Resources\CryptoDeposits\Tables\CryptoDepositsTable;
use App\Models\CryptoDeposit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CryptoDepositResource extends Resource
{
    protected static ?string $model = CryptoDeposit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Deposits';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CryptoDepositForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CryptoDepositsTable::configure($table);
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
            'index' => ListCryptoDeposits::route('/'),
            'create' => CreateCryptoDeposit::route('/create'),
            'edit' => EditCryptoDeposit::route('/{record}/edit'),
        ];
    }
}
