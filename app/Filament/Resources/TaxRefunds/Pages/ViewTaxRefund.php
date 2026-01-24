<?php

namespace App\Filament\Resources\TaxRefunds\Pages;

use App\Enums\TaxRefundStatus;
use App\Filament\Resources\TaxRefunds\TaxRefundResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class ViewTaxRefund extends ViewRecord
{
    protected static string $resource = TaxRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()
                    ->color('primary'),

                Action::make('verify_identity')
                    ->label('Request ID.me Verification')
                    ->icon('heroicon-o-identification')
                    ->color('warning')
                    ->visible(fn () => ! $this->record->idme_verified && in_array($this->record->status, [TaxRefundStatus::Pending, TaxRefundStatus::IdentityVerification]))
                    ->requiresConfirmation()
                    ->modalHeading('Request ID.me Verification')
                    ->modalDescription('Send a request to the taxpayer to verify their identity through ID.me. They will receive an email with instructions.')
                    ->action(function () {
                        $this->record->update(['status' => TaxRefundStatus::IdentityVerification]);
                        Notification::make()
                            ->title('ID.me verification requested')
                            ->body('The taxpayer will be notified to verify their identity.')
                            ->info()
                            ->send();
                    }),

                Action::make('mark_verified')
                    ->label('Mark ID.me Verified')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn () => ! $this->record->idme_verified)
                    ->requiresConfirmation()
                    ->modalHeading('Confirm ID.me Verification')
                    ->modalDescription('Confirm that the taxpayer has successfully verified their identity through ID.me.')
                    ->action(function () {
                        $this->record->update([
                            'idme_verified' => true,
                            'idme_verified_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Identity verified')
                            ->success()
                            ->send();
                    }),

                Action::make('process')
                    ->label('Start Processing')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn () => in_array($this->record->status, [TaxRefundStatus::Pending, TaxRefundStatus::IdentityVerification]))
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->record->update([
                            'status' => TaxRefundStatus::Processing,
                            'processed_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Processing started')
                            ->info()
                            ->send();
                    }),

                Action::make('approve')
                    ->label('Approve Refund')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => $this->record->idme_verified && in_array($this->record->status, [TaxRefundStatus::Pending, TaxRefundStatus::IdentityVerification, TaxRefundStatus::Processing]))
                    ->requiresConfirmation()
                    ->modalHeading('Approve Tax Refund')
                    ->modalDescription('Are you sure you want to approve this tax refund? Ensure all documents and identity have been verified.')
                    ->action(function () {
                        $this->record->update([
                            'status' => TaxRefundStatus::Approved,
                            'approved_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Tax refund approved')
                            ->success()
                            ->send();
                    }),

                Action::make('complete')
                    ->label('Complete & Disburse')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn () => $this->record->status === TaxRefundStatus::Approved)
                    ->requiresConfirmation()
                    ->modalHeading('Complete Tax Refund')
                    ->modalDescription('This will mark the refund as completed and credit $'.number_format($this->record->refund_amount, 2).' to the taxpayer\'s bank account.')
                    ->action(function () {
                        $this->record->update([
                            'status' => TaxRefundStatus::Completed,
                            'completed_at' => now(),
                        ]);

                        if ($this->record->bankAccount) {
                            $this->record->bankAccount->increment('balance', $this->record->refund_amount * 100);
                        }

                        Notification::make()
                            ->title('Tax refund completed')
                            ->body('Amount has been credited to the taxpayer\'s account.')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => ! in_array($this->record->status, [TaxRefundStatus::Rejected, TaxRefundStatus::Completed]))
                    ->modalWidth(Width::Medium)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Provide a detailed reason for rejecting this refund request...'),
                    ])
                    ->action(function (array $data) {
                        $this->record->update([
                            'status' => TaxRefundStatus::Rejected,
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()
                            ->title('Tax refund rejected')
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
                Section::make('Taxpayer Information')
                    ->description('Personal and account details of the taxpayer')
                    ->icon('heroicon-o-user')
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
                                    ->label('Taxpayer Name'),

                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->copyable(),

                                TextEntry::make('bankAccount.account_number')
                                    ->label('Deposit Account')
                                    ->copyable(),

                                TextEntry::make('ssn_tin')
                                    ->label('SSN/TIN (Last 4)')
                                    ->formatStateUsing(fn (?string $state) => $state ? '***-**-'.$state : '-'),
                            ]),
                    ]),

                Section::make('Tax Filing Details')
                    ->description('Information from the tax return')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('tax_year')
                                    ->label('Tax Year')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('filing_status')
                                    ->label('Filing Status')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'single' => 'Single',
                                        'married_filing_jointly' => 'Married Filing Jointly',
                                        'married_filing_separately' => 'Married Filing Separately',
                                        'head_of_household' => 'Head of Household',
                                        'qualifying_widow' => 'Qualifying Widow(er)',
                                        default => $state ?? '-',
                                    }),

                                TextEntry::make('employer_name')
                                    ->label('Employer')
                                    ->placeholder('Not provided'),

                                TextEntry::make('employer_ein')
                                    ->label('Employer EIN')
                                    ->placeholder('Not provided'),

                                TextEntry::make('state')
                                    ->label('State')
                                    ->placeholder('Not provided'),

                                TextEntry::make('irs_reference_number')
                                    ->label('IRS Reference')
                                    ->copyable()
                                    ->placeholder('Not provided'),
                            ]),
                    ]),

                Section::make('Income & Withholding')
                    ->description('Financial details from W-2 and tax return')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('gross_income')
                                    ->label('Gross Income')
                                    ->money('USD')
                                    ->placeholder('$0.00'),

                                TextEntry::make('federal_withheld')
                                    ->label('Federal Tax Withheld')
                                    ->money('USD')
                                    ->placeholder('$0.00'),

                                TextEntry::make('state_withheld')
                                    ->label('State Tax Withheld')
                                    ->money('USD')
                                    ->placeholder('$0.00'),

                                TextEntry::make('refund_amount')
                                    ->label('Refund Amount')
                                    ->money('USD')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->color('success'),
                            ]),
                    ]),

                Section::make('ID.me Verification')
                    ->description('Identity verification status')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                IconEntry::make('idme_verified')
                                    ->label('Verification Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-badge')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                TextEntry::make('idme_verification_id')
                                    ->label('Verification ID')
                                    ->placeholder('Not verified')
                                    ->copyable(),

                                TextEntry::make('idme_verified_at')
                                    ->label('Verified At')
                                    ->dateTime('F d, Y H:i')
                                    ->placeholder('Not verified'),
                            ]),
                    ]),

                Section::make('Status & Processing')
                    ->description('Application status and processing timeline')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Current Status')
                                    ->badge()
                                    ->formatStateUsing(fn (TaxRefundStatus $state): string => $state->label())
                                    ->color(fn (TaxRefundStatus $state): string => $state->color()),

                                TextEntry::make('submitted_at')
                                    ->label('Submitted')
                                    ->dateTime('F d, Y H:i')
                                    ->placeholder('Not submitted'),

                                TextEntry::make('processed_at')
                                    ->label('Processing Started')
                                    ->dateTime('F d, Y H:i')
                                    ->placeholder('Not started'),

                                TextEntry::make('approved_at')
                                    ->label('Approved')
                                    ->dateTime('F d, Y H:i')
                                    ->placeholder('Not approved'),

                                TextEntry::make('completed_at')
                                    ->label('Completed')
                                    ->dateTime('F d, Y H:i')
                                    ->placeholder('Not completed'),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('F d, Y H:i'),
                            ]),

                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->columnSpanFull()
                            ->visible(fn () => $this->record->status === TaxRefundStatus::Rejected)
                            ->color('danger'),
                    ]),
            ]);
    }
}
