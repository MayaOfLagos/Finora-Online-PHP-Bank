<?php

namespace App\Filament\Resources\ReferralLevels\Tables;

use App\Models\ReferralLevel;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReferralLevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->sortable()
                    ->badge()
                    ->color(fn (ReferralLevel $record) => $record->color ? Color::hex($record->color) : 'gray'),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->icon(fn (ReferralLevel $record) => $record->icon ?? 'heroicon-o-star'),
                ColorColumn::make('color')
                    ->label('Color')
                    ->copyable(),
                TextColumn::make('reward_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => $state === 'fixed' ? 'success' : 'info'),
                TextColumn::make('reward_amount')
                    ->label('Reward')
                    ->formatStateUsing(function (ReferralLevel $record) {
                        if ($record->reward_type === 'percentage') {
                            return $record->reward_amount.'%';
                        }

                        return '$'.number_format($record->reward_amount / 100, 2);
                    })
                    ->badge()
                    ->color('success'),
                TextColumn::make('min_referrals_required')
                    ->label('Min. Referrals')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('referrals_count')
                    ->label('Active Users')
                    ->counts('referrals')
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon(Heroicon::SolidCheckCircle)
                    ->falseIcon(Heroicon::SolidXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultSort('level', 'asc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->placeholder('All'),
                SelectFilter::make('reward_type')
                    ->options([
                        'fixed' => 'Fixed Amount',
                        'percentage' => 'Percentage',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalWidth(Width::TwoExtraLarge)
                        ->icon(Heroicon::OutlinedPencilSquare),
                    Action::make('toggle_status')
                        ->label(fn (ReferralLevel $record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn (ReferralLevel $record) => $record->is_active
                            ? Heroicon::OutlinedXCircle
                            : Heroicon::OutlinedCheckCircle)
                        ->color(fn (ReferralLevel $record) => $record->is_active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn (ReferralLevel $record) => ($record->is_active ? 'Deactivate' : 'Activate')." Level: {$record->name}")
                        ->modalDescription(fn (ReferralLevel $record) => $record->is_active
                            ? 'This level will no longer be used for reward calculations.'
                            : 'This level will be used for reward calculations.')
                        ->action(fn (ReferralLevel $record) => $record->update(['is_active' => ! $record->is_active])),
                    DeleteAction::make()
                        ->icon(Heroicon::OutlinedTrash),
                ]),
            ])
            ->bulkActions([])
            ->emptyStateIcon(Heroicon::OutlinedChartBar)
            ->emptyStateHeading('No Referral Levels')
            ->emptyStateDescription('Get started by creating your first referral level or use the quick setup to auto-generate levels.')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
