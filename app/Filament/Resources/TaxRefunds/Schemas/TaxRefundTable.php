<?php

namespace App\Filament\Resources\TaxRefunds\Schemas;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxRefundTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('tax_year')
                    ->label('Tax Year')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('refund_amount')
                    ->label('Refund Amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('irs_reference_number')
                    ->label('IRS Reference')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
