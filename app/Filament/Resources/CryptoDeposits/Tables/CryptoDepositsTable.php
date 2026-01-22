<?php

namespace App\Filament\Resources\CryptoDeposits\Tables;

use App\Enums\DepositStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CryptoDepositsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Reference copied')
                    ->weight('semibold'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable()
                    ->description(fn ($record) => $record->user->email),
                TextColumn::make('bankAccount.account_number')
                    ->label('Account')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn ($record) => $record->bankAccount->accountType->name ?? 'N/A'),
                TextColumn::make('cryptocurrency.name')
                    ->label('Cryptocurrency')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('crypto_amount')
                    ->label('Crypto Amount')
                    ->numeric(decimalPlaces: 8)
                    ->sortable()
                    ->suffix(fn ($record) => ' ' . $record->cryptocurrency->symbol),
                TextColumn::make('usd_amount')
                    ->label('USD Value')
                    ->money('USD', divideBy: 100)
                    ->sortable(),
                TextColumn::make('transaction_hash')
                    ->label('TX Hash')
                    ->searchable()
                    ->copyable()
                    ->limit(10)
                    ->tooltip(fn ($record) => $record->transaction_hash)
                    ->placeholder('Not provided')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (DepositStatus $state): string => $state->color()),
                TextColumn::make('verifiedBy.name')
                    ->label('Verified By')
                    ->placeholder('Not verified')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('Not verified')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('cryptocurrency_id')
                    ->relationship('cryptocurrency', 'name')
                    ->label('Cryptocurrency'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalWidth('3xl'),
                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->modalWidth('3xl'),
                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),
                ])
                    ->button()
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
