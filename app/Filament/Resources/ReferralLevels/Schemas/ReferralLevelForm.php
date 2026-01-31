<?php

namespace App\Filament\Resources\ReferralLevels\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReferralLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Level Information')
                    ->description('Configure the referral level details')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('level')
                                ->label('Level Number')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(20)
                                ->unique(ignoreRecord: true)
                                ->helperText('Unique level number (1, 2, 3...)'),
                            TextInput::make('name')
                                ->label('Level Name')
                                ->required()
                                ->maxLength(50)
                                ->placeholder('e.g., Bronze, Silver, Gold'),
                        ]),
                        Grid::make(2)->schema([
                            ColorPicker::make('color')
                                ->label('Badge Color')
                                ->helperText('Color for the level badge'),
                            TextInput::make('icon')
                                ->label('Icon (optional)')
                                ->placeholder('heroicon-o-star')
                                ->helperText('Heroicon name for the level'),
                        ]),
                    ]),

                Section::make('Reward Configuration')
                    ->description('Set up rewards for this level')
                    ->icon('heroicon-o-gift')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('reward_type')
                                ->label('Reward Type')
                                ->options([
                                    'fixed' => 'Fixed Amount',
                                    'percentage' => 'Percentage of Base',
                                ])
                                ->default('fixed')
                                ->required()
                                ->live()
                                ->helperText('How rewards are calculated'),
                            TextInput::make('reward_amount')
                                ->label('Reward Amount')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->suffix(fn ($get) => $get('reward_type') === 'percentage' ? '%' : 'cents')
                                ->helperText(fn ($get) => $get('reward_type') === 'percentage'
                                    ? 'Percentage of base amount (e.g., 10 for 10%)'
                                    : 'Amount in cents (e.g., 500 for $5.00)'),
                        ]),
                        TextInput::make('min_referrals_required')
                            ->label('Minimum Referrals Required')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Number of completed referrals needed to reach this level'),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive levels will not be used in calculations'),
                    ]),
            ]);
    }
}
