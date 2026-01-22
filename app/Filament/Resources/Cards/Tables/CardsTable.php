<?php

namespace App\Filament\Resources\Cards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('user.id')
                    ->searchable(),
                TextColumn::make('bankAccount.id')
                    ->searchable(),
                TextColumn::make('cardType.name')
                    ->searchable(),
                TextColumn::make('card_number')
                    ->searchable(),
                TextColumn::make('card_holder_name')
                    ->searchable(),
                TextColumn::make('expiry_month')
                    ->searchable(),
                TextColumn::make('expiry_year')
                    ->searchable(),
                TextColumn::make('cvv')
                    ->searchable(),
                TextColumn::make('pin')
                    ->searchable(),
                TextColumn::make('spending_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('daily_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_virtual')
                    ->boolean(),
                TextColumn::make('issued_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('blocked_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
