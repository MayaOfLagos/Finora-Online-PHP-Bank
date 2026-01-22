<?php

namespace App\Filament\Resources\InternalTransfers\Schemas;

use App\Enums\TransferStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class InternalTransferForm
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
                            ->default(fn (): string => 'INT-' . strtoupper(substr(uniqid(), -8)))
                            ->required()
                            ->maxLength(255)
                            ->suffixIcon('heroicon-o-arrow-path')
                            ->helperText('Auto-generated reference number')
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options(TransferStatus::class)
                            ->default('pending')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Sender Information')
                    ->description('Details about who is sending the transfer')
                    ->schema([
                        Select::make('sender_id')
                            ->label('Sender')
                            ->relationship('sender')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->email . ')')
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required()
                            ->preload()
                            ->live(),
                        Select::make('sender_account_id')
                            ->label('Sender Account')
                            ->options(function (callable $get) {
                                $senderId = $get('sender_id');
                                if (!$senderId) {
                                    return [];
                                }
                                $sender = \App\Models\User::find($senderId);
                                if (!$sender) {
                                    return [];
                                }
                                return $sender->bankAccounts->mapWithKeys(function ($account) {
                                    return [
                                        $account->id => $account->accountType->name . ' - ' . $account->account_number . ' (' . $account->currency . ')'
                                    ];
                                });
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Account to debit funds from'),
                    ])
                    ->columns(2),

                Section::make('Receiver Information')
                    ->description('Details about who will receive the transfer')
                    ->schema([
                        Select::make('receiver_id')
                            ->label('Receiver')
                            ->relationship('receiver')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->email . ')')
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required()
                            ->preload()
                            ->live(),
                        Select::make('receiver_account_id')
                            ->label('Receiver Account')
                            ->options(function (callable $get) {
                                $receiverId = $get('receiver_id');
                                if (!$receiverId) {
                                    return [];
                                }
                                $receiver = \App\Models\User::find($receiverId);
                                if (!$receiver) {
                                    return [];
                                }
                                return $receiver->bankAccounts->mapWithKeys(function ($account) {
                                    return [
                                        $account->id => $account->accountType->name . ' - ' . $account->account_number . ' (' . $account->currency . ')'
                                    ];
                                });
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Account to credit funds to'),
                    ])
                    ->columns(2),

                Section::make('Amount Details')
                    ->description('Transfer amount and description')
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
