<?php

namespace App\Filament\Resources\PaymentGateways\Pages;

use App\Enums\PaymentGatewayType;
use App\Filament\Resources\PaymentGateways\PaymentGatewayResource;
use App\Models\PaymentGateway;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentGateways extends ListRecords
{
    protected static string $resource = PaymentGatewayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_automatic')
                ->label('Add Automatic Gateway')
                ->icon('heroicon-o-bolt')
                ->color('primary')
                ->modalWidth(Width::TwoExtraLarge)
                ->form([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Gateway Name')
                            ->required()
                            ->placeholder('e.g., PayPal, Stripe'),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(PaymentGateway::class, 'code')
                            ->placeholder('e.g., paypal, stripe')
                            ->helperText('Unique identifier for this gateway'),
                    ]),

                    FileUpload::make('logo')
                        ->label('Gateway Logo')
                        ->image()
                        ->directory('payment-gateways')
                        ->imageEditor()
                        ->nullable(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->nullable(),

                    Grid::make(2)->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Toggle::make('is_test_mode')
                            ->label('Test Mode')
                            ->default(true),
                    ]),

                    KeyValue::make('credentials')
                        ->label('API Credentials')
                        ->helperText('Enter API keys, secrets, etc.')
                        ->keyLabel('Credential Name')
                        ->valueLabel('Value')
                        ->addButtonLabel('Add Credential')
                        ->reorderable()
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $data['type'] = PaymentGatewayType::AUTOMATIC->value;
                    PaymentGateway::create($data);

                    Notification::make()
                        ->title('Automatic Gateway Added')
                        ->success()
                        ->send();
                }),

            Action::make('add_manual')
                ->label('Add Manual Gateway')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->modalWidth(Width::TwoExtraLarge)
                ->form([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Gateway Name')
                            ->required()
                            ->placeholder('e.g., Bank Transfer, Cash Deposit'),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(PaymentGateway::class, 'code')
                            ->placeholder('e.g., bank_transfer')
                            ->helperText('Unique identifier for this gateway'),
                    ]),

                    FileUpload::make('logo')
                        ->label('Gateway Logo')
                        ->image()
                        ->directory('payment-gateways')
                        ->imageEditor()
                        ->nullable(),

                    Textarea::make('description')
                        ->label('Instructions')
                        ->rows(4)
                        ->required()
                        ->helperText('Provide instructions for users on how to complete the payment'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    KeyValue::make('settings')
                        ->label('Bank Details')
                        ->helperText('Add bank account details or payment information')
                        ->keyLabel('Field Name')
                        ->valueLabel('Value')
                        ->addButtonLabel('Add Field')
                        ->reorderable()
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $data['type'] = PaymentGatewayType::MANUAL->value;
                    $data['is_test_mode'] = false;
                    PaymentGateway::create($data);

                    Notification::make()
                        ->title('Manual Gateway Added')
                        ->success()
                        ->send();
                }),

            Action::make('add_crypto')
                ->label('Add Crypto Gateway')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->modalWidth(Width::TwoExtraLarge)
                ->form([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Cryptocurrency Name')
                            ->required()
                            ->placeholder('e.g., Bitcoin, Ethereum'),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(PaymentGateway::class, 'code')
                            ->placeholder('e.g., BTC, ETH')
                            ->helperText('Currency code'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('currency')
                            ->label('Currency Symbol')
                            ->placeholder('₿, Ξ')
                            ->nullable(),

                        FileUpload::make('logo')
                            ->label('Crypto Logo')
                            ->image()
                            ->directory('payment-gateways')
                            ->imageEditor()
                            ->nullable(),
                    ]),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->nullable(),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    KeyValue::make('settings')
                        ->label('Wallet Details')
                        ->helperText('Add wallet addresses for different networks')
                        ->keyLabel('Network/Chain')
                        ->valueLabel('Wallet Address')
                        ->addButtonLabel('Add Wallet')
                        ->reorderable()
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $data['type'] = PaymentGatewayType::CRYPTO->value;
                    $data['is_test_mode'] = false;
                    PaymentGateway::create($data);

                    Notification::make()
                        ->title('Crypto Gateway Added')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Gateways')
                ->badge(PaymentGateway::count()),

            'automatic' => Tab::make('Automatic')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', PaymentGatewayType::AUTOMATIC->value))
                ->badge(PaymentGateway::where('type', PaymentGatewayType::AUTOMATIC->value)->count()),

            'manual' => Tab::make('Manual')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', PaymentGatewayType::MANUAL->value))
                ->badge(PaymentGateway::where('type', PaymentGatewayType::MANUAL->value)->count()),

            'crypto' => Tab::make('Crypto')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', PaymentGatewayType::CRYPTO->value))
                ->badge(PaymentGateway::where('type', PaymentGatewayType::CRYPTO->value)->count()),
        ];
    }
}
