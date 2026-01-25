<?php

namespace App\Filament\Resources\Cryptocurrencies\Pages;

use App\Filament\Resources\Cryptocurrencies\CryptocurrencyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCryptocurrency extends EditRecord
{
    protected static string $resource = CryptocurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
