<?php

namespace App\Filament\Resources\DomesticTransfers\Tables;

use App\Enums\TransferStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DomesticTransfersTable
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
                    ->label('From Account')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('bank.name')
                    ->label('To Bank')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('beneficiary_name')
                    ->label('Beneficiary')
                    ->searchable()
                    ->limit(20),
                TextColumn::make('beneficiary_account')
                    ->label('To Account')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => strtolower($record->currency ?? 'usd'), divideBy: 100)
                    ->sortable(),
                TextColumn::make('fee')
                    ->label('Fee')
                    ->money(fn ($record) => strtolower($record->currency ?? 'usd'), divideBy: 100)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (TransferStatus $state): string => $state->color()),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
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
