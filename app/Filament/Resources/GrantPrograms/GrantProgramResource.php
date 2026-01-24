<?php

namespace App\Filament\Resources\GrantPrograms;

use App\Filament\Resources\GrantPrograms\Pages\CreateGrantProgram;
use App\Filament\Resources\GrantPrograms\Pages\EditGrantProgram;
use App\Filament\Resources\GrantPrograms\Pages\ListGrantPrograms;
use App\Filament\Resources\GrantPrograms\Schemas\GrantProgramForm;
use App\Filament\Resources\GrantPrograms\Tables\GrantProgramsTable;
use App\Models\GrantProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GrantProgramResource extends Resource
{
    protected static ?string $model = GrantProgram::class;

    protected static ?string $slug = 'grant-programs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Loans & Grants';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return GrantProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GrantProgramsTable::configure($table);
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
            'index' => ListGrantPrograms::route('/'),
            'create' => CreateGrantProgram::route('/create'),
            'edit' => EditGrantProgram::route('/{record}/edit'),
        ];
    }
}
