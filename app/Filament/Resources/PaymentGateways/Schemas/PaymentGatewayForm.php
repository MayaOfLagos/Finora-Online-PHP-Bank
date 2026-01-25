<?php

namespace App\Filament\Resources\PaymentGateways\Schemas;

use App\Enums\PaymentGatewayType;
use Filament\Forms\Components\Select;
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
                Select::make('type')
                    ->label('Gateway Type')
                    ->options([
                        PaymentGatewayType::AUTOMATIC->value => PaymentGatewayType::AUTOMATIC->getLabel(),
                        PaymentGatewayType::MANUAL->value => PaymentGatewayType::MANUAL->getLabel(),
                        PaymentGatewayType::CRYPTO->value => PaymentGatewayType::CRYPTO->getLabel(),
                    ])
                    ->native(false)
                    ->helperText('Automatic (API), Manual (Bank Transfer), or Crypto')
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
