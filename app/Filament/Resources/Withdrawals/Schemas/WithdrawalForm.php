<?php

namespace App\Filament\Resources\Withdrawals\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WithdrawalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Withdrawal Details')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('user_id')
                                ->relationship('user', 'email')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('User'),
                            Select::make('bank_account_id')
                                ->relationship('bankAccount', 'account_number')
                                ->searchable()
                                ->preload()
                                ->label('Bank Account'),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('reference_number')
                                ->required()
                                ->disabled()
                                ->dehydrated()
                                ->default(fn () => 'WTH'.date('Ymd').strtoupper(substr(uniqid(), -6))),
                            TextInput::make('amount')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->label('Amount (cents)')
                                ->helperText('Enter amount in cents (e.g., 10000 = $100)'),
                            Select::make('currency')
                                ->options([
                                    'USD' => 'USD',
                                    'EUR' => 'EUR',
                                    'GBP' => 'GBP',
                                    'NGN' => 'NGN',
                                ])
                                ->default('USD')
                                ->required(),
                        ]),
                        Textarea::make('reason')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status & Approval')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'approved' => 'Approved',
                                    'completed' => 'Completed',
                                    'rejected' => 'Rejected',
                                ])
                                ->default('pending')
                                ->required(),
                            TextInput::make('ip_address')
                                ->disabled(),
                        ]),
                        Grid::make(2)->schema([
                            DateTimePicker::make('approved_at')
                                ->label('Approved At'),
                            DateTimePicker::make('completed_at')
                                ->label('Completed At'),
                        ]),
                        Textarea::make('rejection_reason')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                    ]),

                Section::make('Bank Details')
                    ->schema([
                        Textarea::make('bank_details')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('JSON format: bank name, account number, routing number, etc.'),
                    ])
                    ->collapsed(),
            ]);
    }
}
