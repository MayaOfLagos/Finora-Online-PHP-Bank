<?php

namespace App\Filament\Resources\TransactionHistories;

use App\Filament\Resources\TransactionHistories\Pages\GenerateTransaction;
use App\Filament\Resources\TransactionHistories\Pages\ViewHistory;
use App\Models\TransactionHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class TransactionHistoryResource extends Resource
{
    protected static ?string $model = TransactionHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Transaction History';

    protected static ?int $navigationSort = 5;

    public static function getPages(): array
    {
        return [
            'index' => ViewHistory::route('/'),
            'generate' => GenerateTransaction::route('/generate'),
        ];
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return static::getUrl('index', $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }

    public static function getNavigationItems(): array
    {
        return [
            \Filament\Navigation\NavigationItem::make(static::getNavigationLabel())
                ->icon(static::getNavigationIcon())
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.transaction-histories.*'))
                ->url(static::getUrl('index')),
            \Filament\Navigation\NavigationItem::make('Generate Transaction')
                ->icon('heroicon-o-plus-circle')
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort() + 1)
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.transaction-histories.generate'))
                ->url(static::getUrl('generate')),
        ];
    }
}
