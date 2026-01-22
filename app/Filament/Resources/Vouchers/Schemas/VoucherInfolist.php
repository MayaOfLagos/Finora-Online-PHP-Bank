<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use App\Models\Voucher;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VoucherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('user.id')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('code'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('type'),
                TextEntry::make('status'),
                TextEntry::make('usage_limit')
                    ->numeric(),
                TextEntry::make('times_used')
                    ->numeric(),
                IconEntry::make('is_used')
                    ->boolean(),
                TextEntry::make('expires_at')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('used_at')
                    ->dateTime()
                    ->placeholder('-'),
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
                    ->visible(fn (Voucher $record): bool => $record->trashed()),
            ]);
    }
}
