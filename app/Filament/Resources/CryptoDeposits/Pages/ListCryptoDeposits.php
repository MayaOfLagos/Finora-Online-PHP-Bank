<?php

namespace App\Filament\Resources\CryptoDeposits\Pages;

use App\Filament\Resources\CryptoDeposits\CryptoDepositResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCryptoDeposits extends ListRecords
{
    protected static string $resource = CryptoDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
