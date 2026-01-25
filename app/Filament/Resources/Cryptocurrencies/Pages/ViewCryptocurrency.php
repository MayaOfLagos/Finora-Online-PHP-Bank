<?php

namespace App\Filament\Resources\Cryptocurrencies\Pages;

use App\Filament\Resources\Cryptocurrencies\CryptocurrencyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCryptocurrency extends ViewRecord
{
    protected static string $resource = CryptocurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
