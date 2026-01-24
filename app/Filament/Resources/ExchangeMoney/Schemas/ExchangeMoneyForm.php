<?php

namespace App\Filament\Resources\ExchangeMoney\Schemas;

use App\Models\ExchangeMoney;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExchangeMoneyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Exchange Details')
                    ->description('Currency exchange transaction information')
                    ->icon('heroicon-o-arrows-right-left')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('user_id')
                                ->relationship('user', 'email')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('bank_account_id')
                                ->relationship('bankAccount', 'account_number')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                        TextInput::make('reference_number')
                            ->default(fn () => ExchangeMoney::generateReferenceNumber())
                            ->disabled()
                            ->dehydrated()
                            ->unique(ignoreRecord: true),
                        Grid::make(2)->schema([
                            Select::make('from_currency')
                                ->options([
                                    'USD' => 'USD - US Dollar',
                                    'EUR' => 'EUR - Euro',
                                    'GBP' => 'GBP - British Pound',
                                    'NGN' => 'NGN - Nigerian Naira',
                                    'CAD' => 'CAD - Canadian Dollar',
                                    'AUD' => 'AUD - Australian Dollar',
                                ])
                                ->required()
                                ->native(false),
                            Select::make('to_currency')
                                ->options([
                                    'USD' => 'USD - US Dollar',
                                    'EUR' => 'EUR - Euro',
                                    'GBP' => 'GBP - British Pound',
                                    'NGN' => 'NGN - Nigerian Naira',
                                    'CAD' => 'CAD - Canadian Dollar',
                                    'AUD' => 'AUD - Australian Dollar',
                                ])
                                ->required()
                                ->native(false),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('from_amount')
                                ->label('From Amount (cents)')
                                ->required()
                                ->numeric()
                                ->prefix('¢'),
                            TextInput::make('to_amount')
                                ->label('To Amount (cents)')
                                ->required()
                                ->numeric()
                                ->prefix('¢'),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('exchange_rate')
                                ->required()
                                ->numeric()
                                ->step('0.00000001'),
                            TextInput::make('fee')
                                ->label('Fee (cents)')
                                ->numeric()
                                ->default(0)
                                ->prefix('¢'),
                        ]),
                    ]),
                Section::make('Status & Additional Info')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'completed' => 'Completed',
                                    'failed' => 'Failed',
                                ])
                                ->required()
                                ->default('pending')
                                ->native(false),
                            DateTimePicker::make('completed_at')
                                ->label('Completed At'),
                        ]),
                        TextInput::make('ip_address')
                            ->label('IP Address'),
                        Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
