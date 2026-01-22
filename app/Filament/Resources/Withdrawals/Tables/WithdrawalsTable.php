<?php

namespace App\Filament\Resources\Withdrawals\Tables;

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

class WithdrawalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bankAccount.account_number')
                    ->label('Account')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency, divideBy: 100)
                    ->sortable()
                    ->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved',
                        'success' => 'completed',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Requested At'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
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
