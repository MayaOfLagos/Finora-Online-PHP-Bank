<?php

namespace App\Filament\Resources\Cards\Tables;

use App\Enums\CardStatus;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('card_holder_name')
                    ->label('Card Holder')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('masked_card_number')
                    ->label('Card Number')
                    ->searchable(['card_number']),
                TextColumn::make('cardType.name')
                    ->label('Card Type')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_virtual')
                    ->label('Virtual')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('spending_limit')
                    ->label('Spending Limit')
                    ->money('USD', divideBy: 100)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('issued_at')
                    ->label('Issued')
                    ->date()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CardStatus::class)
                    ->multiple(),
                SelectFilter::make('card_type_id')
                    ->relationship('cardType', 'name')
                    ->label('Card Type')
                    ->multiple(),
                SelectFilter::make('is_virtual')
                    ->options([
                        '1' => 'Virtual',
                        '0' => 'Physical',
                    ])
                    ->label('Type'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalWidth(Width::ThreeExtraLarge)
                        ->infolist(fn ($record) => self::getViewInfolist($record))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),

                    Action::make('edit')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->modalWidth(Width::TwoExtraLarge)
                        ->fillForm(fn ($record) => $record->toArray())
                        ->form(fn ($record) => self::getEditForm($record))
                        ->action(function ($record, array $data) {
                            // Convert dollar amounts to cents
                            if (isset($data['spending_limit'])) {
                                $data['spending_limit'] = $data['spending_limit'] * 100;
                            }
                            if (isset($data['daily_limit'])) {
                                $data['daily_limit'] = $data['daily_limit'] * 100;
                            }

                            $record->update($data);

                            Notification::make()
                                ->title('Card updated successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('pause')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->visible(fn ($record) => $record->status === CardStatus::Active)
                        ->requiresConfirmation()
                        ->modalDescription('This will temporarily freeze the card. The user will not be able to make transactions.')
                        ->action(function ($record) {
                            $record->update(['status' => CardStatus::Frozen]);

                            Notification::make()
                                ->title('Card paused successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status !== CardStatus::Active)
                        ->requiresConfirmation()
                        ->modalDescription('This will activate the card and allow the user to make transactions.')
                        ->action(function ($record) {
                            $record->update(['status' => CardStatus::Active]);

                            Notification::make()
                                ->title('Card activated successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('block')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status !== CardStatus::Blocked)
                        ->requiresConfirmation()
                        ->modalDescription('This will permanently block the card. This action cannot be undone.')
                        ->action(function ($record) {
                            $record->update([
                                'status' => CardStatus::Blocked,
                                'blocked_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Card blocked successfully')
                                ->danger()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('This will permanently delete the card record.'),
                ])
                    ->button()
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function getViewInfolist($record): array
    {
        return [
            Section::make('Card Information')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('card_holder_name')
                                ->label('Card Holder Name'),
                            TextEntry::make('masked_card_number')
                                ->label('Card Number'),
                            TextEntry::make('cardType.name')
                                ->label('Card Type')
                                ->badge()
                                ->color('info'),
                            TextEntry::make('is_virtual')
                                ->label('Card Type')
                                ->formatStateUsing(fn ($state) => $state ? 'Virtual Card' : 'Physical Card')
                                ->badge()
                                ->color(fn ($state) => $state ? 'warning' : 'success'),
                            TextEntry::make('status')
                                ->badge(),
                            TextEntry::make('issued_at')
                                ->label('Issued Date')
                                ->dateTime(),
                            TextEntry::make('expires_at')
                                ->label('Expiry Date')
                                ->dateTime(),
                            TextEntry::make('blocked_at')
                                ->label('Blocked Date')
                                ->dateTime()
                                ->placeholder('Not blocked'),
                        ]),
                ]),

            Section::make('Account Information')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('user.name')
                                ->label('User'),
                            TextEntry::make('user.email')
                                ->label('Email'),
                            TextEntry::make('bankAccount.account_number')
                                ->label('Account Number'),
                            TextEntry::make('bankAccount.accountType.name')
                                ->label('Account Type'),
                        ]),
                ]),

            Section::make('Limits & Security')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('spending_limit')
                                ->label('Spending Limit')
                                ->money('USD', divideBy: 100),
                            TextEntry::make('daily_limit')
                                ->label('Daily Limit')
                                ->money('USD', divideBy: 100),
                        ]),
                ]),
        ];
    }

    protected static function getEditForm($record): array
    {
        return [
            Select::make('status')
                ->options(CardStatus::class)
                ->required(),
            TextInput::make('card_holder_name')
                ->required()
                ->maxLength(255),
            TextInput::make('spending_limit')
                ->label('Spending Limit (USD)')
                ->numeric()
                ->prefix('$')
                ->default(fn ($record) => $record->spending_limit / 100)
                ->step(0.01),
            TextInput::make('daily_limit')
                ->label('Daily Limit (USD)')
                ->numeric()
                ->prefix('$')
                ->default(fn ($record) => $record->daily_limit / 100)
                ->step(0.01),
            DateTimePicker::make('expires_at')
                ->required(),
        ];
    }
}
