<?php

namespace App\Filament\Resources\ExchangeMoney\Pages;

use App\Filament\Resources\ExchangeMoney\ExchangeMoneyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExchangeMoney extends ViewRecord
{
    protected static string $resource = ExchangeMoneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
