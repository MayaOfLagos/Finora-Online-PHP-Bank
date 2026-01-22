<?php

namespace App\Filament\Resources\TaxRefunds\Pages;

use App\Filament\Resources\TaxRefunds\TaxRefundResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxRefund extends CreateRecord
{
    protected static string $resource = TaxRefundResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
