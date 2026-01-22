<?php

namespace App\Filament\Resources\TaxRefunds\Pages;

use App\Filament\Resources\TaxRefunds\TaxRefundResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxRefund extends ViewRecord
{
    protected static string $resource = TaxRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending')
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('process')
                ->label('Mark as Processed')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'approved')
                ->action(function () {
                    $this->record->update([
                        'status' => 'processing',
                        'processed_at' => now(),
                    ]);

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('complete')
                ->label('Complete')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['approved', 'processing']))
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    // Add funds to user's bank account
                    if ($this->record->bankAccount) {
                        $this->record->bankAccount->increment('balance', $this->record->refund_amount * 100);
                    }

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['pending', 'processing']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
