<?php

namespace App\Filament\Resources\TransactionHistories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionHistoryTable
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
                TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'wire_transfer' => 'info',
                        'domestic_transfer' => 'primary',
                        'internal_transfer' => 'success',
                        'check_deposit' => 'warning',
                        'mobile_deposit' => 'success',
                        'crypto_deposit' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => strtoupper($record->currency ?? 'USD'))
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Currency')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        'reversed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('email_sent')
                    ->label('Email')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sent' : 'Pending')
                    ->toggleable(),
                TextColumn::make('processed_at')
                    ->label('Processed')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('transaction_type')
                    ->label('Transaction Type')
                    ->options([
                        'wire_transfer' => 'Wire Transfer',
                        'domestic_transfer' => 'Domestic Transfer',
                        'internal_transfer' => 'Internal Transfer',
                        'check_deposit' => 'Check Deposit',
                        'mobile_deposit' => 'Mobile Deposit',
                        'crypto_deposit' => 'Crypto Deposit',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'reversed' => 'Reversed',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalWidth('4xl'),
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'processing']))
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'completed',
                                'processed_at' => now(),
                            ]);

                            // Update the related transaction
                            if ($record->transactionable) {
                                $modelClass = get_class($record->transactionable);

                                // Handle different status enums
                                if (in_array($modelClass, [
                                    'App\Models\CheckDeposit',
                                    'App\Models\MobileDeposit',
                                    'App\Models\CryptoDeposit'
                                ])) {
                                    // DepositStatus enum
                                    $record->transactionable->update(['status' => \App\Enums\DepositStatus::Completed]);
                                } else {
                                    // TransferStatus enum
                                    $record->transactionable->update(['status' => \App\Enums\TransferStatus::Completed]);
                                }
                            }

                            Notification::make()
                                ->title('Transaction Approved')
                                ->success()
                                ->send();
                        }),
                    Action::make('decline')
                        ->label('Decline')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Decline Transaction')
                        ->modalDescription('Are you sure you want to decline this transaction?')
                        ->form([
                            MarkdownEditor::make('reason')
                                ->label('Reason for declining')
                                ->required()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'bulletList',
                                    'orderedList',
                                ])
                                ->disableToolbarButtons([
                                    'attachFiles',
                                    'codeBlock',
                                    'link',
                                    'heading',
                                ]),
                        ])
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'processing']))
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status' => 'failed',
                                'processed_at' => now(),
                                'metadata' => array_merge($record->metadata ?? [], [
                                    'decline_reason' => $data['reason'],
                                    'declined_at' => now()->toDateTimeString(),
                                ]),
                            ]);

                            // Update the related transaction
                            if ($record->transactionable) {
                                $modelClass = get_class($record->transactionable);

                                // Handle different status enums
                                if (in_array($modelClass, [
                                    'App\Models\CheckDeposit',
                                    'App\Models\MobileDeposit',
                                    'App\Models\CryptoDeposit'
                                ])) {
                                    // DepositStatus enum - use 'rejected'
                                    $record->transactionable->update([
                                        'status' => \App\Enums\DepositStatus::Rejected,
                                        'rejection_reason' => $data['reason'],
                                    ]);
                                } else {
                                    // TransferStatus enum - use 'failed'
                                    $record->transactionable->update([
                                        'status' => \App\Enums\TransferStatus::Failed,
                                        'failed_reason' => $data['reason'],
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title('Transaction Declined')
                                ->success()
                                ->send();
                        }),
                    Action::make('reverse')
                        ->label('Reverse')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reverse Transaction')
                        ->modalDescription('This will reverse the transaction. Continue?')
                        ->visible(fn ($record) => $record->status === 'completed')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'reversed',
                                'metadata' => array_merge($record->metadata ?? [], [
                                    'reversed_at' => now()->toDateTimeString(),
                                ]),
                            ]);

                            Notification::make()
                                ->title('Transaction Reversed')
                                ->success()
                                ->send();
                        }),
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
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
