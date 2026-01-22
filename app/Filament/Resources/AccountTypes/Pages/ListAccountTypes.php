<?php

namespace App\Filament\Resources\AccountTypes\Pages;

use App\Filament\Resources\AccountTypes\AccountTypeResource;
use App\Models\AccountType;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAccountTypes extends ListRecords
{
    protected static string $resource = AccountTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Account Type')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalHeading('Create Account Type')
                ->modalWidth('md')
                ->form([
                    TextInput::make('name')
                        ->label('Account Type Name')
                        ->required()
                        ->maxLength(50)
                        ->unique('account_types', 'name'),

                    TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->maxLength(20)
                        ->unique('account_types', 'code')
                        ->placeholder('e.g., SAV, CHK, BIZ'),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    $accountType = AccountType::create($data);

                    Notification::make()
                        ->title('Account Type Created')
                        ->body('Account type "'.$accountType->name.'" created successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
