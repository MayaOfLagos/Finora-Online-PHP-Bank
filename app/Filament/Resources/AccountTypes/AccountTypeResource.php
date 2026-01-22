<?php

namespace App\Filament\Resources\AccountTypes;

use App\Filament\Resources\AccountTypes\Pages\ListAccountTypes;
use App\Filament\Resources\AccountTypes\Schemas\AccountTypeForm;
use App\Filament\Resources\AccountTypes\Schemas\AccountTypeTable;
use App\Models\AccountType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AccountTypeResource extends Resource
{
    protected static ?string $model = AccountType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AccountTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountTypeTable::configure($table);
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
            'index' => ListAccountTypes::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
