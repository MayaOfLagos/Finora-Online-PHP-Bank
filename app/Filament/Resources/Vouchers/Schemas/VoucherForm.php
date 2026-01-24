<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Voucher Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('code')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->default(fn () => strtoupper(substr(md5(uniqid()), 0, 10)))
                                ->helperText('Auto-generated if left empty'),
                            Select::make('user_id')
                                ->relationship('user', 'email')
                                ->searchable()
                                ->preload()
                                ->label('Assigned User')
                                ->helperText('Leave empty for general voucher'),
                        ]),
                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('amount')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->label('Amount (cents)')
                                ->helperText('Enter amount in cents'),
                            Select::make('currency')
                                ->options([
                                    'USD' => 'USD',
                                    'EUR' => 'EUR',
                                    'GBP' => 'GBP',
                                    'NGN' => 'NGN',
                                ])
                                ->default('USD')
                                ->required(),
                            Select::make('type')
                                ->options([
                                    'discount' => 'Discount',
                                    'cashback' => 'Cashback',
                                    'bonus' => 'Bonus',
                                    'referral' => 'Referral',
                                ])
                                ->default('bonus')
                                ->required(),
                        ]),
                    ]),

                Section::make('Status & Usage')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('status')
                                ->options([
                                    'active' => 'Active',
                                    'used' => 'Used',
                                    'expired' => 'Expired',
                                ])
                                ->default('active')
                                ->required(),
                            TextInput::make('usage_limit')
                                ->numeric()
                                ->default(1)
                                ->helperText('Max times this voucher can be used'),
                            TextInput::make('times_used')
                                ->numeric()
                                ->default(0)
                                ->disabled(),
                        ]),
                        Grid::make(2)->schema([
                            Toggle::make('is_used')
                                ->label('Is Used')
                                ->default(false),
                            DatePicker::make('expires_at')
                                ->label('Expiry Date'),
                        ]),
                        DateTimePicker::make('used_at')
                            ->label('Used At')
                            ->disabled(),
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
