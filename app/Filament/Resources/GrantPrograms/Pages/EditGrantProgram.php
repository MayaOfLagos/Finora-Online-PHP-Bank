<?php

namespace App\Filament\Resources\GrantPrograms\Pages;

use App\Filament\Resources\GrantPrograms\GrantProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGrantProgram extends EditRecord
{
    protected static string $resource = GrantProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
