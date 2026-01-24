<?php

namespace App\Filament\Resources\LoanApplications\Tables;

use App\Enums\LoanStatus;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
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
