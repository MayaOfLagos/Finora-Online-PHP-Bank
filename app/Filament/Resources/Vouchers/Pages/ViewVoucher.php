<?php

namespace App\Filament\Resources\Vouchers\Pages;

use App\Filament\Resources\Vouchers\VoucherResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class ViewVoucher extends ViewRecord
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('copy_code')
                ->label('Copy Code')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->action(function () {
                    Notification::make()
                        ->title('Code copied to clipboard!')
                        ->success()
                        ->send();
                })
                ->extraAttributes(fn () => [
                    'x-data' => '{}',
                    'x-on:click' => 'navigator.clipboard.writeText("'.$this->record->code.'")',
                ]),

            Action::make('mark_used')
                ->label('Mark as Used')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'active' && ! $this->record->is_used)
                ->requiresConfirmation()
                ->modalDescription('This will mark the voucher as used and cannot be undone.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'used',
                        'is_used' => true,
                        'used_at' => now(),
                        'times_used' => $this->record->times_used + 1,
                    ]);

                    Notification::make()
                        ->title('Voucher marked as used')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'is_used', 'used_at', 'times_used']);
                }),

            Action::make('cancel')
                ->label('Cancel Voucher')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'active')
                ->requiresConfirmation()
                ->modalDescription('This will permanently cancel the voucher.')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);

                    Notification::make()
                        ->title('Voucher cancelled')
                        ->success()
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
                Grid::make(2)
                    ->schema([
                        Section::make('Voucher Information')
                            ->icon('heroicon-o-ticket')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('code')
                                            ->label('Voucher Code')
                                            ->weight(FontWeight::Bold)
                                            ->copyable()
                                            ->copyMessage('Voucher code copied!')
                                            ->badge()
                                            ->color('primary'),

                                        TextEntry::make('type')
                                            ->label('Type')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                            ->color(fn (string $state): string => match ($state) {
                                                'discount' => 'info',
                                                'cashback' => 'success',
                                                'bonus' => 'warning',
                                                'referral' => 'primary',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('amount')
                                            ->label('Value')
                                            ->formatStateUsing(fn ($state, $record) => '$'.number_format($state / 100, 2).' '.$record->currency)
                                            ->weight(FontWeight::Bold)
                                            ->color('success'),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                            ->color(fn (string $state): string => match ($state) {
                                                'active' => 'success',
                                                'used' => 'info',
                                                'expired' => 'warning',
                                                'cancelled' => 'danger',
                                                default => 'gray',
                                            }),
                                    ]),

                                TextEntry::make('description')
                                    ->label('Description')
                                    ->placeholder('No description provided')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Status & Usage')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                IconEntry::make('is_used')
                                    ->label('Used')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('gray'),

                                TextEntry::make('times_used')
                                    ->label('Times Used')
                                    ->formatStateUsing(fn ($state, $record) => $state.' / '.$record->usage_limit)
                                    ->badge()
                                    ->color(fn ($state, $record) => $state >= $record->usage_limit ? 'danger' : 'success'),

                                TextEntry::make('expires_at')
                                    ->label('Expires')
                                    ->date()
                                    ->placeholder('Never')
                                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 'gray'),

                                TextEntry::make('used_at')
                                    ->label('Used At')
                                    ->dateTime()
                                    ->placeholder('Not used yet'),
                            ]),
                    ]),

                Section::make('User & Timestamps')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Assigned User')
                                    ->placeholder('Not assigned')
                                    ->icon('heroicon-o-user')
                                    ->url(fn ($record) => $record->user_id
                                        ? route('filament.admin.resources.users.index', ['tableSearch' => $record->user->email])
                                        : null),

                                TextEntry::make('user.email')
                                    ->label('User Email')
                                    ->placeholder('N/A')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime()
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime()
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ]),

                Section::make('Metadata')
                    ->icon('heroicon-o-code-bracket')
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($record) => ! empty($record->metadata))
                    ->schema([
                        TextEntry::make('metadata')
                            ->label('')
                            ->formatStateUsing(fn ($state) => new HtmlString(
                                '<pre class="text-sm bg-gray-100 dark:bg-gray-800 p-4 rounded-lg overflow-x-auto">'.
                                json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).
                                '</pre>'
                            ))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
