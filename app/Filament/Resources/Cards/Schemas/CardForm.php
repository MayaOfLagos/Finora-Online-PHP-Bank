<?php

namespace App\Filament\Resources\Cards\Schemas;

use App\Enums\CardStatus;
use App\Models\BankAccount;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User & Account Information')
                    ->description('Select the user and their bank account for this card')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('User')
                                    ->relationship('user')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->email.')')
                                    ->searchable(['first_name', 'last_name', 'email'])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('bank_account_id', null)),

                                Select::make('bank_account_id')
                                    ->label('Bank Account')
                                    ->options(function (callable $get) {
                                        $userId = $get('user_id');
                                        if (! $userId) {
                                            return [];
                                        }

                                        return BankAccount::where('user_id', $userId)
                                            ->with('accountType')
                                            ->get()
                                            ->mapWithKeys(fn ($account) => [
                                                $account->id => $account->account_number.' - '.$account->accountType->name,
                                            ]);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->disabled(fn (callable $get) => ! $get('user_id'))
                                    ->helperText('Select user first'),
                            ]),
                    ]),

                Section::make('Card Type & Details')
                    ->description('Configure the card type and basic information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('card_type_id')
                                    ->label('Card Type')
                                    ->relationship('cardType', 'name')
                                    ->required()
                                    ->searchable(),

                                TextInput::make('card_holder_name')
                                    ->label('Card Holder Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('John Doe'),

                                Toggle::make('is_virtual')
                                    ->label('Virtual Card')
                                    ->helperText('Virtual cards are digital-only')
                                    ->default(false)
                                    ->inline(false),

                                Select::make('status')
                                    ->options(CardStatus::class)
                                    ->default(CardStatus::Active->value)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Card Security Details')
                    ->description('Secure card information (encrypted in database)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('card_number')
                                    ->label('Card Number')
                                    ->required()
                                    ->maxLength(19)
                                    ->placeholder('1234 5678 9012 3456')
                                    ->tel()
                                    ->helperText('Will be encrypted'),

                                TextInput::make('expiry_month')
                                    ->label('Expiry Month')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(12)
                                    ->placeholder('12')
                                    ->helperText('Will be encrypted'),

                                TextInput::make('expiry_year')
                                    ->label('Expiry Year')
                                    ->required()
                                    ->numeric()
                                    ->minValue(date('Y'))
                                    ->placeholder('2030')
                                    ->helperText('Will be encrypted'),

                                TextInput::make('cvv')
                                    ->label('CVV')
                                    ->required()
                                    ->numeric()
                                    ->maxLength(4)
                                    ->placeholder('123')
                                    ->helperText('Will be encrypted'),

                                TextInput::make('pin')
                                    ->label('PIN')
                                    ->password()
                                    ->numeric()
                                    ->maxLength(6)
                                    ->minLength(4)
                                    ->placeholder('1234')
                                    ->helperText('Will be hashed'),
                            ]),
                    ]),

                Section::make('Limits & Restrictions')
                    ->description('Set spending and daily transaction limits')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('spending_limit')
                                    ->label('Spending Limit')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->placeholder('5000.00')
                                    ->helperText('Maximum total spending limit')
                                    ->afterStateHydrated(function ($component, $state) {
                                        if ($state) {
                                            $component->state($state / 100);
                                        }
                                    }),

                                TextInput::make('daily_limit')
                                    ->label('Daily Limit')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->placeholder('1000.00')
                                    ->helperText('Maximum daily spending limit')
                                    ->afterStateHydrated(function ($component, $state) {
                                        if ($state) {
                                            $component->state($state / 100);
                                        }
                                    }),
                            ]),
                    ]),

                Section::make('Card Dates')
                    ->description('Set issuance and expiration dates')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('issued_at')
                                    ->label('Issue Date')
                                    ->default(now())
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('expires_at')
                                    ->label('Expiration Date')
                                    ->required()
                                    ->native(false)
                                    ->minDate(now())
                                    ->default(now()->addYears(3)),
                            ]),
                    ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert dollar amounts to cents
        if (isset($data['spending_limit'])) {
            $data['spending_limit'] = $data['spending_limit'] * 100;
        }
        if (isset($data['daily_limit'])) {
            $data['daily_limit'] = $data['daily_limit'] * 100;
        }

        // Generate UUID if not present
        if (empty($data['uuid'])) {
            $data['uuid'] = (string) Str::uuid();
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return self::mutateFormDataBeforeCreate($data);
    }
}
