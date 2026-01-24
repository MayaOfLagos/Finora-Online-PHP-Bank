<?php

namespace App\Filament\Widgets;

use App\Models\DomesticTransfer;
use App\Models\InternalTransfer;
use App\Models\WireTransfer;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;

class TransfersChart extends ChartWidget
{
    protected ?string $heading = 'Transfers Overview';

    protected static ?int $sort = 21;

    protected int|string|array $columnSpan = '3xl';

    protected ?string $maxHeight = '300px';

    public ?string $filter = 'month';

    public ?string $chartType = 'bar';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last 7 Days',
            'month' => 'Last 30 Days',
            'quarter' => 'Last 3 Months',
            'year' => 'This Year',
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('chartType')
                ->label('Chart Type')
                ->options([
                    'bar' => 'Bar Chart',
                    'line' => 'Line Chart',
                    'pie' => 'Pie Chart',
                ])
                ->default('bar')
                ->reactive(),
        ];
    }

    protected function getData(): array
    {
        $period = $this->getPeriod();
        $startDate = $period['start'];
        $endDate = $period['end'];

        // Get wire transfers data
        $wireData = WireTransfer::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Get internal transfers data
        $internalData = InternalTransfer::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Get domestic transfers data
        $domesticData = DomesticTransfer::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Generate labels and datasets
        $labels = [];
        $wireAmounts = [];
        $internalAmounts = [];
        $domesticAmounts = [];

        $currentDate = Carbon::parse($startDate);
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M j');

            $wireAmounts[] = ($wireData[$dateKey]->total ?? 0) / 100;
            $internalAmounts[] = ($internalData[$dateKey]->total ?? 0) / 100;
            $domesticAmounts[] = ($domesticData[$dateKey]->total ?? 0) / 100;

            $currentDate->addDay();
        }

        // Reduce labels for longer periods
        if (count($labels) > 15) {
            $step = ceil(count($labels) / 12);
            $reducedLabels = [];
            $reducedWire = [];
            $reducedInternal = [];
            $reducedDomestic = [];

            for ($i = 0; $i < count($labels); $i += $step) {
                $reducedLabels[] = $labels[$i];
                $reducedWire[] = array_sum(array_slice($wireAmounts, $i, $step));
                $reducedInternal[] = array_sum(array_slice($internalAmounts, $i, $step));
                $reducedDomestic[] = array_sum(array_slice($domesticAmounts, $i, $step));
            }

            $labels = $reducedLabels;
            $wireAmounts = $reducedWire;
            $internalAmounts = $reducedInternal;
            $domesticAmounts = $reducedDomestic;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Wire Transfers ($)',
                    'data' => $wireAmounts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Internal Transfers ($)',
                    'data' => $internalAmounts,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Domestic Transfers ($)',
                    'data' => $domesticAmounts,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return $this->chartType ?? 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return '$' + value.toLocaleString(); }",
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
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
