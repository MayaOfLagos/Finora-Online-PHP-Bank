<?php

namespace App\Filament\Resources\Cryptocurrencies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CryptocurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Cryptocurrency Name')
                                ->required()
                                ->placeholder('e.g., Bitcoin, Ethereum')
                                ->maxLength(255),

                            TextInput::make('symbol')
                                ->label('Symbol')
                                ->required()
                                ->placeholder('e.g., BTC, ETH')
                                ->maxLength(10),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('network')
                                ->label('Network/Chain')
                                ->required()
                                ->placeholder('e.g., Bitcoin, Ethereum, TRC20')
                                ->maxLength(100),

                            FileUpload::make('icon')
                                ->label('Crypto Icon')
                                ->image()
                                ->directory('cryptocurrencies')
                                ->imageEditor()
                                ->nullable()
                                ->helperText('Upload an icon for this cryptocurrency'),
                        ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable()
                            ->placeholder('Optional description about this cryptocurrency'),
                    ]),

                Section::make('Exchange Rate Configuration')
                    ->description('Set exchange rates for USD conversion. Live rates will be fetched if CoinGecko ID is provided.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('exchange_rate_usd')
                                ->label('Manual Exchange Rate (USD)')
                                ->numeric()
                                ->inputMode('decimal')
                                ->placeholder('e.g., 45000.00 for BTC')
                                ->helperText('1 crypto = X USD. Used as fallback if live rate fails.')
                                ->nullable()
                                ->minValue(0)
                                ->step(0.00000001)
                                ->suffix('USD'),

                            TextInput::make('coingecko_id')
                                ->label('CoinGecko API ID')
                                ->placeholder('e.g., bitcoin, ethereum, tether')
                                ->helperText('Get from coingecko.com. Leave empty to use manual rate only.')
                                ->nullable()
                                ->maxLength(100),
                        ]),
                    ])->collapsible(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Only active cryptocurrencies will be shown to users'),
            ]);
    }
}
