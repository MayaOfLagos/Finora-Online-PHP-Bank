<?php

namespace App\Filament\Resources\DomesticTransfers\Pages;

use App\Filament\Resources\DomesticTransfers\DomesticTransferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDomesticTransfer extends EditRecord
{
    protected static string $resource = DomesticTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
