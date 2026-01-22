<?php

namespace App\Filament\Resources\DomesticTransfers;

use App\Filament\Resources\DomesticTransfers\Pages\CreateDomesticTransfer;
use App\Filament\Resources\DomesticTransfers\Pages\EditDomesticTransfer;
use App\Filament\Resources\DomesticTransfers\Pages\ListDomesticTransfers;
use App\Filament\Resources\DomesticTransfers\Schemas\DomesticTransferForm;
use App\Filament\Resources\DomesticTransfers\Tables\DomesticTransfersTable;
use App\Models\DomesticTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DomesticTransferResource extends Resource
{
    protected static ?string $model = DomesticTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Transfers';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DomesticTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DomesticTransfersTable::configure($table);
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
            'index' => ListDomesticTransfers::route('/'),
            'create' => CreateDomesticTransfer::route('/create'),
            'edit' => EditDomesticTransfer::route('/{record}/edit'),
        ];
    }
}
