<?php

namespace App\Filament\Resources\TaxRefunds\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TaxRefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('IRS Tax Refund')
                    ->schema([
                        TextInput::make('uuid')
                            ->label('UUID')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required(),
                        Select::make('bank_account_id')
                            ->relationship('bankAccount', 'account_number')
                            ->searchable()
                            ->required(),
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->default(fn (): string => 'TAXREF-'.strtoupper(Str::random(10)))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('tax_year')
                            ->label('Tax Year')
                            ->placeholder('e.g., 2023')
                            ->required()
                            ->maxLength(4),
                        TextInput::make('ssn_tin')
                            ->label('SSN/TIN')
                            ->placeholder('Last 4 digits')
                            ->maxLength(50),
                        Select::make('filing_status')
                            ->label('Filing Status')
                            ->options([
                                'single' => 'Single',
                                'married_filing_jointly' => 'Married Filing Jointly',
                                'married_filing_separately' => 'Married Filing Separately',
                                'head_of_household' => 'Head of Household',
                                'qualifying_widow' => 'Qualifying Widow(er)',
                            ]),
                        TextInput::make('refund_amount')
                            ->label('Refund Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        TextInput::make('currency')
                            ->label('Currency')
                            ->default('USD')
                            ->required()
                            ->maxLength(3),
                        TextInput::make('irs_reference_number')
                            ->label('IRS Reference Number')
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->columnSpanFull()
                            ->rows(3),
                        DateTimePicker::make('submitted_at')
                            ->label('Submitted At'),
                        DateTimePicker::make('processed_at')
                            ->label('Processed At'),
                        DateTimePicker::make('approved_at')
                            ->label('Approved At'),
                        DateTimePicker::make('completed_at')
                            ->label('Completed At'),
                    ])
                    ->columns(2),
            ]);
    }
}
