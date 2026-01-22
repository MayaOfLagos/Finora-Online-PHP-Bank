<?php

namespace App\Filament\Resources\TaxRefunds;

use App\Filament\Resources\TaxRefunds\Pages\CreateTaxRefund;
use App\Filament\Resources\TaxRefunds\Pages\EditTaxRefund;
use App\Filament\Resources\TaxRefunds\Pages\ListPendingRefunds;
use App\Filament\Resources\TaxRefunds\Pages\ListTaxRefunds;
use App\Filament\Resources\TaxRefunds\Pages\RefundSettings;
use App\Filament\Resources\TaxRefunds\Pages\ViewTaxRefund;
use App\Filament\Resources\TaxRefunds\Schemas\TaxRefundForm;
use App\Filament\Resources\TaxRefunds\Schemas\TaxRefundTable;
use App\Models\TaxRefund;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class TaxRefundResource extends Resource
{
    protected static ?string $model = TaxRefund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Loans & Grants';

    protected static ?string $navigationLabel = 'IRS Refunds';

    protected static ?int $navigationSort = 1;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return TaxRefundForm::configure($schema);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return TaxRefundTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxRefunds::route('/'),
            'pending' => ListPendingRefunds::route('/pending'),
            'settings' => RefundSettings::route('/settings'),
            'create' => CreateTaxRefund::route('/create'),
            'view' => ViewTaxRefund::route('/{record}'),
            'edit' => EditTaxRefund::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
