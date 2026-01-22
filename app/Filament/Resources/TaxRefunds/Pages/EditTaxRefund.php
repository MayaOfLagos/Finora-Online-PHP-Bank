<?php

namespace App\Filament\Resources\TaxRefunds\Pages;

use App\Filament\Resources\TaxRefunds\TaxRefundResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxRefund extends EditRecord
{
    protected static string $resource = TaxRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
