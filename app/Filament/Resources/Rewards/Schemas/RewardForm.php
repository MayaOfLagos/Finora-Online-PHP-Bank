<?php

namespace App\Filament\Resources\Rewards\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reward Details')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('user_id')
                                ->relationship('user', 'email')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('User'),
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('points')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->prefix('pts'),
                            Select::make('type')
                                ->options([
                                    'referral' => 'Referral',
                                    'cashback' => 'Cashback',
                                    'loyalty' => 'Loyalty',
                                    'bonus' => 'Bonus',
                                    'achievement' => 'Achievement',
                                ])
                                ->default('loyalty')
                                ->required(),
                            Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'earned' => 'Earned',
                                    'redeemed' => 'Redeemed',
                                    'expired' => 'Expired',
                                ])
                                ->default('pending')
                                ->required(),
                        ]),
                    ]),

                Section::make('Dates & Source')
                    ->schema([
                        Grid::make(3)->schema([
                            DatePicker::make('earned_date')
                                ->label('Earned Date')
                                ->default(now()),
                            DatePicker::make('expiry_date')
                                ->label('Expiry Date'),
                            DateTimePicker::make('redeemed_at')
                                ->label('Redeemed At'),
                        ]),
                        Textarea::make('source')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Source of the reward (e.g., referral link, action type)'),
                    ]),

                Section::make('Metadata')
                    ->schema([
                        Textarea::make('metadata')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('JSON format for additional data'),
                    ])
                    ->collapsed(),
            ]);
    }
}
