<?php

namespace App\Filament\Resources\Withdrawals\Pages;

use App\Filament\Resources\Withdrawals\WithdrawalResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewWithdrawal extends ViewRecord
{
    protected static string $resource = WithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Approve Withdrawal')
                ->modalDescription('Are you sure you want to approve this withdrawal request?')
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Withdrawal Approved')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'approved_at']);
                }),

            Action::make('complete')
                ->label('Mark Completed')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn () => $this->record->status === 'approved')
                ->requiresConfirmation()
                ->modalHeading('Complete Withdrawal')
                ->modalDescription('Mark this withdrawal as completed? This means the funds have been transferred.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Withdrawal Completed')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'completed_at']);
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, ['pending', 'approved']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    // Refund the amount back to user's account if already deducted
                    if ($this->record->bank_account_id) {
                        $this->record->bankAccount?->increment('balance', $this->record->amount);
                    }

                    $this->record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    Notification::make()
                        ->title('Withdrawal Rejected')
                        ->body('Amount has been refunded to the user\'s account.')
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status', 'rejection_reason']);
                }),

            EditAction::make(),
        ];
    }
}
