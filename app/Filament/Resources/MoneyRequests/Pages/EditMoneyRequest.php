<?php

namespace App\Filament\Resources\MoneyRequests\Pages;

use App\Filament\Resources\MoneyRequests\MoneyRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMoneyRequest extends EditRecord
{
    protected static string $resource = MoneyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
