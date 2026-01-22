<?php

namespace App\Filament\Resources\ExchangeMoney\Schemas;

use App\Models\ExchangeMoney;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExchangeMoneyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('user.id')
                    ->label('User'),
                TextEntry::make('bankAccount.id')
                    ->label('Bank account'),
                TextEntry::make('reference_number'),
                TextEntry::make('from_currency'),
                TextEntry::make('to_currency'),
                TextEntry::make('from_amount')
                    ->numeric(),
                TextEntry::make('to_amount')
                    ->numeric(),
                TextEntry::make('exchange_rate')
                    ->numeric(),
                TextEntry::make('fee')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('ip_address')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (ExchangeMoney $record): bool => $record->trashed()),
            ]);
    }
}
