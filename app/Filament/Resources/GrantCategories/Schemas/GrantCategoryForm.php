<?php

namespace App\Filament\Resources\GrantCategories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GrantCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Category Details')
                    ->description('Basic information about the grant category')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Category Name')
                                ->placeholder('e.g., Education, Healthcare, Small Business')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            TextInput::make('sort_order')
                                ->label('Sort Order')
                                ->numeric()
                                ->default(0)
                                ->helperText('Lower numbers appear first'),
                        ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Describe the types of grants that fall under this category...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Display Settings')
                    ->description('Configure how this category appears in the system')
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        Grid::make(2)->schema([
                            ColorPicker::make('color')
                                ->label('Category Color')
                                ->default('#3B82F6')
                                ->helperText('Used for badges and visual indicators'),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true)
                                ->helperText('Inactive categories will not be available for new grants'),
                        ]),
                    ]),
            ]);
    }
}
