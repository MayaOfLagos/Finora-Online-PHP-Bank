<?php

namespace App\Filament\Resources\TaxRefunds\Pages;

use App\Filament\Resources\TaxRefunds\TaxRefundResource;
use Filament\Resources\Pages\ListRecords;

class ListTaxRefunds extends ListRecords
{
    protected static string $resource = TaxRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
