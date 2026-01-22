<?php

namespace App\Filament\Resources\PaymentGateways\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentGatewayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('logo'),
                Textarea::make('credentials')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_test_mode')
                    ->required(),
            ]);
    }
}
