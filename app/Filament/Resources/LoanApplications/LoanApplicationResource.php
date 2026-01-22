<?php

namespace App\Filament\Resources\LoanApplications;

use App\Filament\Resources\LoanApplications\Pages\CreateLoanApplication;
use App\Filament\Resources\LoanApplications\Pages\EditLoanApplication;
use App\Filament\Resources\LoanApplications\Pages\ListLoanApplications;
use App\Filament\Resources\LoanApplications\Schemas\LoanApplicationForm;
use App\Filament\Resources\LoanApplications\Tables\LoanApplicationsTable;
use App\Models\LoanApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LoanApplicationResource extends Resource
{
    protected static ?string $model = LoanApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Loans & Grants';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return LoanApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoanApplicationsTable::configure($table);
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
            'index' => ListLoanApplications::route('/'),
            'create' => CreateLoanApplication::route('/create'),
            'edit' => EditLoanApplication::route('/{record}/edit'),
        ];
    }
}
