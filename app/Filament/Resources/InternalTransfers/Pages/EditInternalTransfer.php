<?php

namespace App\Filament\Resources\InternalTransfers\Pages;

use App\Filament\Resources\InternalTransfers\InternalTransferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInternalTransfer extends EditRecord
{
    protected static string $resource = InternalTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
