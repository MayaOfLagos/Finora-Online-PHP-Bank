<?php

namespace App\Filament\Resources\Rewards\Schemas;

use App\Models\Reward;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RewardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('user.id')
                    ->label('User'),
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('points')
                    ->numeric(),
                TextEntry::make('type'),
                TextEntry::make('status'),
                TextEntry::make('earned_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('expiry_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('redeemed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('source')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
                    ->visible(fn (Reward $record): bool => $record->trashed()),
            ]);
    }
}
