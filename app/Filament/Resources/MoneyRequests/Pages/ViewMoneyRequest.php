<?php

namespace App\Filament\Resources\MoneyRequests\Pages;

use App\Filament\Resources\MoneyRequests\MoneyRequestResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ViewMoneyRequest extends ViewRecord
{
    protected static string $resource = MoneyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('accept')
                ->label('Accept Request')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending' && auth()->id() === $this->record->responder_id)
                ->requiresConfirmation()
                ->modalDescription('This will accept the money request. You commit to sending the requested amount.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'accepted',
                        'accepted_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Money request accepted')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'accepted_at']);
                }),

            Action::make('complete')
                ->label('Mark Completed')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn () => $this->record->status === 'accepted')
                ->requiresConfirmation()
                ->modalDescription('Mark this money request as completed? This means the payment has been sent.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Money request completed')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'completed_at']);
                }),

            Action::make('reject')
                ->label('Reject Request')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, ['pending', 'accepted']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    Notification::make()
                        ->title('Money request rejected')
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status', 'rejected_at', 'rejection_reason']);
                }),

            Actions\EditAction::make(),

            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Request Details')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reference_number')
                                    ->label('Reference Number')
                                    ->copyable()
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('type')
                                    ->label('Type')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                    ->color('primary'),

                                TextEntry::make('amount')
                                    ->label('Amount Requested')
                                    ->formatStateUsing(fn ($state, $record) => '$'.number_format($state / 100, 2).' '.$record->currency)
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'accepted' => 'info',
                                        'completed' => 'success',
                                        'rejected' => 'danger',
                                        'cancelled' => 'gray',
                                        'expired' => 'gray',
                                        default => 'gray',
                                    }),

                                TextEntry::make('reason')
                                    ->label('Reason')
                                    ->placeholder('No reason provided')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Parties Involved')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('requester.name')
                                    ->label('Requester Name')
                                    ->icon('heroicon-o-user')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('responder.name')
                                    ->label('Responder Name')
                                    ->icon('heroicon-o-user')
                                    ->placeholder('Not assigned yet')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('requester.email')
                                    ->label('Requester Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),

                                TextEntry::make('responder.email')
                                    ->label('Responder Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->placeholder('Not assigned yet'),
                            ]),
                    ]),

                Section::make('Timeline')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('accepted_at')
                                    ->label('Accepted At')
                                    ->dateTime()
                                    ->placeholder('Not accepted yet')
                                    ->icon('heroicon-o-check-circle'),

                                TextEntry::make('completed_at')
                                    ->label('Completed At')
                                    ->dateTime()
                                    ->placeholder('Not completed yet')
                                    ->icon('heroicon-o-check-badge'),

                                TextEntry::make('rejected_at')
                                    ->label('Rejected At')
                                    ->dateTime()
                                    ->placeholder('Not rejected')
                                    ->icon('heroicon-o-x-circle'),

                                TextEntry::make('expires_at')
                                    ->label('Expires At')
                                    ->date()
                                    ->placeholder('Never expires')
                                    ->icon('heroicon-o-clock')
                                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 'gray'),
                            ]),
                    ]),

                Section::make('Rejection Details')
                    ->icon('heroicon-o-information-circle')
                    ->visible(fn ($record) => $record->status === 'rejected')
                    ->schema([
                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->columnSpanFull(),
                    ]),

                Section::make('System Information')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Request ID')
                                    ->copyable()
                                    ->icon('heroicon-o-finger-print'),

                                TextEntry::make('currency')
                                    ->label('Currency')
                                    ->badge()
                                    ->color('info'),

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
