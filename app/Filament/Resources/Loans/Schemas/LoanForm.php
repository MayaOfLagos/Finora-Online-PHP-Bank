<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\LoanStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Loan Information')
                    ->description('Core loan details and borrower information')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('uuid')
                                    ->label('UUID')
                                    ->default(fn (): string => (string) Str::uuid())
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->helperText('Unique identifier for this loan'),

                                Select::make('loan_application_id')
                                    ->label('Loan Application')
                                    ->relationship('loanApplication', 'reference_number')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Associated loan application'),

                                Select::make('user_id')
                                    ->label('Borrower')
                                    ->relationship('user')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->email.')')
                                    ->searchable(['first_name', 'last_name', 'email'])
                                    ->preload()
                                    ->required()
                                    ->helperText('The customer who owns this loan'),

                                Select::make('bank_account_id')
                                    ->label('Disbursement Account')
                                    ->relationship('bankAccount', 'account_number')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Account where loan was/will be disbursed'),
                            ]),
                    ]),

                Section::make('Financial Details')
                    ->description('Loan amounts, interest rate, and payment information')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('principal_amount')
                                    ->label('Principal Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->helperText('Original loan amount'),

                                TextInput::make('outstanding_balance')
                                    ->label('Outstanding Balance')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->helperText('Current remaining balance'),

                                TextInput::make('interest_rate')
                                    ->label('Interest Rate')
                                    ->required()
                                    ->numeric()
                                    ->suffix('% p.a.')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->helperText('Annual interest rate'),

                                TextInput::make('monthly_payment')
                                    ->label('Monthly Payment')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->helperText('Monthly installment amount'),
                            ]),
                    ]),

                Section::make('Payment Schedule')
                    ->description('Payment dates and loan timeline')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('next_payment_date')
                                    ->label('Next Payment Due')
                                    ->required()
                                    ->displayFormat('M d, Y')
                                    ->helperText('Date of next scheduled payment'),

                                DatePicker::make('final_payment_date')
                                    ->label('Final Payment Date')
                                    ->required()
                                    ->displayFormat('M d, Y')
                                    ->helperText('Expected loan completion date'),
                            ]),
                    ]),

                Section::make('Status & Dates')
                    ->description('Loan status and important dates')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Loan Status')
                                    ->options(LoanStatus::class)
                                    ->default(LoanStatus::Active)
                                    ->required()
                                    ->helperText('Current status of the loan'),

                                DateTimePicker::make('disbursed_at')
                                    ->label('Disbursed At')
                                    ->displayFormat('M d, Y H:i')
                                    ->helperText('When the loan was disbursed'),

                                DateTimePicker::make('closed_at')
                                    ->label('Closed At')
                                    ->displayFormat('M d, Y H:i')
                                    ->helperText('When the loan was closed (if applicable)'),
                            ]),
                    ]),
            ]);
    }
}
