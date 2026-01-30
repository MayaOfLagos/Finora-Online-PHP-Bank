<?php

namespace App\Filament\Resources\Cards\Tables;

use App\Models\CardType;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class CardTypesTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->searchable()
                ->sortable(),

            TextColumn::make('code')
                ->searchable()
                ->sortable()
                ->badge()
                ->color('info'),

            TextColumn::make('default_limit')
                ->label('Default Limit')
                ->money('USD')
                ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2))
                ->sortable(),

            IconColumn::make('is_virtual')
                ->label('Virtual')
                ->boolean(),

            IconColumn::make('is_credit')
                ->label('Credit')
                ->boolean(),

            IconColumn::make('is_active')
                ->label('Active')
                ->boolean(),

            TextColumn::make('cards_count')
                ->label('Cards Issued')
                ->counts('cards')
                ->badge()
                ->color('success'),

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalWidth(Width::TwoExtraLarge)
                    ->modalHeading(fn (CardType $record) => "Card Type: {$record->name}")
                    ->infolist([
                        \Filament\Schemas\Components\Section::make('Card Type Information')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(3)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('name')
                                            ->label('Name'),

                                        \Filament\Infolists\Components\TextEntry::make('code')
                                            ->label('Code')
                                            ->badge()
                                            ->color('info'),

                                        \Filament\Infolists\Components\TextEntry::make('default_limit')
                                            ->label('Default Limit')
                                            ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2)),
                                    ]),

                                \Filament\Schemas\Components\Grid::make(3)
                                    ->schema(components: [
                                        \Filament\Infolists\Components\IconEntry::make('is_virtual')
                                            ->label('Virtual Card')
                                            ->boolean(),

                                        \Filament\Infolists\Components\IconEntry::make('is_credit')
                                            ->label('Credit Card')
                                            ->boolean(),

                                        \Filament\Infolists\Components\IconEntry::make('is_active')
                                            ->label('Active')
                                            ->boolean(),
                                    ]),

                                \Filament\Infolists\Components\TextEntry::make('cards_count')
                                    ->label('Total Cards Issued')
                                    ->state(fn (CardType $record) => $record->cards()->count())
                                    ->badge()
                                    ->color('success'),

                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime(),

                                        \Filament\Infolists\Components\TextEntry::make('updated_at')
                                            ->label('Updated At')
                                            ->dateTime(),
                                    ]),
                            ]),
                    ]),

                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->modalWidth(Width::TwoExtraLarge)
                    ->fillForm(function (CardType $record): array {
                        return [
                            'name' => $record->name,
                            'code' => $record->code,
                            'default_limit' => $record->default_limit / 100,
                            'is_virtual' => $record->is_virtual,
                            'is_credit' => $record->is_credit,
                            'is_active' => $record->is_active,
                        ];
                    })
                    ->form([
                        \Filament\Schemas\Components\Section::make('Card Type Information')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('e.g., Gold Card, Platinum Card'),

                                        Forms\Components\TextInput::make('code')
                                            ->required()
                                            ->maxLength(50)
                                            ->disabled()
                                            ->helperText('Code cannot be changed'),

                                        Forms\Components\TextInput::make('default_limit')
                                            ->label('Default Limit ($)')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->helperText('Default spending/credit limit'),
                                    ]),

                                \Filament\Schemas\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_virtual')
                                            ->label('Virtual Card')
                                            ->inline(false),

                                        Forms\Components\Toggle::make('is_credit')
                                            ->label('Credit Card')
                                            ->inline(false)
                                            ->helperText('Otherwise debit card'),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active')
                                            ->inline(false),
                                    ]),
                            ]),
                    ])
                    ->action(function (CardType $record, array $data) {
                        // Convert limit to cents
                        $data['default_limit'] = (int) ($data['default_limit'] * 100);
                        unset($data['code']); // Don't update code

                        $record->update($data);

                        Notification::make()
                            ->title('Card Type Updated')
                            ->success()
                            ->send();
                    }),

                Action::make('toggle_status')
                    ->label(fn (CardType $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (CardType $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (CardType $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (CardType $record) => $record->is_active ? 'Deactivate Card Type' : 'Activate Card Type')
                    ->modalDescription(fn (CardType $record) => $record->is_active
                        ? 'Are you sure you want to deactivate this card type? It will no longer be available for new cards.'
                        : 'Are you sure you want to activate this card type?')
                    ->action(function (CardType $record) {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title('Card Type '.($record->is_active ? 'Activated' : 'Deactivated'))
                            ->success()
                            ->send();
                    }),

                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Card Type')
                    ->modalDescription('Are you sure you want to delete this card type? This action cannot be undone.')
                    ->action(function (CardType $record) {
                        if ($record->cards()->count() > 0) {
                            Notification::make()
                                ->title('Cannot Delete')
                                ->body('This card type has cards associated with it and cannot be deleted.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title('Card Type Deleted')
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('primary')
                ->button(),
        ];
    }
}
