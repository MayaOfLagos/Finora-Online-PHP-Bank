<?php

namespace App\Filament\Resources\GrantCategories;

use App\Filament\Resources\GrantCategories\Pages\CreateGrantCategory;
use App\Filament\Resources\GrantCategories\Pages\EditGrantCategory;
use App\Filament\Resources\GrantCategories\Pages\ListGrantCategories;
use App\Filament\Resources\GrantCategories\Schemas\GrantCategoryForm;
use App\Filament\Resources\GrantCategories\Tables\GrantCategoriesTable;
use App\Models\GrantCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GrantCategoryResource extends Resource
{
    protected static ?string $model = GrantCategory::class;

    protected static ?string $slug = 'grant-categories';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Loans & Grants';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return GrantCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GrantCategoriesTable::configure($table);
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
            'index' => ListGrantCategories::route('/'),
            'create' => CreateGrantCategory::route('/create'),
            'edit' => EditGrantCategory::route('/{record}/edit'),
        ];
    }
}
