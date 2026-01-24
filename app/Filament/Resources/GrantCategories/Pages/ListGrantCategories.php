<?php

namespace App\Filament\Resources\GrantCategories\Pages;

use App\Filament\Resources\GrantCategories\GrantCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGrantCategories extends ListRecords
{
    protected static string $resource = GrantCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
