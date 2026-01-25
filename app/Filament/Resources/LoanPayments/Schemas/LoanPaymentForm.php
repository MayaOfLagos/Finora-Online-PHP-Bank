<?php

namespace App\Filament\Resources\LoanPayments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LoanPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('loan_id')
                    ->relationship('loan', 'id')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                DatePicker::make('payment_date')
                    ->required(),
                TextInput::make('payment_method'),
                TextInput::make('reference_number')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('completed'),
            ]);
    }
}
