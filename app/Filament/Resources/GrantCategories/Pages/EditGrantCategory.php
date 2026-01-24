<?php

namespace App\Filament\Resources\GrantCategories\Pages;

use App\Filament\Resources\GrantCategories\GrantCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGrantCategory extends EditRecord
{
    protected static string $resource = GrantCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
