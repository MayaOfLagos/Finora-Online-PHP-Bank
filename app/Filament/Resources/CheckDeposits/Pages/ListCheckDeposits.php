<?php

namespace App\Filament\Resources\CheckDeposits\Pages;

use App\Filament\Resources\CheckDeposits\CheckDepositResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCheckDeposits extends ListRecords
{
    protected static string $resource = CheckDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
