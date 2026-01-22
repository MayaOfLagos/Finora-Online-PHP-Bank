<?php

namespace App\Filament\Resources\CryptoDeposits\Pages;

use App\Filament\Resources\CryptoDeposits\CryptoDepositResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCryptoDeposit extends EditRecord
{
    protected static string $resource = CryptoDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
