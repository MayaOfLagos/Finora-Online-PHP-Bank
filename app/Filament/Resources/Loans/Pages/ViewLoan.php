<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Enums\LoanStatus;
use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()
                    ->color('primary'),

                Action::make('record_payment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn () => $this->record->status === LoanStatus::Active || $this->record->status === LoanStatus::Disbursed)
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
                    ->action(function (array $data) {
                        $newBalance = $this->record->outstanding_balance - ($data['amount'] * 100);

                        $this->record->update([
                            'outstanding_balance' => max(0, $newBalance),
                            'status' => $newBalance <= 0 ? LoanStatus::Closed : $this->record->status,
                            'closed_at' => $newBalance <= 0 ? now() : null,
                        ]);

                        if (class_exists(\App\Models\LoanPayment::class)) {
                            \App\Models\LoanPayment::create([
                                'loan_id' => $this->record->id,
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
                    ->visible(fn () => $this->record->status === LoanStatus::Approved)
                    ->requiresConfirmation()
                    ->modalHeading('Disburse Loan')
                    ->modalDescription('This will transfer the loan amount to the borrower\'s account and mark the loan as disbursed.')
                    ->action(function () {
                        $this->record->update([
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
                    ->visible(fn () => $this->record->status === LoanStatus::Disbursed)
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->record->update(['status' => LoanStatus::Active]);
                        Notification::make()
                            ->title('Loan activated')
                            ->success()
                            ->send();
                    }),

                Action::make('close_loan')
                    ->label('Close Loan')
                    ->icon('heroicon-o-check-circle')
                    ->color('gray')
                    ->visible(fn () => $this->record->status === LoanStatus::Active && $this->record->outstanding_balance <= 0)
                    ->requiresConfirmation()
                    ->modalHeading('Close Loan')
                    ->modalDescription('Are you sure you want to close this loan? This action indicates the loan has been fully repaid.')
                    ->action(function () {
                        $this->record->update([
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
                    ->visible(fn () => $this->record->status === LoanStatus::Active)
                    ->requiresConfirmation()
                    ->modalHeading('Mark Loan as Defaulted')
                    ->modalDescription('This will mark the loan as defaulted due to non-payment.')
                    ->action(function () {
                        $this->record->update(['status' => LoanStatus::Defaulted]);
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
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Loan Information')
                    ->description('Core loan details and borrower information')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('uuid')
                                    ->label('UUID')
                                    ->copyable()
                                    ->fontFamily('mono'),

                                TextEntry::make('loanApplication.reference_number')
                                    ->label('Application Reference')
                                    ->copyable()
                                    ->weight('bold'),

                                TextEntry::make('user.name')
                                    ->label('Borrower'),

                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->copyable(),

                                TextEntry::make('bankAccount.account_number')
                                    ->label('Disbursement Account')
                                    ->copyable(),

                                TextEntry::make('loanApplication.loanType.name')
                                    ->label('Loan Type')
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ]),

                Section::make('Financial Details')
                    ->description('Loan amounts and interest information')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('principal_amount')
                                    ->label('Principal Amount')
                                    ->money('USD')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('outstanding_balance')
                                    ->label('Outstanding Balance')
                                    ->money('USD')
                                    ->weight('bold')
                                    ->color(fn () => $this->record->outstanding_balance > 0 ? 'warning' : 'success'),

                                TextEntry::make('interest_rate')
                                    ->label('Interest Rate')
                                    ->suffix('% p.a.'),

                                TextEntry::make('monthly_payment')
                                    ->label('Monthly Payment')
                                    ->money('USD'),

                                TextEntry::make('paid_amount')
                                    ->label('Total Paid')
                                    ->state(fn () => ($this->record->principal_amount - $this->record->outstanding_balance))
                                    ->money('USD')
                                    ->color('success'),

                                TextEntry::make('progress')
                                    ->label('Repayment Progress')
                                    ->state(function () {
                                        if ($this->record->principal_amount <= 0) {
                                            return '0%';
                                        }
                                        $paid = $this->record->principal_amount - $this->record->outstanding_balance;

                                        return round(($paid / $this->record->principal_amount) * 100, 1).'%';
                                    })
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ]),

                Section::make('Payment Schedule')
                    ->description('Payment dates and loan timeline')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('next_payment_date')
                                    ->label('Next Payment Due')
                                    ->date('F d, Y')
                                    ->color(fn () => $this->record->next_payment_date && $this->record->next_payment_date->isPast() ? 'danger' : null),

                                TextEntry::make('final_payment_date')
                                    ->label('Final Payment Date')
                                    ->date('F d, Y'),

                                TextEntry::make('disbursed_at')
                                    ->label('Disbursed On')
                                    ->dateTime('F d, Y H:i')
                                    ->placeholder('Not yet disbursed'),

                                TextEntry::make('closed_at')
                                    ->label('Closed On')
                                    ->dateTime('F d, Y H:i')
                                    ->placeholder('Not yet closed'),
                            ]),
                    ]),

                Section::make('Status')
                    ->description('Current loan status and record timestamps')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Loan Status')
                                    ->badge()
                                    ->formatStateUsing(fn (LoanStatus $state): string => $state->label())
                                    ->color(fn (LoanStatus $state): string => $state->color()),

                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('F d, Y H:i'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('F d, Y H:i'),
                            ]),
                    ]),
            ]);
    }
}
