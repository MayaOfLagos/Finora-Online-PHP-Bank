<?php

namespace App\Filament\Resources\LoanPayments;

use App\Filament\Resources\LoanPayments\Pages\CreateLoanPayment;
use App\Filament\Resources\LoanPayments\Pages\EditLoanPayment;
use App\Filament\Resources\LoanPayments\Pages\ListLoanPayments;
use App\Filament\Resources\LoanPayments\Schemas\LoanPaymentForm;
use App\Filament\Resources\LoanPayments\Tables\LoanPaymentsTable;
use App\Models\LoanPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LoanPaymentResource extends Resource
{
    protected static ?string $model = LoanPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Loan Repayments';

    protected static string|UnitEnum|null $navigationGroup = 'Loans & Grants';

    public static function form(Schema $schema): Schema
    {
        return LoanPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoanPaymentsTable::configure($table);
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
            'index' => ListLoanPayments::route('/'),
            'create' => CreateLoanPayment::route('/create'),
            'edit' => EditLoanPayment::route('/{record}/edit'),
        ];
    }
}
