<?php

namespace App\Filament\Resources\MoneyRequests\Pages;

use App\Filament\Resources\MoneyRequests\MoneyRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMoneyRequests extends ListRecords
{
    protected static string $resource = MoneyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
