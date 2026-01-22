<?php

namespace App\Filament\Resources\MoneyRequests;

use App\Filament\Resources\MoneyRequests\Pages\CreateMoneyRequest;
use App\Filament\Resources\MoneyRequests\Pages\EditMoneyRequest;
use App\Filament\Resources\MoneyRequests\Pages\ListMoneyRequests;
use App\Filament\Resources\MoneyRequests\Pages\ViewMoneyRequest;
use App\Filament\Resources\MoneyRequests\Schemas\MoneyRequestForm;
use App\Filament\Resources\MoneyRequests\Schemas\MoneyRequestInfolist;
use App\Filament\Resources\MoneyRequests\Tables\MoneyRequestsTable;
use App\Models\MoneyRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MoneyRequestResource extends Resource
{
    protected static ?string $model = MoneyRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Finance Management';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return MoneyRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MoneyRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MoneyRequestsTable::configure($table);
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
            'index' => ListMoneyRequests::route('/'),
            'create' => CreateMoneyRequest::route('/create'),
            'view' => ViewMoneyRequest::route('/{record}'),
            'edit' => EditMoneyRequest::route('/{record}/edit'),
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
        return 'info';
    }
}
