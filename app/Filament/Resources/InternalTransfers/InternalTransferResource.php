<?php

namespace App\Filament\Resources\InternalTransfers;

use App\Filament\Resources\InternalTransfers\Pages\CreateInternalTransfer;
use App\Filament\Resources\InternalTransfers\Pages\EditInternalTransfer;
use App\Filament\Resources\InternalTransfers\Pages\ListInternalTransfers;
use App\Filament\Resources\InternalTransfers\Schemas\InternalTransferForm;
use App\Filament\Resources\InternalTransfers\Tables\InternalTransfersTable;
use App\Models\InternalTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class InternalTransferResource extends Resource
{
    protected static ?string $model = InternalTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Transfers';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return InternalTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InternalTransfersTable::configure($table);
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
            'index' => ListInternalTransfers::route('/'),
            'create' => CreateInternalTransfer::route('/create'),
            'edit' => EditInternalTransfer::route('/{record}/edit'),
        ];
    }
}
