<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\LoanStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Loan details')
                    ->schema([
                        TextInput::make('uuid')
                            ->label('UUID')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('loan_application_id')
                            ->required()
                            ->numeric(),
                        Select::make('user_id')
                            ->relationship('user')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required(),
                        Select::make('bank_account_id')
                            ->relationship('bankAccount', 'account_number')
                            ->required(),
                        TextInput::make('principal_amount')
                            ->required()
                            ->numeric(),
                        TextInput::make('outstanding_balance')
                            ->required()
                            ->numeric(),
                        TextInput::make('interest_rate')
                            ->required()
                            ->numeric(),
                        TextInput::make('monthly_payment')
                            ->required()
                            ->numeric(),
                        DatePicker::make('next_payment_date')
                            ->required(),
                        DatePicker::make('final_payment_date')
                            ->required(),
                        Select::make('status')
                            ->options(LoanStatus::class)
                            ->default('active')
                            ->required(),
                        DateTimePicker::make('disbursed_at')
                            ->required(),
                        DateTimePicker::make('closed_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
