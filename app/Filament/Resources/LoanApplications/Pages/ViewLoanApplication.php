<?php

namespace App\Filament\Resources\LoanApplications\Pages;

use App\Enums\LoanStatus;
use App\Filament\Resources\LoanApplications\LoanApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class ViewLoanApplication extends ViewRecord
{
    protected static string $resource = LoanApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()
                    ->color('primary'),

                Action::make('approve')
                    ->label('Approve Application')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => $this->record->status === LoanStatus::Pending || $this->record->status === LoanStatus::UnderReview)
                    ->requiresConfirmation()
                    ->modalHeading('Approve Loan Application')
                    ->modalDescription('Are you sure you want to approve this loan application? This will allow the loan to be disbursed.')
                    ->action(function () {
                        $this->record->update([
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
                    ->label('Reject Application')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => $this->record->status === LoanStatus::Pending || $this->record->status === LoanStatus::UnderReview)
                    ->modalWidth(Width::Medium)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Please provide a reason for rejecting this application...'),
                    ])
                    ->action(function (array $data) {
                        $this->record->update([
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
                    ->visible(fn () => $this->record->status === LoanStatus::Pending)
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->record->update(['status' => LoanStatus::UnderReview]);
                        Notification::make()
                            ->title('Application marked under review')
                            ->info()
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
                Section::make('Application Details')
                    ->description('Basic information about the loan application')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reference_number')
                                    ->label('Reference Number')
                                    ->weight('bold')
                                    ->copyable(),

                                TextEntry::make('uuid')
                                    ->label('UUID')
                                    ->copyable()
                                    ->fontFamily('mono'),

                                TextEntry::make('user.name')
                                    ->label('Applicant'),

                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->copyable(),

                                TextEntry::make('loanType.name')
                                    ->label('Loan Type')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('created_at')
                                    ->label('Applied On')
                                    ->dateTime('F d, Y H:i'),
                            ]),
                    ]),

                Section::make('Financial Terms')
                    ->description('Loan amount, interest rate, and payment details')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('amount')
                                    ->label('Loan Amount')
                                    ->money('USD')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('term_months')
                                    ->label('Loan Term')
                                    ->suffix(' months'),

                                TextEntry::make('interest_rate')
                                    ->label('Interest Rate')
                                    ->suffix('% p.a.'),

                                TextEntry::make('monthly_payment')
                                    ->label('Monthly Payment')
                                    ->money('USD'),

                                TextEntry::make('total_payable')
                                    ->label('Total Payable')
                                    ->money('USD')
                                    ->weight('bold'),
                            ]),
                    ]),

                Section::make('Loan Purpose')
                    ->description('Details about the intended use of the loan')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsed(fn () => empty($this->record->purpose))
                    ->schema([
                        TextEntry::make('purpose')
                            ->label('')
                            ->placeholder('No purpose specified')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status & Approval')
                    ->description('Application status and approval information')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Application Status')
                                    ->badge()
                                    ->formatStateUsing(fn (LoanStatus $state): string => $state->label())
                                    ->color(fn (LoanStatus $state): string => $state->color()),

                                TextEntry::make('approved_at')
                                    ->label('Approved At')
                                    ->dateTime('F d, Y H:i')
                                    ->placeholder('Not yet approved'),

                                TextEntry::make('approver.name')
                                    ->label('Approved By')
                                    ->placeholder('N/A'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('F d, Y H:i'),
                            ]),

                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->columnSpanFull()
                            ->visible(fn () => $this->record->status === LoanStatus::Rejected)
                            ->color('danger'),
                    ]),
            ]);
    }
}
