<?php

namespace App\Filament\Resources\LoanTypes\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LoanTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Loan Program Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Personal Loan'),

                        TextInput::make('code')
                            ->label('Program Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('e.g., PL01')
                            ->helperText('Unique identifier for this loan program'),

                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->placeholder('Describe this loan program...'),

                        Toggle::make('is_active')
                            ->label('Active Program')
                            ->helperText('Only active programs are visible to customers')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Loan Limits')
                    ->schema([
                        TextInput::make('min_amount')
                            ->label('Minimum Amount ($)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->step(100)
                            ->prefix('$')
                            ->helperText('Amount in dollars (stored as cents internally)')
                            ->dehydrateStateUsing(fn ($state) => $state * 100)
                            ->formatStateUsing(fn ($state) => $state / 100),

                        TextInput::make('max_amount')
                            ->label('Maximum Amount ($)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->step(100)
                            ->prefix('$')
                            ->helperText('Amount in dollars (stored as cents internally)')
                            ->dehydrateStateUsing(fn ($state) => $state * 100)
                            ->formatStateUsing(fn ($state) => $state / 100),

                        TextInput::make('min_term_months')
                            ->label('Minimum Term (Months)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(360)
                            ->suffix('months'),

                        TextInput::make('max_term_months')
                            ->label('Maximum Term (Months)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(360)
                            ->suffix('months'),
                    ])
                    ->columns(2),

                Section::make('Interest Rate')
                    ->schema([
                        TextInput::make('interest_rate')
                            ->label('Annual Interest Rate (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Annual percentage rate (APR)'),
                    ])
                    ->columns(1),
            ]);
    }
}
