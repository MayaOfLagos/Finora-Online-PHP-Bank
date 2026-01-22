<?php

namespace App\Filament\Resources\MoneyRequests\Pages;

use App\Filament\Resources\MoneyRequests\MoneyRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMoneyRequest extends ViewRecord
{
    protected static string $resource = MoneyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
