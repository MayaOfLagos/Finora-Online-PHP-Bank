<?php

namespace App\Filament\Resources\LoanPayments\Tables;

use App\Enums\LoanStatus;
use App\Models\LoanPayment;
use App\Models\TransactionHistory;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class LoanPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->badge(),

                TextColumn::make('loan.uuid')
                    ->label('Loan')
                    ->searchable()
                    ->copyable()
                    ->color('info'),

                TextColumn::make('user.name')
                    ->label('Borrower')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD', divideBy: 100)
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color(fn ($state) => $state === 'crypto' ? 'info' : 'warning')
                    ->sortable(),

                TextColumn::make('asset')
                    ->label('Asset')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('tx_hash')
                    ->label('TX Hash')
                    ->toggleable()
                    ->limit(20)
                    ->tooltip(fn ($state) => $state),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'rejected',
                        'gray' => 'failed',
                    ])
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Submitted')
                    ->date()
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                        'failed' => 'Failed',
                    ])
                    ->label('Status'),
                SelectFilter::make('payment_method')
                    ->options([
                        'manual' => 'Manual',
                        'crypto' => 'Crypto',
                    ])
                    ->label('Method'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('approve')
                        ->label('Approve & Apply')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (LoanPayment $record) => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->action(function (LoanPayment $record) {
                            DB::transaction(function () use ($record) {
                                $loan = $record->loan()->lockForUpdate()->first();

                                $newOutstanding = max(0, $loan->outstanding_balance - $record->amount);
                                $loan->outstanding_balance = $newOutstanding;

                                if ($newOutstanding === 0) {
                                    $loan->status = LoanStatus::Closed;
                                    $loan->closed_at = now();
                                }

                                $loan->save();

                                $record->update([
                                    'status' => 'completed',
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                ]);

                                TransactionHistory::create([
                                    'user_id' => $loan->user_id,
                                    'bank_account_id' => $loan->bank_account_id,
                                    'transaction_type' => 'loan_repayment',
                                    'reference_number' => $record->reference_number,
                                    'transactionable_type' => get_class($record),
                                    'transactionable_id' => $record->id,
                                    'amount' => $record->amount / 100,
                                    'currency' => $loan->bankAccount->currency ?? 'USD',
                                    'status' => 'completed',
                                    'type' => 'debit',
                                    'description' => 'Loan repayment for '.$loan->uuid,
                                    'metadata' => [
                                        'payment_method' => $record->payment_method,
                                        'asset' => $record->asset,
                                        'tx_hash' => $record->tx_hash,
                                    ],
                                ]);
                            });

                            Notification::make()
                                ->title('Repayment approved and applied')
                                ->success()
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (LoanPayment $record) => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->action(function (LoanPayment $record) {
                            $record->update([
                                'status' => 'rejected',
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Repayment rejected')
                                ->warning()
                                ->send();
                        }),
                ])->label('Actions')->icon('heroicon-m-ellipsis-vertical')->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
