<?php

namespace App\Filament\Resources\MobileDeposits\Pages;

use App\Filament\Resources\MobileDeposits\MobileDepositResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMobileDeposits extends ListRecords
{
    protected static string $resource = MobileDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
