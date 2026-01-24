<?php

namespace App\Filament\Resources\WireTransfers\Schemas;

use App\Helpers\Countries;
use App\Enums\TransferStatus;
use App\Enums\TransferStep;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class WireTransferForm
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
                            ->default(fn (): string => 'WIRE-' . strtoupper(substr(uniqid(), -8)))
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
                        Select::make('current_step')
                            ->label('Current Step')
                            ->options(TransferStep::class)
                            ->placeholder('Not set'),
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
                        TextInput::make('beneficiary_bank_name')
                            ->label('Bank Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('beneficiary_bank_address')
                            ->label('Bank Address')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('beneficiary_country')
                            ->label('Beneficiary Country')
                            ->options(Countries::forSelect())
                            ->searchable()
                            ->placeholder('Select country')
                            ->columnSpanFull(),
                        TextInput::make('swift_code')
                            ->label('SWIFT/BIC Code')
                            ->required()
                            ->maxLength(11)
                            ->placeholder('e.g., CHASUS33'),
                        TextInput::make('routing_number')
                            ->label('Routing Number')
                            ->maxLength(9)
                            ->placeholder('Optional'),
                    ])
                    ->columns(2),

                Section::make('Amount Details')
                    ->description('Transfer amount and fees')
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
                            ->maxLength(3)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Auto-set from bank account'),
                        TextInput::make('exchange_rate')
                            ->label('Exchange Rate')
                            ->numeric()
                            ->step(0.0001)
                            ->placeholder('1.0000'),
                        TextInput::make('fee')
                            ->label('Transfer Fee')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->helperText('Amount + Fee'),
                        Textarea::make('remarks')
                            ->label('Remarks / Purpose of Transfer')
                            ->placeholder('Reason for this transfer (optional)')
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
                        DateTimePicker::make('imf_verified_at')
                            ->label('IMF Code Verified At')
                            ->displayFormat('M d, Y H:i')
                            ->disabled(),
                        DateTimePicker::make('tax_verified_at')
                            ->label('Tax Code Verified At')
                            ->displayFormat('M d, Y H:i')
                            ->disabled(),
                        DateTimePicker::make('cot_verified_at')
                            ->label('COT Code Verified At')
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
                        Textarea::make('failed_reason')
                            ->label('Failed Reason')
                            ->placeholder('Reason for failure if status is failed')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
