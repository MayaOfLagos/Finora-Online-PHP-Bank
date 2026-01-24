<?php

namespace App\Filament\Resources\TransactionHistories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TransactionHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Fieldset::make()
                    ->schema([
                        TextInput::make('uuid')
                            ->label('UUID')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->default(fn (): string => strtoupper(Str::random(15)))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required(),
                        Select::make('transaction_type')
                            ->label('Transaction Type')
                            ->options([
                                'deposit' => 'Deposit',
                                'withdrawal' => 'Withdrawal',
                                'transfer' => 'Transfer',
                                'refund' => 'Refund',
                                'adjustment' => 'Adjustment',
                                'credit' => 'Credit',
                                'debit' => 'Debit',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        TextInput::make('currency')
                            ->label('Currency')
                            ->default('USD')
                            ->required()
                            ->maxLength(3),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'reversed' => 'Reversed',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                        Toggle::make('email_sent')
                            ->label('Send Email to User')
                            ->default(false),
                        Toggle::make('wallet_debited')
                            ->label('Debit User Wallet')
                            ->default(false),
                        DateTimePicker::make('processed_at')
                            ->label('Processed At'),
                        DateTimePicker::make('email_sent_at')
                            ->label('Email Sent At'),
                    ])
                    ->columns(2),
            ]);
    }
}
