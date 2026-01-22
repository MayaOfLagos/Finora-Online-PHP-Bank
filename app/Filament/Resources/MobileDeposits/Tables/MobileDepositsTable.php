<?php

namespace App\Filament\Resources\MobileDeposits\Tables;

use App\Enums\DepositStatus;
use App\Enums\PaymentGateway;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MobileDepositsTable
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
                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->color(fn (PaymentGateway $state): string => match($state->value) {
                        'stripe' => 'success',
                        'paypal' => 'info',
                        'paystack' => 'warning',
                        'flutterwave' => 'primary',
                        'razorpay' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('gateway_transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->copyable()
                    ->limit(15)
                    ->tooltip(fn ($record) => $record->gateway_transaction_id)
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
                    ->color(fn (DepositStatus $state): string => $state->color()),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('Not completed')
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
                    ]),
                SelectFilter::make('gateway')
                    ->options([
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                        'paystack' => 'Paystack',
                        'flutterwave' => 'Flutterwave',
                        'razorpay' => 'Razorpay',
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
