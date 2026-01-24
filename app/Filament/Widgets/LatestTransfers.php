<?php

namespace App\Filament\Widgets;

use App\Models\WireTransfer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTransfers extends TableWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = '3xl';

    protected static ?string $heading = 'Latest Wire Transfers';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => WireTransfer::query()->latest()->limit(5))
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable(),
                TextColumn::make('user.full_name')
                    ->label('User')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('beneficiary_name')
                    ->label('Beneficiary'),
                TextColumn::make('amount')
                    ->money('USD', divideBy: 100)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'info' => 'processing',
                    ]),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
