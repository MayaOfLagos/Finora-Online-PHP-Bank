<?php

namespace App\Filament\Widgets;

use App\Models\CheckDeposit;
use App\Models\CryptoDeposit;
use App\Models\MobileDeposit;
use Filament\Widgets\ChartWidget;

class DepositsChart extends ChartWidget
{
    protected ?string $heading = 'Deposits Overview';

    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last 7 Days',
            'month' => 'Last 30 Days',
            'quarter' => 'Last 3 Months',
            'year' => 'This Year',
        ];
    }

    protected function getData(): array
    {
        $period = $this->getPeriod();
        $startDate = $period['start'];
        $endDate = $period['end'];

        // Get totals by type
        $checkTotal = CheckDeposit::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount') / 100;

        $mobileTotal = MobileDeposit::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount') / 100;

        $cryptoTotal = CryptoDeposit::whereBetween('created_at', [$startDate, $endDate])
            ->sum('usd_amount') / 100;

        // Get counts
        $checkCount = CheckDeposit::whereBetween('created_at', [$startDate, $endDate])->count();
        $mobileCount = MobileDeposit::whereBetween('created_at', [$startDate, $endDate])->count();
        $cryptoCount = CryptoDeposit::whereBetween('created_at', [$startDate, $endDate])->count();

        return [
            'datasets' => [
                [
                    'label' => 'Amount ($)',
                    'data' => [$checkTotal, $mobileTotal, $cryptoTotal],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',  // Blue for Check
                        'rgba(16, 185, 129, 0.7)',  // Green for Mobile
                        'rgba(245, 158, 11, 0.7)', // Orange for Crypto
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                "Check ({$checkCount})",
                "Mobile ({$mobileCount})",
                "Crypto ({$cryptoCount})",
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) { return context.label + ': $' + context.parsed.toLocaleString(); }",
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }

    protected function getPeriod(): array
    {
        return match ($this->filter) {
            'week' => [
                'start' => now()->subDays(7)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'month' => [
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'quarter' => [
                'start' => now()->subMonths(3)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfDay(),
            ],
            default => [
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
        };
    }

    public static function canView(): bool
    {
        return true;
    }
}
