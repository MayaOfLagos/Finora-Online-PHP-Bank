<?php

namespace App\Filament\Resources\GrantPrograms\Schemas;

use App\Enums\GrantStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GrantProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Program Information')
                    ->description('Basic details about the grant program')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Program Name')
                                ->placeholder('e.g., Small Business Development Grant')
                                ->required()
                                ->maxLength(255),

                            Select::make('status')
                                ->label('Status')
                                ->options(GrantStatus::class)
                                ->default(GrantStatus::Open)
                                ->required(),
                        ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Describe the purpose and goals of this grant program...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Funding Details')
                    ->description('Financial information and limits')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('amount')
                                ->label('Grant Amount')
                                ->numeric()
                                ->prefix('$')
                                ->placeholder('10000')
                                ->helperText('Amount in dollars (stored as cents)')
                                ->required(),

                            TextInput::make('currency')
                                ->label('Currency')
                                ->default('USD')
                                ->maxLength(3)
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('max_recipients')
                                ->label('Maximum Recipients')
                                ->numeric()
                                ->placeholder('100')
                                ->helperText('Leave empty for unlimited recipients'),

                            Toggle::make('is_renewable')
                                ->label('Renewable Grant')
                                ->helperText('Can recipients apply again after completion?')
                                ->default(false),
                        ]),
                    ]),

                Section::make('Program Timeline')
                    ->description('Application period and deadlines')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('start_date')
                                ->label('Application Start Date')
                                ->native(false)
                                ->displayFormat('M d, Y')
                                ->helperText('When applications open'),

                            DatePicker::make('end_date')
                                ->label('Application End Date')
                                ->native(false)
                                ->displayFormat('M d, Y')
                                ->helperText('Application deadline')
                                ->afterOrEqual('start_date'),
                        ]),
                    ]),

                Section::make('Eligibility & Requirements')
                    ->description('Define who can apply and what documents are needed')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->collapsible()
                    ->schema([
                        KeyValue::make('eligibility_criteria')
                            ->label('Eligibility Criteria')
                            ->keyLabel('Criterion')
                            ->valueLabel('Description')
                            ->addActionLabel('Add Criterion')
                            ->reorderable()
                            ->helperText('Define the requirements applicants must meet'),

                        KeyValue::make('required_documents')
                            ->label('Required Documents')
                            ->keyLabel('Document Type')
                            ->valueLabel('Description')
                            ->addActionLabel('Add Document')
                            ->reorderable()
                            ->helperText('List all documents applicants must submit'),
                    ]),
            ]);
    }
}
