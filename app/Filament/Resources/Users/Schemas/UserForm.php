<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User details')
                    ->schema([
                        TextInput::make('first_name')
                            ->required(),
                        TextInput::make('last_name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        Select::make('role')
                            ->label('User Role')
                            ->options(function () {
                                // Super admins can assign any role
                                // Regular admins cannot assign super_admin
                                $user = auth()->user();
                                if ($user?->isSuperAdmin()) {
                                    return UserRole::options();
                                }
                                return UserRole::assignableByAdmin();
                            })
                            ->default(UserRole::User->value)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('email_verified_at'),
                        TextInput::make('password')
                            ->password()
                            ->required(fn ($record) => $record === null)
                            ->dehydrated(fn ($state) => filled($state)),
                        TextInput::make('uuid')
                            ->label('UUID')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('phone_number')
                            ->tel(),
                        DatePicker::make('date_of_birth'),
                        TextInput::make('address_line_1'),
                        TextInput::make('address_line_2'),
                        TextInput::make('city'),
                        TextInput::make('state'),
                        TextInput::make('postal_code'),
                        TextInput::make('country'),
                        TextInput::make('profile_photo_path'),
                        TextInput::make('transaction_pin')
                            ->password(),
                        Toggle::make('is_active')
                            ->required(),
                        Toggle::make('is_verified')
                            ->required(),
                        TextInput::make('kyc_level')
                            ->required()
                            ->numeric()
                            ->default(1),
                        DateTimePicker::make('last_login_at')
                            ->disabled(),
                        TextInput::make('last_login_ip')
                            ->disabled(),
                        Textarea::make('two_factor_secret')
                            ->columnSpanFull(),
                        Textarea::make('two_factor_recovery_codes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
