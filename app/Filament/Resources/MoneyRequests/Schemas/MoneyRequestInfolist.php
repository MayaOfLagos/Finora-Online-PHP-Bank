<?php

namespace App\Filament\Resources\MoneyRequests\Schemas;

use App\Models\MoneyRequest;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MoneyRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('requester.id')
                    ->label('Requester'),
                TextEntry::make('responder.id')
                    ->label('Responder')
                    ->placeholder('-'),
                TextEntry::make('reference_number'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('status'),
                TextEntry::make('reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('accepted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('rejected_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('rejection_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('expires_at')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('type'),
                TextEntry::make('metadata')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (MoneyRequest $record): bool => $record->trashed()),
            ]);
    }
}
