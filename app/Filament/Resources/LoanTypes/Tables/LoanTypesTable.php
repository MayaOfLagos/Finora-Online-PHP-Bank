<?php

namespace App\Filament\Resources\LoanTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoanTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Program Name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('min_amount')
                    ->label('Min Amount')
                    ->money('USD', divideBy: 100)
                    ->sortable(),

                TextColumn::make('max_amount')
                    ->label('Max Amount')
                    ->money('USD', divideBy: 100)
                    ->sortable(),

                TextColumn::make('min_term_months')
                    ->label('Min Term')
                    ->suffix(' mo')
                    ->sortable(),

                TextColumn::make('max_term_months')
                    ->label('Max Term')
                    ->suffix(' mo')
                    ->sortable(),

                TextColumn::make('interest_rate')
                    ->label('Interest Rate')
                    ->suffix('%')
                    ->sortable()
                    ->color('warning')
                    ->weight(FontWeight::Bold),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ])
                    ->placeholder('All Programs'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
