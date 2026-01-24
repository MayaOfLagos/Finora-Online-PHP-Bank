<?php

namespace App\Filament\Resources\TaxRefunds\Schemas;

use App\Enums\TaxRefundStatus;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TaxRefundTable
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
                    ->label('Taxpayer')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('tax_year')
                    ->label('Tax Year')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('filing_status')
                    ->label('Filing Status')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'single' => 'Single',
                        'married_filing_jointly' => 'MFJ',
                        'married_filing_separately' => 'MFS',
                        'head_of_household' => 'HOH',
                        'qualifying_widow' => 'QW',
                        default => $state ?? '-',
                    })
                    ->toggleable(),

                TextColumn::make('refund_amount')
                    ->label('Refund Amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                IconColumn::make('idme_verified')
                    ->label('ID.me')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (TaxRefundStatus $state): string => $state->label())
                    ->color(fn (TaxRefundStatus $state): string => $state->color())
                    ->icon(fn (TaxRefundStatus $state): string => $state->icon())
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(TaxRefundStatus::class)
                    ->label('Status'),
                SelectFilter::make('tax_year')
                    ->options(fn () => \App\Models\TaxRefund::query()
                        ->distinct()
                        ->pluck('tax_year', 'tax_year')
                        ->toArray())
                    ->label('Tax Year'),
                TernaryFilter::make('idme_verified')
                    ->label('ID.me Verified')
                    ->placeholder('All')
                    ->trueLabel('Verified')
                    ->falseLabel('Not Verified'),
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('info'),

                    EditAction::make()
                        ->color('primary'),

                    Action::make('verify_identity')
                        ->label('Request ID.me Verification')
                        ->icon('heroicon-o-identification')
                        ->color('warning')
                        ->visible(fn ($record) => ! $record->idme_verified && in_array($record->status, [TaxRefundStatus::Pending, TaxRefundStatus::IdentityVerification]))
                        ->requiresConfirmation()
                        ->modalHeading('Request ID.me Verification')
                        ->modalDescription('Send a request to the taxpayer to verify their identity through ID.me.')
                        ->action(function ($record) {
                            $record->update(['status' => TaxRefundStatus::IdentityVerification]);
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
                        ->visible(fn ($record) => ! $record->idme_verified)
                        ->requiresConfirmation()
                        ->modalHeading('Confirm ID.me Verification')
                        ->modalDescription('Confirm that the taxpayer has successfully verified their identity through ID.me.')
                        ->action(function ($record) {
                            $record->update([
                                'idme_verified' => true,
                                'idme_verified_at' => now(),
                            ]);
                            Notification::make()
                                ->title('Identity verified')
                                ->success()
                                ->send();
                        }),

                    Action::make('approve')
                        ->label('Approve Refund')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->idme_verified && in_array($record->status, [TaxRefundStatus::Pending, TaxRefundStatus::IdentityVerification, TaxRefundStatus::Processing]))
                        ->requiresConfirmation()
                        ->modalHeading('Approve Tax Refund')
                        ->modalDescription('Are you sure you want to approve this tax refund? Ensure all documents have been verified.')
                        ->action(function ($record) {
                            $record->update([
                                'status' => TaxRefundStatus::Approved,
                                'approved_at' => now(),
                            ]);
                            Notification::make()
                                ->title('Tax refund approved')
                                ->success()
                                ->send();
                        }),

                    Action::make('process')
                        ->label('Start Processing')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->visible(fn ($record) => $record->status === TaxRefundStatus::Pending || $record->status === TaxRefundStatus::IdentityVerification)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'status' => TaxRefundStatus::Processing,
                                'processed_at' => now(),
                            ]);
                            Notification::make()
                                ->title('Refund processing started')
                                ->info()
                                ->send();
                        }),

                    Action::make('complete')
                        ->label('Complete & Disburse')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === TaxRefundStatus::Approved)
                        ->requiresConfirmation()
                        ->modalHeading('Complete Tax Refund')
                        ->modalDescription('This will mark the refund as completed and credit the amount to the taxpayer\'s bank account.')
                        ->action(function ($record) {
                            $record->update([
                                'status' => TaxRefundStatus::Completed,
                                'completed_at' => now(),
                            ]);

                            if ($record->bankAccount) {
                                $record->bankAccount->increment('balance', $record->refund_amount * 100);
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
                        ->visible(fn ($record) => ! in_array($record->status, [TaxRefundStatus::Rejected, TaxRefundStatus::Completed]))
                        ->modalWidth(Width::Medium)
                        ->form([
                            Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->rows(3)
                                ->placeholder('Provide a detailed reason for rejecting this refund request...'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
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
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
