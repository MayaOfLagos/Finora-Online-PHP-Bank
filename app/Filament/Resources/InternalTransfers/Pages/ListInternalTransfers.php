<?php

namespace App\Filament\Resources\InternalTransfers\Pages;

use App\Filament\Resources\InternalTransfers\InternalTransferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInternalTransfers extends ListRecords
{
    protected static string $resource = InternalTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
