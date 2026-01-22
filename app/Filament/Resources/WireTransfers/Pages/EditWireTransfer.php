<?php

namespace App\Filament\Resources\WireTransfers\Pages;

use App\Filament\Resources\WireTransfers\WireTransferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWireTransfer extends EditRecord
{
    protected static string $resource = WireTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
