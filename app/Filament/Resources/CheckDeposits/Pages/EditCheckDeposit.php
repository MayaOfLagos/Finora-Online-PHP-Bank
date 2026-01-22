<?php

namespace App\Filament\Resources\CheckDeposits\Pages;

use App\Filament\Resources\CheckDeposits\CheckDepositResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCheckDeposit extends EditRecord
{
    protected static string $resource = CheckDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
