<?php

namespace App\Filament\Resources\PaymentGateways\Tables;

use App\Enums\PaymentGatewayType;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentGatewaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name))
                    ->size(40),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (PaymentGatewayType $state): string => $state->getLabel())
                    ->colors([
                        'primary' => PaymentGatewayType::AUTOMATIC->value,
                        'warning' => PaymentGatewayType::MANUAL->value,
                        'success' => PaymentGatewayType::CRYPTO->value,
                    ]),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('is_test_mode')
                    ->label('Test Mode')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('View Details')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalWidth(Width::TwoExtraLarge)
                        ->form(fn ($record) => self::getViewForm($record))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),

                    Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->modalWidth(Width::TwoExtraLarge)
                        ->fillForm(fn ($record) => $record->toArray())
                        ->form(fn ($record) => self::getEditForm($record))
                        ->action(function ($record, array $data) {
                            $record->update($data);
                            Notification::make()
                                ->title('Gateway Updated')
                                ->success()
                                ->send();
                        }),

                    Action::make('toggle_status')
                        ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['is_active' => ! $record->is_active]);
                            Notification::make()
                                ->title($record->is_active ? 'Gateway Activated' : 'Gateway Deactivated')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Gateway')
                        ->modalDescription('Are you sure you want to delete this payment gateway? This action cannot be undone.')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Gateway Deleted')
                        ),
                ])
                    ->button()
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getViewForm($record): array
    {
        $sections = [
            \Filament\Schemas\Components\Section::make('Basic Information')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name')
                            ->label('Gateway Name'),

                        TextEntry::make('code')
                            ->label('Gateway Code')
                            ->badge()
                            ->color('primary'),
                    ]),

                    Grid::make(2)->schema([
                        TextEntry::make('type')
                            ->label('Gateway Type')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                PaymentGatewayType::AUTOMATIC => 'success',
                                PaymentGatewayType::MANUAL => 'warning',
                                PaymentGatewayType::CRYPTO => 'info',
                            })
                            ->formatStateUsing(fn ($state) => $state->getLabel()),

                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),
                    ]),
                ])
                ->collapsible(),
        ];

        if ($record->logo) {
            $sections[] = \Filament\Schemas\Components\Section::make('Logo')
                ->schema([
                    TextEntry::make('logo')
                        ->label('')
                        ->html()
                        ->formatStateUsing(fn () => new \Illuminate\Support\HtmlString(
                            '<div class="flex justify-center"><img src="'.asset('storage/'.$record->logo).'" alt="Gateway Logo" class="max-w-xs rounded-lg shadow-md"></div>'
                        )),
                ])
                ->collapsible();
        }

        if ($record->description) {
            $sections[] = \Filament\Schemas\Components\Section::make('Description')
                ->schema([
                    TextEntry::make('description')
                        ->label('')
                        ->html(),
                ])
                ->collapsible();
        }

        $additionalFields = [];

        if ($record->currency) {
            $additionalFields[] = TextEntry::make('currency')
                ->label('Currency')
                ->badge()
                ->color('gray');
        }

        if ($record->type === PaymentGatewayType::AUTOMATIC) {
            $additionalFields[] = TextEntry::make('test_mode')
                ->label('Test Mode')
                ->badge()
                ->color(fn ($state) => $state ? 'warning' : 'success')
                ->formatStateUsing(fn ($state) => $state ? 'Enabled' : 'Disabled');
        }

        if (! empty($additionalFields)) {
            $sections[] = \Filament\Schemas\Components\Section::make('Settings')
                ->schema($additionalFields)
                ->columns(2)
                ->collapsible();
        }

        if ($record->credentials) {
            $credentialFields = [];
            foreach ($record->credentials as $key => $value) {
                $credentialFields[] = TextEntry::make("credentials.{$key}")
                    ->label(ucwords(str_replace('_', ' ', $key)))
                    ->formatStateUsing(fn () => str_repeat('•', 20))
                    ->copyable()
                    ->copyableState(fn () => $value);
            }

            $sections[] = \Filament\Schemas\Components\Section::make('Credentials')
                ->description('Click on any field to copy the actual value')
                ->schema($credentialFields)
                ->columns(1)
                ->collapsible();
        }

        if ($record->settings) {
            $settingsFields = [];
            foreach ($record->settings as $key => $value) {
                $settingsFields[] = TextEntry::make("settings.{$key}")
                    ->label(ucwords(str_replace('_', ' ', $key)))
                    ->copyable();
            }

            $sections[] = \Filament\Schemas\Components\Section::make(
                $record->type === PaymentGatewayType::CRYPTO ? 'Wallet Details' : 'Bank Details'
            )
                ->schema($settingsFields)
                ->columns(2)
                ->collapsible();
        }

        return $sections;
    }

    protected static function getEditForm($record): array
    {
        $commonFields = [
            Grid::make(2)->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(),
            ]),

            FileUpload::make('logo')
                ->label('Logo')
                ->image()
                ->disk('public')
                ->directory('payment-gateways')
                ->visibility('public')
                ->imageEditor()
                ->nullable(),

            Textarea::make('description')
                ->label($record->type === PaymentGatewayType::MANUAL ? 'Instructions' : 'Description')
                ->rows(3)
                ->nullable(),
        ];

        if ($record->type === PaymentGatewayType::CRYPTO) {
            $commonFields[] = TextInput::make('currency')
                ->label('Currency Symbol')
                ->nullable();
        }

        $commonFields[] = Grid::make(2)->schema([
            Toggle::make('is_active')
                ->label('Active'),

            Toggle::make('is_test_mode')
                ->label('Test Mode')
                ->hidden($record->type !== PaymentGatewayType::AUTOMATIC),
        ]);

        if ($record->type === PaymentGatewayType::AUTOMATIC) {
            $credentialsFields = match ($record->code) {
                'stripe' => [
                    TextInput::make('credentials.publishable_key')
                        ->label('Publishable Key')
                        ->required()
                        ->helperText('e.g. pk_live_xxx')
                        ->default($record->credentials['publishable_key'] ?? null),
                    TextInput::make('credentials.secret_key')
                        ->label('Secret Key')
                        ->required()
                        ->helperText('e.g. sk_live_xxx')
                        ->default($record->credentials['secret_key'] ?? null),
                ],
                'paypal' => [
                    TextInput::make('credentials.client_id')
                        ->label('Client ID')
                        ->required()
                        ->default($record->credentials['client_id'] ?? null),
                    TextInput::make('credentials.client_secret')
                        ->label('Client Secret')
                        ->required()
                        ->default($record->credentials['client_secret'] ?? null),
                ],
                'paystack' => [
                    TextInput::make('credentials.public_key')
                        ->label('Public Key')
                        ->required()
                        ->helperText('e.g. pk_live_xxx')
                        ->default($record->credentials['public_key'] ?? null),
                    TextInput::make('credentials.secret_key')
                        ->label('Secret Key')
                        ->required()
                        ->helperText('e.g. sk_live_xxx')
                        ->default($record->credentials['secret_key'] ?? null),
                ],
                'flutterwave' => [
                    TextInput::make('credentials.public_key')
                        ->label('Public Key')
                        ->required()
                        ->default($record->credentials['public_key'] ?? null),
                    TextInput::make('credentials.secret_key')
                        ->label('Secret Key')
                        ->required()
                        ->default($record->credentials['secret_key'] ?? null),
                    TextInput::make('credentials.encryption_key')
                        ->label('Encryption Key')
                        ->required()
                        ->default($record->credentials['encryption_key'] ?? null),
                ],
                default => [],
            };

            if (! empty($credentialsFields)) {
                $commonFields[] = Grid::make(2)->schema($credentialsFields);
            } else {
                $commonFields[] = KeyValue::make('credentials')
                    ->label('API Credentials')
                    ->keyLabel('Credential Name')
                    ->valueLabel('Value')
                    ->addButtonLabel('Add Credential')
                    ->reorderable()
                    ->nullable();
            }
        }

        $commonFields[] = KeyValue::make('settings')
            ->label($record->type === PaymentGatewayType::CRYPTO ? 'Wallet Details' : 'Bank Details')
            ->keyLabel($record->type === PaymentGatewayType::CRYPTO ? 'Network/Chain' : 'Field Name')
            ->valueLabel($record->type === PaymentGatewayType::CRYPTO ? 'Wallet Address' : 'Value')
            ->addButtonLabel($record->type === PaymentGatewayType::CRYPTO ? 'Add Wallet' : 'Add Field')
            ->reorderable()
            ->nullable();

        return $commonFields;
    }
}
