<?php

namespace App\Http\Controllers;

use App\Models\TransactionHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionHistoryController extends Controller
{
    /**
     * Display a listing of user's transaction history.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get filter parameters
        $accountId = $request->input('account');
        $type = $request->input('type');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $search = $request->input('search');

        // Build query
        $query = TransactionHistory::query()
            ->where('user_id', $user->id)
            ->with(['bankAccount:id,uuid,account_number,account_type_id,currency', 'bankAccount.accountType:id,name'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($accountId) {
            $query->whereHas('bankAccount', function ($q) use ($accountId) {
                $q->where('uuid', $accountId);
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Paginate results
        $transactions = $query->paginate(15)->through(function ($transaction) {
            return [
                'uuid' => $transaction->uuid,
                'reference_number' => $transaction->reference_number,
                'transaction_type' => $transaction->transaction_type,
                'type' => $transaction->type,
                'amount' => $transaction->amount * 100, // Convert to cents for frontend
                'balance_after' => $transaction->balance_after,
                'currency' => $transaction->currency,
                'status' => $transaction->status,
                'description' => $transaction->description,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $transaction->created_at->diffForHumans(),
                'bank_account' => $transaction->bankAccount ? [
                    'uuid' => $transaction->bankAccount->uuid,
                    'account_number' => $transaction->bankAccount->account_number,
                    'account_name' => $transaction->bankAccount->accountType?->name ?? 'Account',
                ] : null,
            ];
        });

        // Get user's accounts for filter dropdown
        $accounts = $user->bankAccounts()
            ->with('accountType:id,name')
            ->get()
            ->map(fn ($account) => [
                'uuid' => $account->uuid,
                'account_number' => $account->account_number,
                'account_name' => $account->accountType?->name ?? 'Account',
            ]);

        // Get transaction statistics
        $stats = [
            'total_transactions' => TransactionHistory::where('user_id', $user->id)->count(),
            'total_credits' => TransactionHistory::where('user_id', $user->id)
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_debits' => TransactionHistory::where('user_id', $user->id)
                ->where('type', 'debit')
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_transactions' => TransactionHistory::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
        ];

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'stats' => $stats,
            'filters' => [
                'account' => $accountId,
                'type' => $type,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display a specific transaction.
     */
    public function show(TransactionHistory $transaction): Response
    {
        $this->authorize('view', $transaction);

        return Inertia::render('Transactions/Show', [
            'transaction' => [
                'uuid' => $transaction->uuid,
                'reference_number' => $transaction->reference_number,
                'transaction_type' => $transaction->transaction_type,
                'type' => $transaction->type,
                'amount' => $transaction->amount * 100,
                'balance_after' => $transaction->balance_after,
                'currency' => $transaction->currency,
                'status' => $transaction->status,
                'description' => $transaction->description,
                'metadata' => $transaction->metadata,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                'processed_at' => $transaction->processed_at?->format('Y-m-d H:i:s'),
                'bank_account' => $transaction->bankAccount ? [
                    'uuid' => $transaction->bankAccount->uuid,
                    'account_number' => $transaction->bankAccount->account_number,
                    'account_name' => $transaction->bankAccount->account_name,
                ] : null,
            ],
        ]);
    }
}
