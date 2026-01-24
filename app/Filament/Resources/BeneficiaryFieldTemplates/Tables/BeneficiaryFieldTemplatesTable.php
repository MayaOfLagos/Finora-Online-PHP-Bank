<?php

namespace App\Filament\Resources\BeneficiaryFieldTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BeneficiaryFieldTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),
                TextColumn::make('field_label')
                    ->label('Field')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('field_key')
                    ->label('Key')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('field_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'info',
                        'textarea' => 'warning',
                        'select' => 'success',
                        'country' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('applies_to')
                    ->label('Transfer Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wire' => 'danger',
                        'domestic' => 'success',
                        'internal' => 'info',
                        'all' => 'gray',
                        default => 'gray',
                    }),
                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('applies_to')
                    ->options([
                        'wire' => 'Wire Transfer',
                        'domestic' => 'Domestic Transfer',
                        'internal' => 'Internal Transfer',
                        'all' => 'All Types',
                    ])
                    ->label('Transfer Type'),
                SelectFilter::make('field_type')
                    ->options([
                        'text' => 'Text Input',
                        'textarea' => 'Textarea',
                        'select' => 'Select',
                        'country' => 'Country',
                    ])
                    ->label('Field Type'),
                SelectFilter::make('is_enabled')
                    ->options([
                        true => 'Enabled',
                        false => 'Disabled',
                    ])
                    ->label('Status'),
            ])
            ->defaultSort('display_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
