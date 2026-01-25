<?php

namespace App\Filament\Resources\Cryptocurrencies\Pages;

use App\Filament\Resources\Cryptocurrencies\CryptocurrencyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCryptocurrency extends CreateRecord
{
    protected static string $resource = CryptocurrencyResource::class;
}
