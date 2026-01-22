<?php

namespace App\Filament\Resources\MobileDeposits;

use App\Filament\Resources\MobileDeposits\Pages\CreateMobileDeposit;
use App\Filament\Resources\MobileDeposits\Pages\EditMobileDeposit;
use App\Filament\Resources\MobileDeposits\Pages\ListMobileDeposits;
use App\Filament\Resources\MobileDeposits\Schemas\MobileDepositForm;
use App\Filament\Resources\MobileDeposits\Tables\MobileDepositsTable;
use App\Models\MobileDeposit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MobileDepositResource extends Resource
{
    protected static ?string $model = MobileDeposit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static string|UnitEnum|null $navigationGroup = 'Deposits';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return MobileDepositForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MobileDepositsTable::configure($table);
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
            'index' => ListMobileDeposits::route('/'),
            'create' => CreateMobileDeposit::route('/create'),
            'edit' => EditMobileDeposit::route('/{record}/edit'),
        ];
    }
}
