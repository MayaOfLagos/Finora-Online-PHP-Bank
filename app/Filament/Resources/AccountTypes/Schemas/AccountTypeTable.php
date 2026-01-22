<?php

namespace App\Filament\Resources\AccountTypes\Schemas;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountTypeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Account Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('bankAccounts_count')
                    ->label('Total Accounts')
                    ->counts('bankAccounts')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading('View Account Type')
                        ->modalWidth('md')
                        ->infolist([
                            Section::make()
                                ->schema([
                                    TextEntry::make('name')
                                        ->label('Account Type Name'),

                                    TextEntry::make('code')
                                        ->label('Code'),

                                    TextEntry::make('description')
                                        ->label('Description'),

                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime(),
                                ])
                                ->columns(2),
                        ])
                        ->action(function (): void {
                            // View only action
                        }),

                    Action::make('edit')
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->modalHeading('Edit Account Type')
                        ->modalWidth('md')
                        ->form([
                            TextInput::make('name')
                                ->label('Account Type Name')
                                ->required()
                                ->maxLength(50),

                            TextInput::make('code')
                                ->label('Code')
                                ->required()
                                ->maxLength(20),

                            Textarea::make('description')
                                ->label('Description')
                                ->rows(3)
                                ->maxLength(255),
                        ])
                        ->fillForm(fn ($record) => $record->toArray())
                        ->action(function ($record, array $data): void {
                            $record->update($data);

                            Notification::make()
                                ->title('Account Type Updated')
                                ->body('Account type "'.$record->name.'" updated successfully.')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
