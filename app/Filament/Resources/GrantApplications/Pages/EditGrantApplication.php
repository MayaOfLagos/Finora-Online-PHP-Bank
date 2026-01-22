<?php

namespace App\Filament\Resources\GrantApplications\Pages;

use App\Filament\Resources\GrantApplications\GrantApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGrantApplication extends EditRecord
{
    protected static string $resource = GrantApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
