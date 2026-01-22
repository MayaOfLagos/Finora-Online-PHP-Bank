<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bank account details')
                    ->schema([
                        TextInput::make('uuid')
                            ->label('UUID')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required(),
                        Select::make('account_type_id')
                            ->relationship('accountType', 'name')
                            ->required(),
                        TextInput::make('account_number')
                            ->required(),
                        TextInput::make('balance')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('currency')
                            ->required()
                            ->default('USD'),
                        Toggle::make('is_primary')
                            ->required(),
                        Toggle::make('is_active')
                            ->required(),
                        DateTimePicker::make('opened_at')
                            ->required(),
                        DateTimePicker::make('closed_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
