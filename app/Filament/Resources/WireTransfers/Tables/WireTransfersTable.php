<?php

namespace App\Filament\Resources\WireTransfers\Tables;

use App\Enums\TransferStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WireTransfersTable
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
                    ->copyMessage('Reference copied'),
                TextColumn::make('uuid')
                    ->label('ID')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 8).'...')
                    ->copyable()
                    ->copyMessage('UUID copied')
                    ->copyableState(fn (string $state): string => $state)
                    ->tooltip(fn (string $state): string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bankAccount.account_name')
                    ->label('Bank Account')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('beneficiary_name')
                    ->label('Beneficiary')
                    ->searchable()
                    ->limit(20),
                TextColumn::make('amount')
                    ->money(fn ($record) => strtolower($record->currency ?? 'usd'), divideBy: 100)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransferStatus $state): string => $state->color()),
                TextColumn::make('current_step')
                    ->label('Step')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
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
                        ->modalWidth('4xl'),
                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->modalWidth('4xl'),
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
