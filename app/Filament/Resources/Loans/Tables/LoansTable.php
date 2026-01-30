<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Enums\LoanStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
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

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('loanApplication.reference_number')
                    ->label('Application Ref')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('Borrower')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('principal_amount')
                    ->label('Principal')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('outstanding_balance')
                    ->label('Outstanding')
                    ->money('USD')
                    ->sortable()
                    ->color(fn ($record) => $record->outstanding_balance > 0 ? 'warning' : 'success'),

                TextColumn::make('interest_rate')
                    ->label('Interest')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('monthly_payment')
                    ->label('Monthly')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('next_payment_date')
                    ->label('Next Payment')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(fn ($record) => $record->next_payment_date && $record->next_payment_date->isPast() ? 'danger' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LoanStatus $state): string => $state->label())
                    ->color(fn (LoanStatus $state): string => $state->color())
                    ->sortable(),

                TextColumn::make('disbursed_at')
                    ->label('Disbursed')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(LoanStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('info'),

                    EditAction::make()
                        ->color('primary'),

                    Action::make('record_payment')
                        ->label('Record Payment')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === LoanStatus::Active || $record->status === LoanStatus::Disbursed)
                        ->modalWidth(Width::Medium)
                        ->form([
                            TextInput::make('amount')
                                ->label('Payment Amount')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->minValue(0.01)
                                ->helperText('Enter the payment amount received'),
                            DatePicker::make('payment_date')
                                ->label('Payment Date')
                                ->required()
                                ->default(now())
                                ->maxDate(now()),
                        ])
                        ->action(function ($record, array $data) {
                            $newBalance = $record->outstanding_balance - ($data['amount'] * 100);

                            $record->update([
                                'outstanding_balance' => max(0, $newBalance),
                                'status' => $newBalance <= 0 ? LoanStatus::Closed : $record->status,
                                'closed_at' => $newBalance <= 0 ? now() : null,
                            ]);

                            // Create loan payment record if model exists
                            if (class_exists(\App\Models\LoanPayment::class)) {
                                \App\Models\LoanPayment::create([
                                    'loan_id' => $record->id,
                                    'amount' => $data['amount'] * 100,
                                    'payment_date' => $data['payment_date'],
                                    'status' => 'completed',
                                ]);
                            }

                            Notification::make()
                                ->title('Payment recorded successfully')
                                ->body($newBalance <= 0 ? 'Loan has been fully paid and closed.' : 'Outstanding balance updated.')
                                ->success()
                                ->send();
                        }),

                    Action::make('disburse')
                        ->label('Disburse Loan')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->visible(fn ($record) => $record->status === LoanStatus::Approved)
                        ->requiresConfirmation()
                        ->modalHeading('Disburse Loan')
                        ->modalDescription('This will transfer the loan amount to the borrower\'s account and mark the loan as disbursed.')
                        ->action(function ($record) {
                            $record->update([
                                'status' => LoanStatus::Disbursed,
                                'disbursed_at' => now(),
                            ]);
                            Notification::make()
                                ->title('Loan disbursed successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('activate')
                        ->label('Activate Loan')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === LoanStatus::Disbursed)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => LoanStatus::Active]);
                            Notification::make()
                                ->title('Loan activated')
                                ->success()
                                ->send();
                        }),

                    Action::make('close_loan')
                        ->label('Close Loan')
                        ->icon('heroicon-o-check-circle')
                        ->color('gray')
                        ->visible(fn ($record) => $record->status === LoanStatus::Active && $record->outstanding_balance <= 0)
                        ->requiresConfirmation()
                        ->modalHeading('Close Loan')
                        ->modalDescription('Are you sure you want to close this loan? This action indicates the loan has been fully repaid.')
                        ->action(function ($record) {
                            $record->update([
                                'status' => LoanStatus::Closed,
                                'closed_at' => now(),
                            ]);
                            Notification::make()
                                ->title('Loan closed successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('mark_defaulted')
                        ->label('Mark as Defaulted')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === LoanStatus::Active)
                        ->requiresConfirmation()
                        ->modalHeading('Mark Loan as Defaulted')
                        ->modalDescription('This will mark the loan as defaulted due to non-payment. This action should be taken after all collection attempts have failed.')
                        ->action(function ($record) {
                            $record->update(['status' => LoanStatus::Defaulted]);
                            Notification::make()
                                ->title('Loan marked as defaulted')
                                ->warning()
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
