<?php

namespace App\Http\Controllers;

use App\Enums\CardStatus;
use App\Enums\LoanStatus;
use App\Enums\TicketStatus;
use App\Enums\TransferStatus;
use App\Models\TransactionHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Load relationships
        $user->load([
            'bankAccounts.accountType',
            'cards.cardType',
            'cards.bankAccount',
        ]);

        // Get accounts data
        $accounts = $user->bankAccounts->map(function ($account) {
            return [
                'id' => $account->id,
                'uuid' => $account->uuid,
                'account_name' => $account->accountType?->name ?? 'Bank Account',
                'account_number' => $account->account_number,
                'balance' => $account->balance,
                'currency' => $account->currency,
                'type' => $account->accountType?->slug ?? 'checking',
                'is_primary' => $account->is_primary,
                'is_active' => $account->is_active,
            ];
        });

        // Get cards data
        $cards = $user->cards->map(function ($card) {
            return [
                'id' => $card->id,
                'uuid' => $card->uuid,
                'card_type' => $card->cardType?->name ?? 'Debit Card',
                'card_number' => $card->masked_card_number,
                'card_holder_name' => $card->card_holder_name,
                'expiry' => $card->expiry_month && $card->expiry_year
                    ? $card->expiry_month.'/'.substr($card->expiry_year, -2)
                    : null,
                'status' => $card->status->value,
                'status_label' => $card->status->label(),
                'status_color' => $card->status->color(),
                'is_virtual' => $card->is_virtual,
                'spending_limit' => $card->spending_limit,
                'daily_limit' => $card->daily_limit,
                'bank_account' => $card->bankAccount ? [
                    'name' => $card->bankAccount->accountType?->name,
                    'number' => $card->bankAccount->account_number,
                ] : null,
            ];
        });

        // Get recent transactions
        $recentTransactions = TransactionHistory::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'uuid' => $transaction->uuid,
                    'type' => $transaction->transaction_type,
                    'description' => $transaction->description,
                    'amount' => (int) ($transaction->amount * 100), // Convert to cents
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'reference' => $transaction->reference_number,
                    'created_at' => $transaction->created_at->toISOString(),
                ];
            });

        // Calculate stats
        $stats = $this->calculateStats($user);

        // Get chart data
        $chartData = $this->getChartData($user);

        // Get pending items counts
        $pendingItems = $this->getPendingItems($user);

        // Get quick stats for cards
        $cardStats = [
            'total' => $user->cards->count(),
            'active' => $user->cards->where('status', CardStatus::Active)->count(),
            'virtual' => $user->cards->where('is_virtual', true)->count(),
            'physical' => $user->cards->where('is_virtual', false)->count(),
        ];

        return Inertia::render('Dashboard', [
            'accounts' => $accounts,
            'cards' => $cards,
            'recentTransactions' => $recentTransactions,
            'stats' => $stats,
            'chartData' => $chartData,
            'pendingItems' => $pendingItems,
            'cardStats' => $cardStats,
        ]);
    }

    /**
     * Calculate dashboard statistics.
     */
    private function calculateStats($user): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Total balance across all accounts
        $totalBalance = $user->bankAccounts->sum('balance');

        // Monthly income (deposits + incoming transfers)
        $monthlyIncome = TransactionHistory::where('user_id', $user->id)
            ->where('created_at', '>=', $startOfMonth)
            ->whereIn('transaction_type', ['deposit', 'check_deposit', 'mobile_deposit', 'crypto_deposit', 'internal_transfer_received'])
            ->where('status', 'completed')
            ->sum(DB::raw('CAST(amount AS DECIMAL(15,2)) * 100'));

        // Last month income for comparison
        $lastMonthIncome = TransactionHistory::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->whereIn('transaction_type', ['deposit', 'check_deposit', 'mobile_deposit', 'crypto_deposit', 'internal_transfer_received'])
            ->where('status', 'completed')
            ->sum(DB::raw('CAST(amount AS DECIMAL(15,2)) * 100'));

        // Monthly expenses (transfers + withdrawals)
        $monthlyExpenses = TransactionHistory::where('user_id', $user->id)
            ->where('created_at', '>=', $startOfMonth)
            ->whereIn('transaction_type', ['wire_transfer', 'internal_transfer', 'domestic_transfer', 'account_transfer', 'withdrawal'])
            ->where('status', 'completed')
            ->sum(DB::raw('CAST(amount AS DECIMAL(15,2)) * 100'));

        // Last month expenses for comparison
        $lastMonthExpenses = TransactionHistory::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->whereIn('transaction_type', ['wire_transfer', 'internal_transfer', 'domestic_transfer', 'account_transfer', 'withdrawal'])
            ->where('status', 'completed')
            ->sum(DB::raw('CAST(amount AS DECIMAL(15,2)) * 100'));

        // Pending transfers count
        $pendingTransfers = $user->wireTransfers()->where('status', TransferStatus::Pending)->count()
            + $user->sentInternalTransfers()->where('status', TransferStatus::Pending)->count()
            + $user->domesticTransfers()->where('status', TransferStatus::Pending)->count();

        // Calculate trends
        $incomeTrend = $lastMonthIncome > 0 ? (($monthlyIncome - $lastMonthIncome) / $lastMonthIncome) * 100 : 0;
        $expensesTrend = $lastMonthExpenses > 0 ? (($monthlyExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100 : 0;

        return [
            'totalBalance' => (int) $totalBalance,
            'monthlyIncome' => (int) $monthlyIncome,
            'monthlyExpenses' => (int) $monthlyExpenses,
            'pendingTransfers' => $pendingTransfers,
            'incomeTrend' => round($incomeTrend, 1),
            'expensesTrend' => round($expensesTrend, 1),
        ];
    }

    /**
     * Get chart data for the dashboard.
     */
    private function getChartData($user): array
    {
        // Get last 6 months of transaction data
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i));
        }

        $labels = $months->map(fn ($m) => $m->format('M'))->toArray();
        $incomeData = [];
        $expenseData = [];

        foreach ($months as $month) {
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            // Income for this month
            $income = TransactionHistory::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->whereIn('transaction_type', ['deposit', 'check_deposit', 'mobile_deposit', 'crypto_deposit', 'internal_transfer_received'])
                ->where('status', 'completed')
                ->sum(DB::raw('CAST(amount AS DECIMAL(15,2))'));

            // Expenses for this month
            $expenses = TransactionHistory::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->whereIn('transaction_type', ['wire_transfer', 'internal_transfer', 'domestic_transfer', 'account_transfer', 'withdrawal'])
                ->where('status', 'completed')
                ->sum(DB::raw('CAST(amount AS DECIMAL(15,2))'));

            $incomeData[] = (float) $income;
            $expenseData[] = (float) $expenses;
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expenses' => $expenseData,
        ];
    }

    /**
     * Get counts of pending items.
     */
    private function getPendingItems($user): array
    {
        return [
            'deposits' => $user->checkDeposits()->where('status', 'pending')->count()
                + $user->mobileDeposits()->where('status', 'pending')->count()
                + $user->cryptoDeposits()->where('status', 'pending')->count(),
            'loans' => $user->loanApplications()->whereIn('status', [LoanStatus::Pending, LoanStatus::UnderReview])->count(),
            'tickets' => $user->supportTickets()->whereIn('status', [TicketStatus::Open, TicketStatus::InProgress])->count(),
            'cardRequests' => 0,
            'grants' => $user->grantApplications()->whereIn('status', [\App\Enums\GrantApplicationStatus::Pending, \App\Enums\GrantApplicationStatus::UnderReview])->count(),
        ];
    }
}
