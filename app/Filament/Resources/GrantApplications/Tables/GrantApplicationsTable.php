<?php

namespace App\Filament\Resources\GrantApplications\Tables;

use App\Enums\DepositStatus;
use App\Enums\GrantApplicationStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\BankAccount;
use App\Models\GrantDisbursement;
use App\Models\TransactionHistory;
use App\Notifications\GrantApprovedNotification;
use App\Notifications\GrantDisbursedNotification;
use App\Notifications\GrantRejectedNotification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GrantApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('user.id')
                    ->searchable(),
                TextColumn::make('grantProgram.name')
                    ->searchable(),
                TextColumn::make('reference_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('approved_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary'),

                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === GrantApplicationStatus::Pending || $record->status === GrantApplicationStatus::UnderReview)
                        ->requiresConfirmation()
                        ->modalHeading('Approve Grant Application')
                        ->modalDescription('Are you sure you want to approve this grant application?')
                        ->modalWidth(Width::Medium)
                        ->action(function ($record) {
                            $record->update([
                                'status' => GrantApplicationStatus::Approved,
                                'approved_at' => now(),
                                'approved_by' => Auth::id(),
                            ]);

                            // Send email notification
                            $record->user->notify(new GrantApprovedNotification($record));

                            Notification::make()
                                ->title('Grant Approved')
                                ->success()
                                ->body('The grant application has been approved successfully.')
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === GrantApplicationStatus::Pending || $record->status === GrantApplicationStatus::UnderReview)
                        ->modalWidth(Width::Medium)
                        ->form([
                            Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->rows(3)
                                ->placeholder('Provide a reason for rejection...'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status' => GrantApplicationStatus::Rejected,
                                'rejection_reason' => $data['rejection_reason'],
                                'approved_at' => now(),
                                'approved_by' => Auth::id(),
                            ]);

                            // Send email notification
                            $record->user->notify(new GrantRejectedNotification($record));

                            Notification::make()
                                ->title('Grant Rejected')
                                ->warning()
                                ->body('The grant application has been rejected.')
                                ->send();
                        }),

                    Action::make('disburse')
                        ->label('Disburse Funds')
                        ->icon('heroicon-o-banknotes')
                        ->color('primary')
                        ->visible(fn ($record) => $record->status === GrantApplicationStatus::Approved && ! $record->disbursement)
                        ->modalWidth(Width::Large)
                        ->form([
                            Select::make('bank_account_id')
                                ->label('Bank Account')
                                ->required()
                                ->options(function ($record) {
                                    return $record->user->bankAccounts()->pluck('account_number', 'id');
                                })
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function ($record, array $data) {
                            // Create disbursement record
                            $disbursement = GrantDisbursement::create([
                                'grant_application_id' => $record->id,
                                'bank_account_id' => $data['bank_account_id'],
                                'amount' => $record->grantProgram->amount,
                                'status' => DepositStatus::Completed,
                                'disbursed_at' => now(),
                            ]);

                            // Credit user account
                            $bankAccount = BankAccount::find($data['bank_account_id']);
                            $bankAccount->increment('balance', $record->grantProgram->amount);
                            $bankAccount->refresh();

                            TransactionHistory::create([
                                'user_id' => $record->user_id,
                                'bank_account_id' => $bankAccount->id,
                                'transaction_type' => TransactionType::GrantDisbursement->value,
                                'transactionable_type' => GrantDisbursement::class,
                                'transactionable_id' => $disbursement->id,
                                'reference_number' => $disbursement->reference_number,
                                'amount' => $record->grantProgram->amount / 100,
                                'type' => 'credit',
                                'currency' => $record->grantProgram->currency,
                                'status' => TransactionStatus::Completed->value,
                                'description' => 'Grant Disbursement - '.$record->grantProgram->name,
                                'processed_at' => now(),
                                'balance_after' => $bankAccount->balance / 100,
                            ]);

                            // Update application status
                            $record->update([
                                'status' => GrantApplicationStatus::Disbursed,
                            ]);

                            // Send email notification
                            $record->user->notify(new GrantDisbursedNotification($record, $bankAccount->account_number));

                            Notification::make()
                                ->title('Funds Disbursed')
                                ->success()
                                ->body('Grant funds have been disbursed to the user account.')
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
