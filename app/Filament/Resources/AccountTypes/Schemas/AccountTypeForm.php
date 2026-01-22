<?php

namespace App\Filament\Resources\AccountTypes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Account Type Name')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(255),
            ]);
    }
}
