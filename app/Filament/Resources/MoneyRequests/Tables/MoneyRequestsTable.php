<?php

namespace App\Filament\Resources\MoneyRequests\Tables;

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

class MoneyRequestsTable
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
                TextColumn::make('requester.email')
                    ->label('Requester')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('responder.email')
                    ->label('Responder')
                    ->searchable()
                    ->sortable()
                    ->default('—'),
                TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency, divideBy: 100)
                    ->sortable()
                    ->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'accepted',
                        'success' => 'completed',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('type')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('expires_at')
                    ->date()
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
                        'accepted' => 'Accepted',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'personal' => 'Personal',
                        'business' => 'Business',
                        'split' => 'Split Bill',
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
