<?php

namespace App\Filament\Resources\Beneficiaries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeneficiaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Beneficiary Information')
                    ->description('Add a beneficiary for quick and easy transfers')
                    ->schema([
                        Select::make('user_id')
                            ->label('Account Owner')
                            ->relationship('user')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->email . ')')
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required()
                            ->preload()
                            ->columnSpanFull(),
                        Select::make('beneficiary_user_id')
                            ->label('Beneficiary User')
                            ->relationship('beneficiaryUser')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->email . ')')
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Auto-load first account when user is selected
                                if ($state) {
                                    $user = \App\Models\User::find($state);
                                    if ($user && $user->bankAccounts()->exists()) {
                                        $set('beneficiary_account_id', $user->bankAccounts()->first()->id);
                                    }
                                }
                            })
                            ->helperText('Select the user who will receive transfers'),
                        Select::make('beneficiary_account_id')
                            ->label('Beneficiary Account')
                            ->options(function (callable $get) {
                                $userId = $get('beneficiary_user_id');
                                if (!$userId) {
                                    return [];
                                }
                                $user = \App\Models\User::find($userId);
                                if (!$user) {
                                    return [];
                                }
                                return $user->bankAccounts->mapWithKeys(function ($account) {
                                    return [
                                        $account->id => $account->accountType->name . ' - ' . $account->account_number . ' (' . $account->currency . ')'
                                    ];
                                });
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Select the account to receive transfers')
                            ->unique(
                                table: \App\Models\Beneficiary::class,
                                column: 'beneficiary_account_id',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule, callable $get) {
                                    return $rule->where('user_id', $get('user_id'));
                                }
                            )
                            ->validationMessages([
                                'unique' => 'This beneficiary account has already been added.',
                            ]),
                        TextInput::make('nickname')
                            ->label('Nickname')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Mom, Business Partner, Savings Account')
                            ->helperText('A friendly name to identify this beneficiary'),
                    ])
                    ->columns(2),
                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_verified')
                            ->label('Verified')
                            ->helperText('Mark this beneficiary as verified')
                            ->default(false)
                            ->inline(false),
                        Toggle::make('is_favorite')
                            ->label('Favorite')
                            ->helperText('Mark as favorite for quick access')
                            ->default(false)
                            ->inline(false),
                        TextInput::make('transfer_limit')
                            ->label('Transfer Limit (Optional)')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('No limit')
                            ->helperText('Maximum amount that can be transferred in a single transaction')
                            ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => $state ? (int) ($state * 100) : null),
                        DateTimePicker::make('last_used_at')
                            ->label('Last Used At')
                            ->disabled()
                            ->helperText('Automatically updated when a transfer is made'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
