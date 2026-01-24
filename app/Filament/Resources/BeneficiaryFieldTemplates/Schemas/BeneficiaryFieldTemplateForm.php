<?php

namespace App\Filament\Resources\BeneficiaryFieldTemplates\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeneficiaryFieldTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Field Configuration')
                    ->schema([
                        TextInput::make('field_key')
                            ->label('Field Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('e.g., beneficiary_name')
                            ->helperText('Unique identifier for this field (snake_case)'),
                        TextInput::make('field_label')
                            ->label('Field Label')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Beneficiary Name')
                            ->helperText('Display label shown to users'),
                        Select::make('field_type')
                            ->label('Field Type')
                            ->required()
                            ->options([
                                'text' => 'Text Input',
                                'textarea' => 'Textarea',
                                'select' => 'Select Dropdown',
                                'country' => 'Country Selector',
                            ])
                            ->default('text')
                            ->live(),
                        Select::make('applies_to')
                            ->label('Applies To Transfer Type')
                            ->required()
                            ->options([
                                'wire' => 'Wire Transfer',
                                'domestic' => 'Domestic Transfer',
                                'internal' => 'Internal Transfer',
                                'all' => 'All Transfer Types',
                            ])
                            ->default('all'),
                    ])
                    ->columns(2),

                Section::make('Field Properties')
                    ->schema([
                        Toggle::make('is_required')
                            ->label('Required Field')
                            ->default(false)
                            ->helperText('Users must fill this field'),
                        Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true)
                            ->helperText('Show this field in forms'),
                        TextInput::make('display_order')
                            ->label('Display Order')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first'),
                    ])
                    ->columns(3),

                Section::make('Field Options')
                    ->schema([
                        TextInput::make('placeholder')
                            ->label('Placeholder Text')
                            ->maxLength(255)
                            ->placeholder('e.g., Enter beneficiary name')
                            ->columnSpanFull(),
                        TextInput::make('helper_text')
                            ->label('Helper Text')
                            ->maxLength(255)
                            ->placeholder('Additional guidance for users')
                            ->columnSpanFull(),
                        KeyValue::make('options')
                            ->label('Select Options (for select field type)')
                            ->keyLabel('Value')
                            ->valueLabel('Label')
                            ->visible(fn (callable $get) => $get('field_type') === 'select')
                            ->helperText('Define options for select dropdown')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
