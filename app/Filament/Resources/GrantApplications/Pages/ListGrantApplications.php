<?php

namespace App\Filament\Resources\GrantApplications\Pages;

use App\Filament\Resources\GrantApplications\GrantApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGrantApplications extends ListRecords
{
    protected static string $resource = GrantApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('grant_programs')
                    ->label('Grant Programs')
                    ->icon('heroicon-o-rectangle-stack')
                    ->url(fn () => route('filament.admin.resources.grant-programs.index')),

                Action::make('grant_categories')
                    ->label('Grant Categories')
                    ->icon('heroicon-o-tag')
                    ->url(fn () => route('filament.admin.resources.grant-categories.index')),
            ])
                ->label('Manage')
                ->icon('heroicon-o-cog-6-tooth')
                ->button(),

            CreateAction::make(),
        ];
    }
}
