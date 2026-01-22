<?php

namespace App\Filament\Resources\WireTransfers;

use App\Filament\Resources\WireTransfers\Pages\CreateWireTransfer;
use App\Filament\Resources\WireTransfers\Pages\EditWireTransfer;
use App\Filament\Resources\WireTransfers\Pages\ListWireTransfers;
use App\Filament\Resources\WireTransfers\Schemas\WireTransferForm;
use App\Filament\Resources\WireTransfers\Tables\WireTransfersTable;
use App\Models\WireTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WireTransferResource extends Resource
{
    protected static ?string $model = WireTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'Transfers';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return WireTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WireTransfersTable::configure($table);
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
            'index' => ListWireTransfers::route('/'),
            'create' => CreateWireTransfer::route('/create'),
            'edit' => EditWireTransfer::route('/{record}/edit'),
        ];
    }
}
