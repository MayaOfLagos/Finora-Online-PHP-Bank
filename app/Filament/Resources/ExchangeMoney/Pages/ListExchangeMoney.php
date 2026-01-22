<?php

namespace App\Filament\Resources\ExchangeMoney\Pages;

use App\Filament\Resources\ExchangeMoney\ExchangeMoneyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExchangeMoney extends ListRecords
{
    protected static string $resource = ExchangeMoneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
