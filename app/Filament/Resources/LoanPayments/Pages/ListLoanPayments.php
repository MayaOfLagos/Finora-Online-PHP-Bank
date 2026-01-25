<?php

namespace App\Filament\Resources\LoanPayments\Pages;

use App\Filament\Resources\LoanPayments\LoanPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoanPayments extends ListRecords
{
    protected static string $resource = LoanPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
