<?php

namespace App\Filament\Resources\CheckDeposits;

use App\Filament\Resources\CheckDeposits\Pages\CreateCheckDeposit;
use App\Filament\Resources\CheckDeposits\Pages\EditCheckDeposit;
use App\Filament\Resources\CheckDeposits\Pages\ListCheckDeposits;
use App\Filament\Resources\CheckDeposits\Schemas\CheckDepositForm;
use App\Filament\Resources\CheckDeposits\Tables\CheckDepositsTable;
use App\Models\CheckDeposit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CheckDepositResource extends Resource
{
    protected static ?string $model = CheckDeposit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Deposits';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CheckDepositForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CheckDepositsTable::configure($table);
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
            'index' => ListCheckDeposits::route('/'),
            'create' => CreateCheckDeposit::route('/create'),
            'edit' => EditCheckDeposit::route('/{record}/edit'),
        ];
    }
}
