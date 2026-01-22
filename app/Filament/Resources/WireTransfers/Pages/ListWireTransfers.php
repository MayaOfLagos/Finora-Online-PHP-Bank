<?php

namespace App\Filament\Resources\WireTransfers\Pages;

use App\Filament\Resources\WireTransfers\WireTransferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWireTransfers extends ListRecords
{
    protected static string $resource = WireTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
