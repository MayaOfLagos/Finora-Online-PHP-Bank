<?php

namespace App\Filament\Resources\MoneyRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MoneyRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Details')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('requester_id')
                                ->relationship('requester', 'email')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('Requester'),
                            Select::make('responder_id')
                                ->relationship('responder', 'email')
                                ->searchable()
                                ->preload()
                                ->label('Responder'),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('reference_number')
                                ->required()
                                ->disabled()
                                ->dehydrated()
                                ->default(fn () => 'MRQ'.date('Ymd').strtoupper(substr(uniqid(), -6))),
                            TextInput::make('amount')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->label('Amount (cents)'),
                            Select::make('currency')
                                ->options([
                                    'USD' => 'USD',
                                    'EUR' => 'EUR',
                                    'GBP' => 'GBP',
                                    'NGN' => 'NGN',
                                ])
                                ->default('USD')
                                ->required(),
                        ]),
                        Textarea::make('reason')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status & Dates')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'accepted' => 'Accepted',
                                    'rejected' => 'Rejected',
                                    'completed' => 'Completed',
                                ])
                                ->default('pending')
                                ->required(),
                            Select::make('type')
                                ->options([
                                    'personal' => 'Personal',
                                    'business' => 'Business',
                                    'split' => 'Split Bill',
                                ])
                                ->default('personal')
                                ->required(),
                        ]),
                        Grid::make(3)->schema([
                            DateTimePicker::make('accepted_at')
                                ->label('Accepted At'),
                            DateTimePicker::make('completed_at')
                                ->label('Completed At'),
                            DatePicker::make('expires_at')
                                ->label('Expires At'),
                        ]),
                        Textarea::make('rejection_reason')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                    ]),
            ]);
    }
}
