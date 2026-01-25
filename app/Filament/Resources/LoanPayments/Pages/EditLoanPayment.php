<?php

namespace App\Filament\Resources\LoanPayments\Pages;

use App\Filament\Resources\LoanPayments\LoanPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLoanPayment extends EditRecord
{
    protected static string $resource = LoanPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
