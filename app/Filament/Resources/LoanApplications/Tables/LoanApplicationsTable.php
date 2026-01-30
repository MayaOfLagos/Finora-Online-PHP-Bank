<?php

namespace App\Filament\Resources\LoanApplications\Tables;

use App\Enums\LoanStatus;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoanApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('loanType.name')
                    ->label('Loan Type')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('term_months')
                    ->label('Term')
                    ->suffix(' months')
                    ->sortable(),

                TextColumn::make('interest_rate')
                    ->label('Interest')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LoanStatus $state): string => $state->label())
                    ->color(fn (LoanStatus $state): string => $state->color())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(LoanStatus::class),
                SelectFilter::make('loan_type_id')
                    ->relationship('loanType', 'name')
                    ->label('Loan Type'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('info'),

                    EditAction::make()
                        ->color('primary'),

                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === LoanStatus::Pending || $record->status === LoanStatus::UnderReview)
                        ->requiresConfirmation()
                        ->modalHeading('Approve Loan Application')
                        ->modalDescription('Are you sure you want to approve this loan application? This will allow the loan to be disbursed.')
                        ->action(function ($record) {
                            $record->update([
                                'status' => LoanStatus::Approved,
                                'approved_at' => now(),
                                'approved_by' => auth()->id(),
                            ]);
                            Notification::make()
                                ->title('Loan application approved')
                                ->success()
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === LoanStatus::Pending || $record->status === LoanStatus::UnderReview)
                        ->modalWidth(Width::Medium)
                        ->form([
                            Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->rows(3)
                                ->placeholder('Please provide a reason for rejecting this application...'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status' => LoanStatus::Rejected,
                                'rejection_reason' => $data['rejection_reason'],
                            ]);
                            Notification::make()
                                ->title('Loan application rejected')
                                ->warning()
                                ->send();
                        }),

                    Action::make('under_review')
                        ->label('Mark Under Review')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('info')
                        ->visible(fn ($record) => $record->status === LoanStatus::Pending)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => LoanStatus::UnderReview]);
                            Notification::make()
                                ->title('Application marked under review')
                                ->info()
                                ->send();
                        }),

                    Action::make('disburse')
                        ->label('Disburse Loan')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === LoanStatus::Approved && ! $record->loan)
                        ->requiresConfirmation()
                        ->modalHeading('Disburse Loan')
                        ->modalDescription('This will create an active loan and credit the borrower\'s account.')
                        ->action(function ($record) {
                            $bankAccount = $record->bankAccount
                                ?? $record->user->bankAccounts()->where('is_primary', true)->first()
                                ?? $record->user->bankAccounts()->first();

                            if (! $bankAccount) {
                                Notification::make()
                                    ->title('No bank account found')
                                    ->danger()
                                    ->body('The borrower has no bank account to receive funds.')
                                    ->send();

                                return;
                            }

                            // Create the loan record
                            $loan = \App\Models\Loan::create([
                                'loan_application_id' => $record->id,
                                'user_id' => $record->user_id,
                                'bank_account_id' => $bankAccount->id,
                                'principal_amount' => $record->amount,
                                'outstanding_balance' => $record->total_payable,
                                'interest_rate' => $record->interest_rate,
                                'monthly_payment' => $record->monthly_payment,
                                'next_payment_date' => now()->addMonth(),
                                'final_payment_date' => now()->addMonths($record->term_months),
                                'status' => LoanStatus::Active,
                                'disbursed_at' => now(),
                            ]);

                            // Credit the borrower's account
                            $bankAccount->increment('balance', $record->amount);

                            // Update application status
                            $record->update(['status' => LoanStatus::Disbursed]);

                            Notification::make()
                                ->title('Loan disbursed successfully')
                                ->body("Loan {$loan->uuid} created and funds credited to account {$bankAccount->account_number}")
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make(),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
