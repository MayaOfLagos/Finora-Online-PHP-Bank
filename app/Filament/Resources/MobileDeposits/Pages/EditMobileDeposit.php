<?php

namespace App\Filament\Resources\MobileDeposits\Pages;

use App\Filament\Resources\MobileDeposits\MobileDepositResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMobileDeposit extends EditRecord
{
    protected static string $resource = MobileDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
