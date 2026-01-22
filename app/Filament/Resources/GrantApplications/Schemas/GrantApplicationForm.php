<?php

namespace App\Filament\Resources\GrantApplications\Schemas;

use App\Enums\GrantApplicationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GrantApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grant application')
                    ->schema([
                        TextInput::make('uuid')
                            ->label('UUID')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required(),
                        Select::make('grant_program_id')
                            ->relationship('grantProgram', 'name')
                            ->required(),
                        TextInput::make('reference_number')
                            ->required(),
                        Select::make('status')
                            ->options(GrantApplicationStatus::class)
                            ->default('pending')
                            ->required(),
                        Textarea::make('rejection_reason')
                            ->columnSpanFull(),
                        DateTimePicker::make('approved_at'),
                        TextInput::make('approved_by')
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }
}
