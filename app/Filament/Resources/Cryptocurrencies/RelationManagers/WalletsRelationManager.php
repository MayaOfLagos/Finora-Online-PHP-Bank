<?php

namespace App\Filament\Resources\Cryptocurrencies\RelationManagers;

use App\Filament\Resources\Cryptocurrencies\CryptocurrencyResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class WalletsRelationManager extends RelationManager
{
    protected static string $relationship = 'wallets';

    protected static ?string $relatedResource = CryptocurrencyResource::class;

    protected static ?string $title = 'Wallet Addresses';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wallet_address')
                    ->label('Wallet Address')
                    ->searchable()
                    ->limit(30)
                    ->copyable()
                    ->copyMessage('Wallet address copied')
                    ->tooltip(fn ($record) => $record->wallet_address),

                TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Wallet')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        TextInput::make('wallet_address')
                            ->label('Wallet Address')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter wallet address')
                            ->helperText('The cryptocurrency wallet address'),

                        TextInput::make('label')
                            ->label('Label')
                            ->nullable()
                            ->maxLength(100)
                            ->placeholder('e.g., Main Wallet, Cold Storage')
                            ->helperText('Optional label to identify this wallet'),

                        Textarea::make('qr_code')
                            ->label('QR Code (optional)')
                            ->rows(3)
                            ->nullable()
                            ->placeholder('QR code data or URL')
                            ->helperText('Optional QR code for this wallet'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active wallets are shown to users'),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('wallet_address')
                            ->label('Wallet Address')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('label')
                            ->label('Label')
                            ->nullable()
                            ->maxLength(100),

                        Textarea::make('qr_code')
                            ->label('QR Code')
                            ->rows(3)
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                DeleteAction::make(),
            ]);
    }
}
