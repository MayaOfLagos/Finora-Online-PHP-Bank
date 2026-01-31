<?php

namespace App\Filament\Resources\Rewards\Tables;

use App\Models\Reward;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->weight('bold')
                    ->limit(30),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('points')
                    ->numeric()
                    ->sortable()
                    ->suffix(' pts')
                    ->color('success'),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'referral',
                        'success' => 'cashback',
                        'info' => 'loyalty',
                        'warning' => 'bonus',
                        'danger' => 'achievement',
                    ]),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'earned',
                        'info' => 'redeemed',
                        'gray' => 'expired',
                    ]),
                TextColumn::make('earned_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'earned' => 'Earned',
                        'redeemed' => 'Redeemed',
                        'expired' => 'Expired',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'referral' => 'Referral',
                        'cashback' => 'Cashback',
                        'loyalty' => 'Loyalty',
                        'bonus' => 'Bonus',
                        'achievement' => 'Achievement',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalWidth(Width::ThreeExtraLarge)
                        ->modalHeading(fn (Reward $record) => "Reward: {$record->title}")
                        ->infolist(fn (Reward $record) => self::getViewInfolist($record))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),

                    Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->modalWidth(Width::TwoExtraLarge)
                        ->fillForm(fn (Reward $record) => [
                            'user_id' => $record->user_id,
                            'title' => $record->title,
                            'description' => $record->description,
                            'points' => $record->points,
                            'type' => $record->type,
                            'status' => $record->status,
                            'earned_date' => $record->earned_date,
                            'expiry_date' => $record->expiry_date,
                            'redeemed_at' => $record->redeemed_at,
                            'source' => $record->source,
                        ])
                        ->form(self::getEditForm())
                        ->action(function (Reward $record, array $data) {
                            $record->update($data);

                            Notification::make()
                                ->title('Reward updated successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('redeem')
                        ->label('Mark Redeemed')
                        ->icon('heroicon-o-gift')
                        ->color('success')
                        ->visible(fn (Reward $record) => $record->status === 'earned')
                        ->requiresConfirmation()
                        ->modalDescription('This will mark the reward as redeemed and set the redemption timestamp.')
                        ->action(function (Reward $record) {
                            $record->update([
                                'status' => 'redeemed',
                                'redeemed_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Reward marked as redeemed')
                                ->success()
                                ->send();
                        }),

                    Action::make('expire')
                        ->label('Mark Expired')
                        ->icon('heroicon-o-clock')
                        ->color('gray')
                        ->visible(fn (Reward $record) => in_array($record->status, ['pending', 'earned']))
                        ->requiresConfirmation()
                        ->modalDescription('This will mark the reward as expired.')
                        ->action(function (Reward $record) {
                            $record->update(['status' => 'expired']);

                            Notification::make()
                                ->title('Reward marked as expired')
                                ->warning()
                                ->send();
                        }),

                    Action::make('delete')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Reward')
                        ->modalDescription('Are you sure you want to delete this reward? This action can be undone.')
                        ->action(function (Reward $record) {
                            $record->delete();

                            Notification::make()
                                ->title('Reward deleted')
                                ->success()
                                ->send();
                        }),

                    Action::make('restore')
                        ->label('Restore')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->visible(fn (Reward $record) => $record->trashed())
                        ->requiresConfirmation()
                        ->modalDescription('This will restore the deleted reward.')
                        ->action(function (Reward $record) {
                            $record->restore();

                            Notification::make()
                                ->title('Reward restored')
                                ->success()
                                ->send();
                        }),

                    Action::make('force_delete')
                        ->label('Permanently Delete')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Reward $record) => $record->trashed())
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Reward')
                        ->modalDescription('Are you sure you want to permanently delete this reward? This action cannot be undone.')
                        ->action(function (Reward $record) {
                            $record->forceDelete();

                            Notification::make()
                                ->title('Reward permanently deleted')
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function getViewInfolist(Reward $record): array
    {
        return [
            Section::make('Reward Details')
                ->icon('heroicon-o-gift')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('title')
                                ->label('Title')
                                ->weight(FontWeight::Bold),

                            TextEntry::make('points')
                                ->label('Points')
                                ->formatStateUsing(fn ($state) => number_format($state).' pts')
                                ->badge()
                                ->color('success'),

                            TextEntry::make('type')
                                ->label('Type')
                                ->badge()
                                ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                ->color(fn (string $state): string => match ($state) {
                                    'referral' => 'primary',
                                    'cashback' => 'success',
                                    'loyalty' => 'info',
                                    'bonus' => 'warning',
                                    'achievement' => 'danger',
                                    default => 'gray',
                                }),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextEntry::make('status')
                                ->badge()
                                ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                ->color(fn (string $state): string => match ($state) {
                                    'pending' => 'warning',
                                    'earned' => 'success',
                                    'redeemed' => 'info',
                                    'expired' => 'gray',
                                    default => 'gray',
                                }),

                            TextEntry::make('user.email')
                                ->label('User')
                                ->icon('heroicon-o-user')
                                ->copyable(),
                        ]),

                    TextEntry::make('description')
                        ->label('Description')
                        ->placeholder('No description')
                        ->columnSpanFull(),
                ]),

            Section::make('Dates')
                ->icon('heroicon-o-calendar')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('earned_date')
                                ->label('Earned Date')
                                ->date()
                                ->placeholder('Not set'),

                            TextEntry::make('expiry_date')
                                ->label('Expiry Date')
                                ->date()
                                ->placeholder('Never')
                                ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 'gray'),

                            TextEntry::make('redeemed_at')
                                ->label('Redeemed At')
                                ->dateTime()
                                ->placeholder('Not redeemed'),
                        ]),
                ]),

            Section::make('Source & Metadata')
                ->icon('heroicon-o-information-circle')
                ->collapsible()
                ->schema([
                    TextEntry::make('source')
                        ->label('Source')
                        ->placeholder('No source specified')
                        ->columnSpanFull(),

                    TextEntry::make('created_at')
                        ->label('Created')
                        ->dateTime(),

                    TextEntry::make('updated_at')
                        ->label('Last Updated')
                        ->dateTime(),
                ]),
        ];
    }

    private static function getEditForm(): array
    {
        return [
            Section::make('Reward Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                    ]),
                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                    Grid::make(3)->schema([
                        TextInput::make('points')
                            ->required()
                            ->numeric()
                            ->prefix('pts'),
                        Select::make('type')
                            ->options([
                                'referral' => 'Referral',
                                'cashback' => 'Cashback',
                                'loyalty' => 'Loyalty',
                                'bonus' => 'Bonus',
                                'achievement' => 'Achievement',
                            ])
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'earned' => 'Earned',
                                'redeemed' => 'Redeemed',
                                'expired' => 'Expired',
                            ])
                            ->required(),
                    ]),
                ]),

            Section::make('Dates')
                ->schema([
                    Grid::make(3)->schema([
                        DatePicker::make('earned_date')
                            ->label('Earned Date'),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date'),
                        DateTimePicker::make('redeemed_at')
                            ->label('Redeemed At'),
                    ]),
                    Textarea::make('source')
                        ->rows(2)
                        ->columnSpanFull()
                        ->label('Source'),
                ]),
        ];
    }
}
