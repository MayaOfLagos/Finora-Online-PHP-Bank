<?php

namespace App\Filament\Resources\LoanApplications\Schemas;

use App\Enums\LoanStatus;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LoanApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Application Details')
                    ->description('Basic information about the loan application')
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
                                    ->helperText('Unique identifier for this application'),

                                TextInput::make('reference_number')
                                    ->label('Reference Number')
                                    ->required()
                                    ->default(fn (): string => 'LA-'.strtoupper(Str::random(8)))
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Auto-generated reference number'),

                                Select::make('user_id')
                                    ->label('Applicant')
                                    ->relationship('user')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->email.')')
                                    ->searchable(['first_name', 'last_name', 'email'])
                                    ->preload()
                                    ->required()
                                    ->helperText('Select the customer applying for the loan'),

                                Select::make('loan_type_id')
                                    ->label('Loan Type')
                                    ->relationship('loanType', 'name')
                                    ->preload()
                                    ->required()
                                    ->helperText('Type of loan being applied for'),
                            ]),
                    ]),

                Section::make('Financial Terms')
                    ->description('Loan amount, interest rate, and payment details')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Loan Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculatePayments($set, $get))
                                    ->helperText('Total amount requested'),

                                TextInput::make('term_months')
                                    ->label('Loan Term')
                                    ->required()
                                    ->numeric()
                                    ->suffix('months')
                                    ->minValue(1)
                                    ->maxValue(360)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculatePayments($set, $get))
                                    ->helperText('Duration of the loan'),

                                TextInput::make('interest_rate')
                                    ->label('Interest Rate')
                                    ->required()
                                    ->numeric()
                                    ->suffix('% p.a.')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculatePayments($set, $get))
                                    ->helperText('Annual interest rate'),

                                TextInput::make('monthly_payment')
                                    ->label('Monthly Payment')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Calculated monthly installment'),

                                TextInput::make('total_payable')
                                    ->label('Total Payable')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Total amount to be repaid'),
                            ]),
                    ]),

                Section::make('Loan Purpose')
                    ->description('Details about the intended use of the loan')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Textarea::make('purpose')
                            ->label('Purpose of Loan')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Describe the purpose for which this loan will be used...')
                            ->helperText('Provide a detailed description of how the loan will be utilized'),
                    ]),

                Section::make('Status & Approval')
                    ->description('Application status and approval information')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Application Status')
                                    ->options(LoanStatus::class)
                                    ->default(LoanStatus::Pending)
                                    ->required()
                                    ->helperText('Current status of the application'),

                                DateTimePicker::make('approved_at')
                                    ->label('Approved At')
                                    ->displayFormat('M d, Y H:i')
                                    ->helperText('Date and time of approval'),

                                Select::make('approved_by')
                                    ->label('Approved By')
                                    ->options(fn () => User::query()->pluck('email', 'id'))
                                    ->searchable()
                                    ->helperText('Admin who approved the application'),
                            ]),

                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Reason for rejection (if applicable)...')
                            ->helperText('Provide a detailed reason if the application is rejected')
                            ->visible(fn ($get) => $get('status') === LoanStatus::Rejected->value || $get('status') === LoanStatus::Rejected),
                    ]),
            ]);
    }

    /**
     * Calculate monthly payment and total payable based on loan amount, term, and interest rate.
     * Uses the standard amortization formula for calculating monthly payments.
     */
    protected static function calculatePayments(callable $set, callable $get): void
    {
        $amount = (float) ($get('amount') ?? 0);
        $termMonths = (int) ($get('term_months') ?? 0);
        $annualInterestRate = (float) ($get('interest_rate') ?? 0);

        if ($amount <= 0 || $termMonths <= 0) {
            $set('monthly_payment', null);
            $set('total_payable', null);

            return;
        }

        if ($annualInterestRate <= 0) {
            // Simple division if no interest (0% APR)
            $monthlyPayment = $amount / $termMonths;
            $totalPayable = $amount;
        } else {
            // Standard amortization formula: M = P * [r(1+r)^n] / [(1+r)^n - 1]
            // Where: M = monthly payment, P = principal, r = monthly interest rate, n = number of payments
            $monthlyInterestRate = ($annualInterestRate / 100) / 12;
            $compoundFactor = pow(1 + $monthlyInterestRate, $termMonths);

            $monthlyPayment = $amount * ($monthlyInterestRate * $compoundFactor) / ($compoundFactor - 1);
            $totalPayable = $monthlyPayment * $termMonths;
        }

        // Round to 2 decimal places for currency
        $set('monthly_payment', round($monthlyPayment, 2));
        $set('total_payable', round($totalPayable, 2));
    }
}
