<?php

namespace App\Filament\Resources\ExchangeMoney\Pages;

use App\Filament\Resources\ExchangeMoney\ExchangeMoneyResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ViewExchangeMoney extends ViewRecord
{
    protected static string $resource = ExchangeMoneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('complete')
                ->label('Mark Completed')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalDescription('This will mark the currency exchange as completed.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Exchange marked as completed')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'completed_at']);
                }),

            Action::make('cancel')
                ->label('Cancel Exchange')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalDescription('This will cancel the currency exchange.')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);

                    Notification::make()
                        ->title('Exchange cancelled')
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
                Section::make('Exchange Overview')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reference_number')
                                    ->label('Reference Number')
                                    ->copyable()
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'gray',
                                        default => 'gray',
                                    }),

                                TextEntry::make('from_currency')
                                    ->label('From Currency')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('to_currency')
                                    ->label('To Currency')
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ]),

                Section::make('Exchange Details')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('from_amount')
                                    ->label('From Amount')
                                    ->formatStateUsing(fn ($state, $record) => '$'.number_format($state / 100, 2).' '.$record->from_currency)
                                    ->weight(FontWeight::Bold)
                                    ->color('warning'),

                                TextEntry::make('to_amount')
                                    ->label('To Amount')
                                    ->formatStateUsing(fn ($state, $record) => '$'.number_format($state / 100, 2).' '.$record->to_currency)
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),

                                TextEntry::make('exchange_rate')
                                    ->label('Exchange Rate')
                                    ->formatStateUsing(fn ($state) => '1 '.$this->record->from_currency.' = '.$state.' '.$this->record->to_currency)
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('fee')
                                    ->label('Transaction Fee')
                                    ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2))
                                    ->badge()
                                    ->color('danger'),
                            ]),
                    ]),

                Section::make('User Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('User Name')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('bankAccount.account_number')
                                    ->label('Bank Account')
                                    ->copyable()
                                    ->icon('heroicon-o-banknotes')
                                    ->placeholder('No account assigned'),

                                TextEntry::make('user.email')
                                    ->label('User Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),

                                TextEntry::make('bankAccount.account_holder_name')
                                    ->label('Account Holder')
                                    ->placeholder('N/A')
                                    ->icon('heroicon-o-user-circle'),
                            ]),
                    ]),

                Section::make('Timeline')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime()
                                    ->icon('heroicon-o-clock'),

                                TextEntry::make('completed_at')
                                    ->label('Completed At')
                                    ->dateTime()
                                    ->placeholder('Not completed yet')
                                    ->icon('heroicon-o-check-circle'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime()
                                    ->icon('heroicon-o-arrow-path'),

                                TextEntry::make('ip_address')
                                    ->label('IP Address')
                                    ->copyable()
                                    ->icon('heroicon-o-globe-alt')
                                    ->placeholder('Not recorded'),
                            ]),
                    ]),

                Section::make('Additional Information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Exchange ID')
                                    ->copyable()
                                    ->icon('heroicon-o-finger-print'),

                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
