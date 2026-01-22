<?php

namespace App\Filament\Resources\DomesticTransfers\Schemas;

use App\Enums\TransferStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DomesticTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transfer Information')
                    ->description('Basic transfer details and reference')
                    ->schema([
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->default(fn (): string => 'DOM-' . strtoupper(substr(uniqid(), -8)))
                            ->required()
                            ->maxLength(255)
                            ->suffixIcon('heroicon-o-arrow-path')
                            ->helperText('Auto-generated reference number')
                            ->columnSpanFull(),
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->email . ')')
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required()
                            ->preload()
                            ->live(),
                        Select::make('bank_account_id')
                            ->label('Source Bank Account')
                            ->options(function (callable $get) {
                                $userId = $get('user_id');
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
                            ->searchable(),
                        Select::make('status')
                            ->label('Status')
                            ->options(TransferStatus::class)
                            ->default('pending')
                            ->required(),
                        Select::make('bank_id')
                            ->label('Destination Bank')
                            ->relationship('bank', 'name')
                            ->required()
                            ->searchable()
                            ->helperText('Select the bank to transfer to'),
                    ])
                    ->columns(2),

                Section::make('Beneficiary Details')
                    ->description('Information about the transfer recipient')
                    ->schema([
                        TextInput::make('beneficiary_name')
                            ->label('Beneficiary Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('beneficiary_account')
                            ->label('Beneficiary Account Number')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Amount Details')
                    ->description('Transfer amount, fees and description')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Transfer Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->minValue(0.01),
                        TextInput::make('currency')
                            ->label('Currency')
                            ->required()
                            ->default('USD')
                            ->maxLength(3),
                        TextInput::make('fee')
                            ->label('Transfer Fee')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(0)
                            ->minValue(0),
                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Optional note about this transfer')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Verification Timeline')
                    ->description('Verification steps timestamps')
                    ->schema([
                        DateTimePicker::make('pin_verified_at')
                            ->label('PIN Verified At')
                            ->displayFormat('M d, Y H:i')
                            ->disabled(),
                        DateTimePicker::make('otp_verified_at')
                            ->label('OTP Verified At')
                            ->displayFormat('M d, Y H:i')
                            ->disabled(),
                        DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->displayFormat('M d, Y H:i')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Additional Information')
                    ->schema([
                        TextInput::make('uuid')
                            ->label('UUID')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
