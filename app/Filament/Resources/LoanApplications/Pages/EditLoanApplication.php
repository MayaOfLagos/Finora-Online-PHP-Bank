<?php

namespace App\Filament\Resources\LoanApplications\Pages;

use App\Filament\Resources\LoanApplications\LoanApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLoanApplication extends EditRecord
{
    protected static string $resource = LoanApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
