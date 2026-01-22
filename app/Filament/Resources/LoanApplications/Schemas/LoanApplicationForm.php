<?php

namespace App\Filament\Resources\LoanApplications\Schemas;

use App\Enums\LoanStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LoanApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Loan application')
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
                        Select::make('loan_type_id')
                            ->relationship('loanType', 'name')
                            ->required(),
                        TextInput::make('reference_number')
                            ->required(),
                        TextInput::make('amount')
                            ->required()
                            ->numeric(),
                        TextInput::make('term_months')
                            ->required()
                            ->numeric(),
                        TextInput::make('interest_rate')
                            ->required()
                            ->numeric(),
                        TextInput::make('monthly_payment')
                            ->required()
                            ->numeric(),
                        TextInput::make('total_payable')
                            ->required()
                            ->numeric(),
                        Textarea::make('purpose')
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options(LoanStatus::class)
                            ->default('pending')
                            ->required(),
                        Textarea::make('rejection_reason')
                            ->columnSpanFull(),
                        DateTimePicker::make('approved_at'),
                        TextInput::make('approved_by')
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }
}
