<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->default('General'),
                TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency, divideBy: 100)
                    ->sortable()
                    ->color('success'),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'info' => 'discount',
                        'success' => 'cashback',
                        'warning' => 'bonus',
                        'primary' => 'referral',
                    ]),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'gray' => 'used',
                        'danger' => 'expired',
                    ]),
                TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('times_used')
                    ->label('Used')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_used')
                    ->boolean()
                    ->label('Redeemed'),
                TextColumn::make('expires_at')
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
                        'active' => 'Active',
                        'used' => 'Used',
                        'expired' => 'Expired',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'discount' => 'Discount',
                        'cashback' => 'Cashback',
                        'bonus' => 'Bonus',
                        'referral' => 'Referral',
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
