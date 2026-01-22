<?php

namespace App\Filament\Resources\DomesticTransfers\Pages;

use App\Filament\Resources\DomesticTransfers\DomesticTransferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDomesticTransfers extends ListRecords
{
    protected static string $resource = DomesticTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
