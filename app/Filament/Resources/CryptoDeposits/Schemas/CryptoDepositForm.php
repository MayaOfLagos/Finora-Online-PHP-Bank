<?php

namespace App\Filament\Resources\CryptoDeposits\Schemas;

use App\Enums\DepositStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CryptoDepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Crypto deposit')
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
                        Select::make('cryptocurrency_id')
                            ->relationship('cryptocurrency', 'name')
                            ->required(),
                        Select::make('crypto_wallet_id')
                            ->relationship('cryptoWallet', 'wallet_address')
                            ->searchable()
                            ->required(),
                        TextInput::make('reference_number')
                            ->required(),
                        TextInput::make('crypto_amount')
                            ->required()
                            ->numeric(),
                        TextInput::make('usd_amount')
                            ->required()
                            ->numeric(),
                        TextInput::make('transaction_hash'),
                        Select::make('status')
                            ->options(DepositStatus::class)
                            ->default('pending')
                            ->required(),
                        DateTimePicker::make('verified_at'),
                        TextInput::make('verified_by')
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }
}
