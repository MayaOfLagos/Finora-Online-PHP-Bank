<?php

namespace App\Filament\Resources\Rewards\Pages;

use App\Filament\Resources\Rewards\RewardResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ViewReward extends ViewRecord
{
    protected static string $resource = RewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('redeem')
                ->label('Mark Redeemed')
                ->icon('heroicon-o-gift')
                ->color('success')
                ->visible(fn () => $this->record->status === 'earned')
                ->requiresConfirmation()
                ->modalDescription('This will mark the reward as redeemed.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'redeemed',
                        'redeemed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Reward marked as redeemed')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'redeemed_at']);
                }),

            Action::make('expire')
                ->label('Mark Expired')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->visible(fn () => in_array($this->record->status, ['pending', 'earned']))
                ->requiresConfirmation()
                ->modalDescription('This will mark the reward as expired.')
                ->action(function () {
                    $this->record->update(['status' => 'expired']);

                    Notification::make()
                        ->title('Reward marked as expired')
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            Actions\EditAction::make(),

            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Reward Details')
                    ->icon('heroicon-o-gift')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Title')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('user.email')
                                    ->label('User')
                                    ->icon('heroicon-o-user')
                                    ->copyable(),

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

                                TextEntry::make('description')
                                    ->label('Description')
                                    ->placeholder('No description provided'),
                            ]),
                    ]),

                Section::make('Dates')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('earned_date')
                                    ->label('Earned Date')
                                    ->date()
                                    ->placeholder('Not set')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('expiry_date')
                                    ->label('Expiry Date')
                                    ->date()
                                    ->placeholder('Never expires')
                                    ->icon('heroicon-o-clock')
                                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 'gray'),

                                TextEntry::make('redeemed_at')
                                    ->label('Redeemed At')
                                    ->dateTime()
                                    ->placeholder('Not redeemed yet')
                                    ->icon('heroicon-o-gift'),

                                TextEntry::make('source')
                                    ->label('Source')
                                    ->placeholder('No source specified')
                                    ->icon('heroicon-o-link'),
                            ]),
                    ]),

                Section::make('System Information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Reward ID')
                                    ->copyable()
                                    ->icon('heroicon-o-finger-print'),

                                TextEntry::make('user.name')
                                    ->label('User Name')
                                    ->placeholder('N/A')
                                    ->icon('heroicon-o-user-circle'),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime()
                                    ->icon('heroicon-o-clock'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime()
                                    ->icon('heroicon-o-arrow-path'),
                            ]),
                    ]),
            ]);
    }
}
