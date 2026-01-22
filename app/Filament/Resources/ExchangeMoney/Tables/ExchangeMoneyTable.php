<?php

namespace App\Filament\Resources\ExchangeMoney\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ExchangeMoneyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('from_currency')
                    ->label('From')
                    ->badge()
                    ->color('info'),
                TextColumn::make('to_currency')
                    ->label('To')
                    ->badge()
                    ->color('success'),
                TextColumn::make('from_amount')
                    ->label('From Amount')
                    ->money(fn ($record) => strtolower($record->from_currency), divideBy: 100)
                    ->sortable(),
                TextColumn::make('to_amount')
                    ->label('To Amount')
                    ->money(fn ($record) => strtolower($record->to_currency), divideBy: 100)
                    ->sortable(),
                TextColumn::make('exchange_rate')
                    ->label('Rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fee')
                    ->label('Fee')
                    ->money('usd', divideBy: 100)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('from_currency')
                    ->label('From Currency')
                    ->options([
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                        'GBP' => 'GBP',
                        'NGN' => 'NGN',
                    ]),
                SelectFilter::make('to_currency')
                    ->label('To Currency')
                    ->options([
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                        'GBP' => 'GBP',
                        'NGN' => 'NGN',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
