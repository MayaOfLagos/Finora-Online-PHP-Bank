<?php

namespace App\Filament\Resources\CheckDeposits\Schemas;

use App\Enums\DepositStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CheckDepositForm
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
                TextInput::make('reference_number')
                    ->required(),
                TextInput::make('check_number'),
                FileUpload::make('check_front_image')
                    ->image()
                    ->required(),
                FileUpload::make('check_back_image')
                    ->image(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                Select::make('status')
                    ->options(DepositStatus::class)
                    ->default('pending')
                    ->required(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                DateTimePicker::make('hold_until'),
                DateTimePicker::make('approved_at'),
                TextInput::make('approved_by')
                    ->numeric(),
            ]);
    }
}
