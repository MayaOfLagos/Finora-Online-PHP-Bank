<?php

namespace App\Filament\Resources\Referrals\Tables;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->limit(8),

                TextColumn::make('referrer.full_name')
                    ->label('Referrer')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->description(fn (Referral $record) => $record->referrer?->email)
                    ->weight(FontWeight::Bold),

                TextColumn::make('referred.full_name')
                    ->label('New User')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->description(fn (Referral $record) => $record->referred?->email),

                TextColumn::make('referral_code_used')
                    ->label('Code Used')
                    ->badge()
                    ->color('gray')
                    ->copyable(),

                TextColumn::make('referrerLevel.name')
                    ->label('Level')
                    ->badge()
                    ->color(fn (Referral $record) => $record->referrerLevel?->color
                        ? Color::hex($record->referrerLevel->color)
                        : 'gray'),

                TextColumn::make('referrer_earned')
                    ->label('Referrer Earned')
                    ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2))
                    ->badge()
                    ->color('success'),

                TextColumn::make('referred_earned')
                    ->label('User Earned')
                    ->formatStateUsing(fn ($state) => $state > 0 ? '$'.number_format($state / 100, 2) : '-')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'info' : 'gray'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ReferralStatus $state) => $state->label())
                    ->color(fn (ReferralStatus $state) => $state->color())
                    ->icon(fn (ReferralStatus $state) => $state->icon()),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ReferralStatus::cases())->mapWithKeys(
                        fn (ReferralStatus $status) => [$status->value => $status->label()]
                    )),
                SelectFilter::make('referrer_level_id')
                    ->label('Level')
                    ->relationship('referrerLevel', 'name'),
                Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query) => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)),
                Filter::make('has_user_bonus')
                    ->label('With User Bonus')
                    ->query(fn (Builder $query) => $query->where('referred_earned', '>', 0)),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('View Details')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('info')
                        ->modalHeading(fn (Referral $record) => "Referral #{$record->uuid}")
                        ->modalWidth(Width::TwoExtraLarge)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->infolist([
                            Section::make('Referrer Information')
                                ->icon('heroicon-o-user')
                                ->columns(3)
                                ->schema([
                                    ImageEntry::make('referrer.avatar_url')
                                        ->label('')
                                        ->circular()
                                        ->size(60),
                                    Group::make([
                                        TextEntry::make('referrer.full_name')
                                            ->label('Name')
                                            ->weight(FontWeight::Bold),
                                        TextEntry::make('referrer.email')
                                            ->label('Email')
                                            ->copyable(),
                                    ]),
                                    Group::make([
                                        TextEntry::make('referrerLevel.name')
                                            ->label('Level at Time')
                                            ->badge(),
                                        TextEntry::make('referral_code_used')
                                            ->label('Code Used')
                                            ->badge()
                                            ->color('gray'),
                                    ]),
                                ]),

                            Section::make('New User Information')
                                ->icon('heroicon-o-user-plus')
                                ->columns(3)
                                ->schema([
                                    ImageEntry::make('referred.avatar_url')
                                        ->label('')
                                        ->circular()
                                        ->size(60),
                                    Group::make([
                                        TextEntry::make('referred.full_name')
                                            ->label('Name')
                                            ->weight(FontWeight::Bold),
                                        TextEntry::make('referred.email')
                                            ->label('Email')
                                            ->copyable(),
                                    ]),
                                    Group::make([
                                        TextEntry::make('referred.created_at')
                                            ->label('Joined')
                                            ->dateTime('M j, Y g:i A'),
                                    ]),
                                ]),

                            Section::make('Rewards')
                                ->icon('heroicon-o-gift')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('referrer_earned')
                                        ->label('Referrer Earned')
                                        ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2))
                                        ->badge()
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->color('success'),
                                    TextEntry::make('referred_earned')
                                        ->label('New User Earned')
                                        ->formatStateUsing(fn ($state) => $state > 0 ? '$'.number_format($state / 100, 2) : 'N/A')
                                        ->badge()
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->color(fn ($state) => $state > 0 ? 'info' : 'gray'),
                                    TextEntry::make('total_earnings')
                                        ->label('Total Payout')
                                        ->formatStateUsing(fn (Referral $record) => '$'.number_format(($record->referrer_earned + $record->referred_earned) / 100, 2))
                                        ->badge()
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->color('warning'),
                                ]),

                            Section::make('Status & Timeline')
                                ->icon('heroicon-o-clock')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('status')
                                        ->badge()
                                        ->formatStateUsing(fn (ReferralStatus $state) => $state->label())
                                        ->color(fn (ReferralStatus $state) => $state->color()),
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime('M j, Y g:i A'),
                                    TextEntry::make('completed_at')
                                        ->label('Completed')
                                        ->dateTime('M j, Y g:i A')
                                        ->placeholder('Not completed'),
                                ]),
                        ]),

                    Action::make('cancel')
                        ->label('Cancel Referral')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Referral')
                        ->modalDescription('Are you sure you want to cancel this referral? This will mark the referral as cancelled.')
                        ->visible(fn (Referral $record) => $record->status === ReferralStatus::Pending)
                        ->action(fn (Referral $record) => $record->markCancelled('Cancelled by admin')),
                ]),
            ])
            ->bulkActions([])
            ->emptyStateIcon(Heroicon::OutlinedUserGroup)
            ->emptyStateHeading('No Referrals Yet')
            ->emptyStateDescription('Referrals will appear here when users sign up using referral codes.')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
