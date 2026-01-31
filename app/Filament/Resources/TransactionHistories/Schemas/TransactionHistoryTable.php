<?php

namespace App\Filament\Resources\TransactionHistories\Schemas;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Grid as FormGrid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->defaultSort('created_at', 'desc')
            ->filters(self::getFilters())
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalWidth('4xl')
                        ->infolist(self::getInfolistSchema()),
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'processing']))
                        ->action(fn ($record) => self::approveTransaction($record)),
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
                                ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList'])
                                ->disableToolbarButtons(['attachFiles', 'codeBlock', 'link', 'heading']),
                        ])
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'processing']))
                        ->action(fn ($record, array $data) => self::declineTransaction($record, $data)),
                    Action::make('reverse')
                        ->label('Reverse')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reverse Transaction')
                        ->modalDescription('This will reverse the transaction. Continue?')
                        ->visible(fn ($record) => $record->status === 'completed')
                        ->action(fn ($record) => self::reverseTransaction($record)),
                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->modalWidth('4xl')
                        ->form(self::getEditFormSchema()),
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

    protected static function getColumns(): array
    {
        return [
            TextColumn::make('reference_number')
                ->label('Reference')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('Reference copied'),
            TextColumn::make('transaction_type')
                ->label('Type')
                ->badge()
                ->formatStateUsing(fn (TransactionType $state): string => ucwords(str_replace('_', ' ', $state->value)))
                ->color(fn (TransactionType $state): string => match ($state) {
                    TransactionType::WireTransfer => 'info',
                    TransactionType::DomesticTransfer => 'primary',
                    TransactionType::InternalTransfer => 'success',
                    TransactionType::CheckDeposit => 'warning',
                    TransactionType::MobileDeposit => 'success',
                    TransactionType::CryptoDeposit => 'warning',
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
                ->color(fn (TransactionStatus $state): string => match ($state) {
                    TransactionStatus::Pending => 'warning',
                    TransactionStatus::Processing => 'info',
                    TransactionStatus::Completed => 'success',
                    TransactionStatus::Failed => 'danger',
                    TransactionStatus::Cancelled => 'gray',
                    TransactionStatus::Reversed => 'danger',
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
        ];
    }

    protected static function getFilters(): array
    {
        return [
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
        ];
    }

    protected static function getInfolistSchema(): array
    {
        return [
            Section::make('Transaction Details')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('reference_number')
                            ->label('Reference Number')
                            ->copyable()
                            ->weight('bold'),
                        TextEntry::make('uuid')
                            ->label('UUID')
                            ->copyable()
                            ->fontFamily('mono'),
                        TextEntry::make('transaction_type')
                            ->label('Transaction Type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                            ->color(fn (string $state): string => match ($state) {
                                'wire_transfer' => 'info',
                                'domestic_transfer' => 'primary',
                                'internal_transfer' => 'success',
                                'check_deposit', 'deposit' => 'warning',
                                'mobile_deposit', 'credit' => 'success',
                                'crypto_deposit' => 'warning',
                                'withdrawal', 'debit' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'processing' => 'info',
                                'completed' => 'success',
                                'failed' => 'danger',
                                'cancelled' => 'gray',
                                'reversed' => 'danger',
                                default => 'gray',
                            }),
                    ]),
                ]),
            Section::make('Financial Information')
                ->icon('heroicon-o-currency-dollar')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('amount')
                            ->money(fn ($record) => strtoupper($record->currency ?? 'USD'))
                            ->size('lg')
                            ->weight('bold'),
                        TextEntry::make('currency')
                            ->badge()
                            ->color('gray'),
                    ]),
                ]),
            Section::make('User Information')
                ->icon('heroicon-o-user')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('user.name')->label('Customer Name'),
                        TextEntry::make('user.email')->label('Email')->copyable(),
                    ]),
                ]),
            Section::make('Additional Information')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('description')
                            ->columnSpanFull()
                            ->placeholder('No description provided'),
                        IconEntry::make('email_sent')
                            ->label('Email Notification')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                        IconEntry::make('wallet_debited')
                            ->label('Wallet Updated')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                        TextEntry::make('generated_by')
                            ->label('Generated By')
                            ->formatStateUsing(function ($state) {
                                if (! $state) {
                                    return 'System';
                                }
                                $admin = \App\Models\User::find($state);

                                return $admin ? $admin->name : 'Admin #'.$state;
                            }),
                        TextEntry::make('email_sent_at')
                            ->label('Email Sent At')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not sent'),
                    ]),
                ]),
            Section::make('Timestamps')
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('processed_at')
                            ->label('Processed At')
                            ->dateTime('F d, Y H:i')
                            ->placeholder('Not processed'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('F d, Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F d, Y H:i'),
                    ]),
                ]),
        ];
    }

    protected static function getEditFormSchema(): array
    {
        return [
            FormSection::make('Transaction Details')
                ->icon('heroicon-o-document-text')
                ->schema([
                    FormGrid::make(2)->schema([
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->disabled()
                            ->dehydrated(),
                        Select::make('transaction_type')
                            ->label('Transaction Type')
                            ->options([
                                'wire_transfer' => 'Wire Transfer',
                                'domestic_transfer' => 'Domestic Transfer',
                                'internal_transfer' => 'Internal Transfer',
                                'check_deposit' => 'Check Deposit',
                                'mobile_deposit' => 'Mobile Deposit',
                                'crypto_deposit' => 'Crypto Deposit',
                                'deposit' => 'Deposit',
                                'withdrawal' => 'Withdrawal',
                                'transfer' => 'Transfer',
                                'refund' => 'Refund',
                                'adjustment' => 'Adjustment',
                                'credit' => 'Credit',
                                'debit' => 'Debit',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                                'reversed' => 'Reversed',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Select::make('currency')
                            ->label('Currency')
                            ->options([
                                'USD' => 'USD - US Dollar',
                                'EUR' => 'EUR - Euro',
                                'GBP' => 'GBP - British Pound',
                                'CAD' => 'CAD - Canadian Dollar',
                            ])
                            ->default('USD'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                ]),
        ];
    }

    protected static function approveTransaction($record): void
    {
        $record->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        if ($record->transactionable) {
            $modelClass = get_class($record->transactionable);

            if (in_array($modelClass, [
                'App\Models\CheckDeposit',
                'App\Models\MobileDeposit',
                'App\Models\CryptoDeposit',
            ])) {
                $record->transactionable->update(['status' => \App\Enums\DepositStatus::Completed]);
            } else {
                $record->transactionable->update(['status' => \App\Enums\TransferStatus::Completed]);
            }
        }

        Notification::make()
            ->title('Transaction Approved')
            ->success()
            ->send();
    }

    protected static function declineTransaction($record, array $data): void
    {
        $record->update([
            'status' => 'failed',
            'processed_at' => now(),
            'metadata' => array_merge($record->metadata ?? [], [
                'decline_reason' => $data['reason'],
                'declined_at' => now()->toDateTimeString(),
            ]),
        ]);

        if ($record->transactionable) {
            $modelClass = get_class($record->transactionable);

            if (in_array($modelClass, [
                'App\Models\CheckDeposit',
                'App\Models\MobileDeposit',
                'App\Models\CryptoDeposit',
            ])) {
                $record->transactionable->update([
                    'status' => \App\Enums\DepositStatus::Rejected,
                    'rejection_reason' => $data['reason'],
                ]);
            } else {
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
    }

    protected static function reverseTransaction($record): void
    {
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
    }
}
