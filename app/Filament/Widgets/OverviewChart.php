<?php

namespace App\Filament\Widgets;

use App\Models\CheckDeposit;
use App\Models\CryptoDeposit;
use App\Models\DomesticTransfer;
use App\Models\GrantApplication;
use App\Models\InternalTransfer;
use App\Models\LoanApplication;
use App\Models\MobileDeposit;
use App\Models\User;
use App\Models\WireTransfer;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class OverviewChart extends ChartWidget
{
    protected ?string $heading = 'Platform Activity Overview';

    protected static ?int $sort = 23;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '350px';

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
        $dates = $period['dates'];

        // Get daily activity data for each category
        $transfersData = $this->getTransfersData($startDate, $endDate, $dates);
        $depositsData = $this->getDepositsData($startDate, $endDate, $dates);
        $loansData = $this->getLoansData($startDate, $endDate, $dates);
        $usersData = $this->getUsersData($startDate, $endDate, $dates);

        return [
            'datasets' => [
                [
                    'label' => 'Transfers',
                    'data' => array_values($transfersData),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Deposits',
                    'data' => array_values($depositsData),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Loan Applications',
                    'data' => array_values($loansData),
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'New Users',
                    'data' => array_values($usersData),
                    'borderColor' => 'rgb(249, 115, 22)',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => array_map(function ($date) {
                return Carbon::parse($date)->format('M d');
            }, array_keys($transfersData)),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }

    protected function getTransfersData(Carbon $startDate, Carbon $endDate, array $dates): array
    {
        $data = array_fill_keys($dates, 0);

        // Wire transfers
        $wireTransfers = WireTransfer::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Internal transfers
        $internalTransfers = InternalTransfer::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Domestic transfers
        $domesticTransfers = DomesticTransfer::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        foreach ($dates as $date) {
            $data[$date] = ($wireTransfers[$date] ?? 0)
                         + ($internalTransfers[$date] ?? 0)
                         + ($domesticTransfers[$date] ?? 0);
        }

        return $data;
    }

    protected function getDepositsData(Carbon $startDate, Carbon $endDate, array $dates): array
    {
        $data = array_fill_keys($dates, 0);

        // Check deposits
        $checkDeposits = CheckDeposit::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Mobile deposits
        $mobileDeposits = MobileDeposit::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Crypto deposits
        $cryptoDeposits = CryptoDeposit::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        foreach ($dates as $date) {
            $data[$date] = ($checkDeposits[$date] ?? 0)
                         + ($mobileDeposits[$date] ?? 0)
                         + ($cryptoDeposits[$date] ?? 0);
        }

        return $data;
    }

    protected function getLoansData(Carbon $startDate, Carbon $endDate, array $dates): array
    {
        $data = array_fill_keys($dates, 0);

        $loanApplications = LoanApplication::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $grantApplications = GrantApplication::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        foreach ($dates as $date) {
            $data[$date] = ($loanApplications[$date] ?? 0) + ($grantApplications[$date] ?? 0);
        }

        return $data;
    }

    protected function getUsersData(Carbon $startDate, Carbon $endDate, array $dates): array
    {
        $data = array_fill_keys($dates, 0);

        $users = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        foreach ($dates as $date) {
            $data[$date] = $users[$date] ?? 0;
        }

        return $data;
    }

    protected function getPeriod(): array
    {
        $endDate = now()->endOfDay();

        $startDate = match ($this->filter) {
            'week' => now()->subDays(6)->startOfDay(),
            'month' => now()->subDays(29)->startOfDay(),
            'quarter' => now()->subMonths(3)->startOfDay(),
            'year' => now()->startOfYear()->startOfDay(),
            default => now()->subDays(29)->startOfDay(),
        };

        // Generate date range
        $dates = [];
        $currentDate = $startDate->copy();

        // For year view, group by week
        if ($this->filter === 'year' || $this->filter === 'quarter') {
            while ($currentDate <= $endDate) {
                $dates[] = $currentDate->format('Y-m-d');
                $currentDate->addWeek();
            }
        } else {
            while ($currentDate <= $endDate) {
                $dates[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'dates' => $dates,
        ];
    }

    public static function canView(): bool
    {
        return true;
    }
}
