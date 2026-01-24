<?php

namespace App\Filament\Resources\GrantApplications;

use App\Filament\Resources\GrantApplications\Pages\CreateGrantApplication;
use App\Filament\Resources\GrantApplications\Pages\EditGrantApplication;
use App\Filament\Resources\GrantApplications\Pages\ListGrantApplications;
use App\Filament\Resources\GrantApplications\Schemas\GrantApplicationForm;
use App\Filament\Resources\GrantApplications\Tables\GrantApplicationsTable;
use App\Models\GrantApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GrantApplicationResource extends Resource
{
    protected static ?string $model = GrantApplication::class;

    protected static ?string $slug = 'grant-applications';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = 'Loans & Grants';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return GrantApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GrantApplicationsTable::configure($table);
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
            'index' => ListGrantApplications::route('/'),
            'create' => CreateGrantApplication::route('/create'),
            'edit' => EditGrantApplication::route('/{record}/edit'),
        ];
    }
}
