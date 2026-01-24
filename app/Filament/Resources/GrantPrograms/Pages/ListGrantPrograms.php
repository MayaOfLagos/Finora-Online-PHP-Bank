<?php

namespace App\Filament\Resources\GrantPrograms\Pages;

use App\Filament\Resources\GrantPrograms\GrantProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGrantPrograms extends ListRecords
{
    protected static string $resource = GrantProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
