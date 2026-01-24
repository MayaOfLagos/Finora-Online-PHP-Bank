<?php

namespace App\Filament\Resources\BeneficiaryFieldTemplates\Pages;

use App\Filament\Resources\BeneficiaryFieldTemplates\BeneficiaryFieldTemplateResource;
use App\Models\BeneficiaryFieldTemplate;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBeneficiaryFieldTemplates extends ListRecords
{
    protected static string $resource = BeneficiaryFieldTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        // Temporarily disable badges to fix loading issue
        // Will add caching later
        return [
            'all' => Tab::make('All Fields'),

            'wire' => Tab::make('Wire Transfer')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('applies_to', 'wire')),

            'domestic' => Tab::make('Domestic Transfer')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('applies_to', 'domestic')),

            'internal' => Tab::make('Internal Transfer')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('applies_to', 'internal')),

            'enabled' => Tab::make('Enabled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true))
                ->badgeColor('success'),

            'disabled' => Tab::make('Disabled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', false))
                ->badgeColor('danger'),
        ];
    }
}
