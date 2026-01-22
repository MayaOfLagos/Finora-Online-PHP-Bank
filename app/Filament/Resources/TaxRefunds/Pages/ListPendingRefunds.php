<?php

namespace App\Filament\Resources\TaxRefunds\Pages;

use App\Filament\Resources\TaxRefunds\TaxRefundResource;
use App\Models\TaxRefund;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListPendingRefunds extends ListRecords
{
    protected static string $resource = TaxRefundResource::class;

    protected static ?string $title = 'Pending Refunds';

    protected static ?string $navigationLabel = 'Pending Refunds';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 2;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return TaxRefund::query()->where('status', 'pending');
    }

    public static function getNavigationBadge(): ?string
    {
        return TaxRefund::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
