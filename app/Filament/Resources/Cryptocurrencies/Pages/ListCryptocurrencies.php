<?php

namespace App\Filament\Resources\Cryptocurrencies\Pages;

use App\Filament\Resources\Cryptocurrencies\CryptocurrencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCryptocurrencies extends ListRecords
{
    protected static string $resource = CryptocurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
