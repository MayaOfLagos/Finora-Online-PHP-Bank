<?php

namespace App\Filament\Resources\CryptoDeposits\Pages;

use App\Filament\Resources\CryptoDeposits\CryptoDepositResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCryptoDeposit extends CreateRecord
{
    protected static string $resource = CryptoDepositResource::class;
}
