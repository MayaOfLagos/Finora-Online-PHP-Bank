<?php

namespace App\Filament\Resources\MobileDeposits\Schemas;

use App\Enums\DepositStatus;
use App\Enums\PaymentGateway;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MobileDepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mobile deposit')
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
                            ->required(),
                        TextInput::make('reference_number')
                            ->required(),
                        Select::make('gateway')
                            ->options(PaymentGateway::class)
                            ->required(),
                        TextInput::make('gateway_transaction_id'),
                        TextInput::make('amount')
                            ->required()
                            ->numeric(),
                        TextInput::make('currency')
                            ->required()
                            ->default('USD'),
                        TextInput::make('fee')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Select::make('status')
                            ->options(DepositStatus::class)
                            ->default('pending')
                            ->required(),
                        Textarea::make('gateway_response')
                            ->columnSpanFull(),
                        DateTimePicker::make('completed_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
