<?php

namespace App\Filament\Widgets;

use App\Enums\GrantApplicationStatus;
use App\Enums\LoanStatus;
use App\Models\GrantApplication;
use App\Models\Loan;
use App\Models\LoanApplication;
use Filament\Widgets\ChartWidget;

class LoansGrantsChart extends ChartWidget
{
    protected ?string $heading = 'Loans & Grants';

    protected static ?int $sort = 22;

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

        // Get loan statistics
        $loansPending = LoanApplication::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', LoanStatus::Pending)
            ->count();
        $loansApproved = LoanApplication::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', LoanStatus::Approved)
            ->count();
        $loansRejected = LoanApplication::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', LoanStatus::Rejected)
            ->count();
        $loansActive = Loan::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', LoanStatus::Active)
            ->count();

        // Get grant statistics
        $grantsPending = GrantApplication::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', GrantApplicationStatus::Pending)
            ->count();
        $grantsApproved = GrantApplication::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', GrantApplicationStatus::Approved)
            ->count();
        $grantsRejected = GrantApplication::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', GrantApplicationStatus::Rejected)
            ->count();
        $grantsDisbursed = GrantApplication::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', GrantApplicationStatus::Disbursed)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Loans',
                    'data' => [$loansPending, $loansApproved, $loansRejected, $loansActive],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Grants',
                    'data' => [$grantsPending, $grantsApproved, $grantsRejected, $grantsDisbursed],
                    'backgroundColor' => 'rgba(168, 85, 247, 0.7)',
                    'borderColor' => 'rgb(168, 85, 247)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Pending', 'Approved', 'Rejected', 'Active/Disbursed'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
            'barPercentage' => 0.6,
            'categoryPercentage' => 0.8,
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
