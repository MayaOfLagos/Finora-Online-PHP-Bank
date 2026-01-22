<?php

namespace App\Filament\Resources\Cards\Schemas;

use App\Enums\CardStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                Select::make('bank_account_id')
                    ->relationship('bankAccount', 'account_number')
                    ->required(),
                Select::make('card_type_id')
                    ->relationship('cardType', 'name')
                    ->required(),
                TextInput::make('card_number')
                    ->required(),
                TextInput::make('card_holder_name')
                    ->required(),
                TextInput::make('expiry_month')
                    ->required(),
                TextInput::make('expiry_year')
                    ->required(),
                TextInput::make('cvv')
                    ->required(),
                TextInput::make('pin'),
                TextInput::make('spending_limit')
                    ->numeric(),
                TextInput::make('daily_limit')
                    ->numeric(),
                Select::make('status')
                    ->options(CardStatus::class)
                    ->default('active')
                    ->required(),
                Toggle::make('is_virtual')
                    ->required(),
                DateTimePicker::make('issued_at')
                    ->required(),
                DateTimePicker::make('expires_at')
                    ->required(),
                DateTimePicker::make('blocked_at'),
            ]);
    }
}
