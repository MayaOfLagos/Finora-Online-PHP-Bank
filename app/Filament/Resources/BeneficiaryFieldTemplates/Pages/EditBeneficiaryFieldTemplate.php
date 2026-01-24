<?php

namespace App\Filament\Resources\BeneficiaryFieldTemplates\Pages;

use App\Filament\Resources\BeneficiaryFieldTemplates\BeneficiaryFieldTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBeneficiaryFieldTemplate extends EditRecord
{
    protected static string $resource = BeneficiaryFieldTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
