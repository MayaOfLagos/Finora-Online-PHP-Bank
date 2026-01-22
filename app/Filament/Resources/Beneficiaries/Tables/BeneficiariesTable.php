<?php

namespace App\Filament\Resources\Beneficiaries\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BeneficiariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Account Owner')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable()
                    ->description(fn ($record) => $record->user->email),
                TextColumn::make('beneficiaryUser.name')
                    ->label('Beneficiary')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable()
                    ->description(fn ($record) => $record->beneficiaryUser->email),
                TextColumn::make('beneficiaryAccount.account_number')
                    ->label('Account Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Account number copied')
                    ->description(fn ($record) => $record->beneficiaryAccount->accountType->name ?? 'N/A'),
                TextColumn::make('nickname')
                    ->label('Nickname')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->weight('semibold'),
                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_favorite')
                    ->label('Favorite')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('transfer_limit')
                    ->label('Transfer Limit')
                    ->numeric()
                    ->sortable()
                    ->money('USD', divideBy: 100)
                    ->placeholder('No limit'),
                TextColumn::make('last_used_at')
                    ->label('Last Used')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder('Never used'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary'),
                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
