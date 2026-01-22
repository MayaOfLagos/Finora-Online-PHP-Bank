<?php

namespace App\Filament\Resources\Beneficiaries\Pages;

use App\Filament\Resources\Beneficiaries\BeneficiaryResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBeneficiaries extends ListRecords
{
    protected static string $resource = BeneficiaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Add Beneficiary')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalWidth('2xl')
                ->modalHeading('Add New Beneficiary')
                ->form(fn () => BeneficiaryResource::form(\Filament\Schemas\Schema::make())->getComponents())
                ->action(function (array $data) {
                    // Check if beneficiary already exists
                    $exists = \App\Models\Beneficiary::where('user_id', $data['user_id'])
                        ->where('beneficiary_account_id', $data['beneficiary_account_id'])
                        ->exists();

                    if ($exists) {
                        Notification::make()
                            ->title('Beneficiary Already Exists')
                            ->body('This beneficiary account has already been added for this user.')
                            ->warning()
                            ->duration(5000)
                            ->send();
                        return;
                    }

                    try {
                        \App\Models\Beneficiary::create([
                            'uuid' => (string) \Illuminate\Support\Str::uuid(),
                            'user_id' => $data['user_id'],
                            'beneficiary_user_id' => $data['beneficiary_user_id'],
                            'beneficiary_account_id' => $data['beneficiary_account_id'],
                            'nickname' => $data['nickname'],
                            'is_verified' => $data['is_verified'] ?? false,
                            'is_favorite' => $data['is_favorite'] ?? false,
                            'transfer_limit' => $data['transfer_limit'] ?? null,
                            'last_used_at' => $data['last_used_at'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Beneficiary Added')
                            ->body('The beneficiary has been created successfully.')
                            ->success()
                            ->send();

                        $this->redirect(static::getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error Creating Beneficiary')
                            ->body('An error occurred: ' . $e->getMessage())
                            ->danger()
                            ->duration(7000)
                            ->send();
                    }
                }),
        ];
    }
}
