<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(),
                TextColumn::make('accountType.name')
                    ->label('Account Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account_number')
                    ->label('Account Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('opened_at')
                    ->label('Opened')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
