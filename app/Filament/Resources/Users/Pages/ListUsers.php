<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New user')
                ->modalHeading('Create user')
                ->modalWidth('2xl')
                ->model(User::class)
                ->schema(fn (Schema $schema) => UserForm::configure($schema->columns(2))),
        ];
    }
}
