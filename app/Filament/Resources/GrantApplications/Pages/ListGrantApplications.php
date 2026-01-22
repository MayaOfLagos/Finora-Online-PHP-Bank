<?php

namespace App\Filament\Resources\GrantApplications\Pages;

use App\Filament\Resources\GrantApplications\GrantApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGrantApplications extends ListRecords
{
    protected static string $resource = GrantApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
