<?php

namespace App\Filament\Resources\Rewards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
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
                TextColumn::make('redeemed_at')
                    ->dateTime()
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
                ViewAction::make(),
                EditAction::make(),
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
}
