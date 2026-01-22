<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Get all accounts for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $accounts = $request->user()
            ->accounts()
            ->where('is_active', true)
            ->with('accountType')
            ->get()
            ->map(fn ($account) => $this->formatAccount($account));

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts,
            ],
        ]);
    }

    /**
     * Get a specific account.
     */
    public function show(Request $request, BankAccount $account): JsonResponse
    {
        // Ensure user owns this account
        if ($account->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to account.',
            ], 403);
        }

        $account->load('accountType');

        return response()->json([
            'success' => true,
            'data' => [
                'account' => $this->formatAccount($account, true),
            ],
        ]);
    }

    /**
     * Get account transactions.
     */
    public function transactions(Request $request, BankAccount $account): JsonResponse
    {
        // Ensure user owns this account
        if ($account->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to account.',
            ], 403);
        }

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'nullable|string|in:credit,debit',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Transaction::where('account_id', $account->id);

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
        ]);
    }

    /**
     * Generate account statement.
     */
    public function statement(Request $request, BankAccount $account): JsonResponse
    {
        // Ensure user owns this account
        if ($account->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to account.',
            ], 403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'nullable|string|in:json,pdf',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $transactions = Transaction::where('account_id', $account->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        // Calculate opening balance (sum of all transactions before start date)
        $openingBalance = Transaction::where('account_id', $account->id)
            ->where('created_at', '<', $startDate)
            ->sum(\DB::raw("CASE WHEN type = 'credit' THEN amount ELSE -amount END"));

        // Calculate totals
        $totalCredits = $transactions->where('type', 'credit')->sum('amount');
        $totalDebits = $transactions->where('type', 'debit')->sum('amount');
        $closingBalance = $openingBalance + $totalCredits - $totalDebits;

        $statementData = [
            'account' => $this->formatAccount($account),
            'user' => [
                'name' => $request->user()->full_name,
                'email' => $request->user()->email,
                'address' => $request->user()->address_line_1,
            ],
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'opening_balance' => $openingBalance / 100,
                'total_credits' => $totalCredits / 100,
                'total_debits' => $totalDebits / 100,
                'closing_balance' => $closingBalance / 100,
                'transaction_count' => $transactions->count(),
            ],
            'transactions' => $transactions->map(fn ($t) => [
                'date' => $t->created_at->format('Y-m-d H:i:s'),
                'reference' => $t->reference_number,
                'description' => $t->description,
                'type' => $t->type,
                'amount' => $t->amount / 100,
                'balance_after' => $t->balance_after / 100,
            ]),
            'generated_at' => now()->toIso8601String(),
        ];

        if ($request->format === 'pdf') {
            // For PDF generation, you'd use a PDF package
            // This returns a placeholder response
            return response()->json([
                'success' => true,
                'message' => 'PDF statement would be generated here',
                'data' => $statementData,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $statementData,
        ]);
    }

    /**
     * Set account as primary.
     */
    public function setPrimary(Request $request, BankAccount $account): JsonResponse
    {
        // Ensure user owns this account
        if ($account->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to account.',
            ], 403);
        }

        // Remove primary from all other accounts
        BankAccount::where('user_id', $request->user()->id)
            ->where('id', '!=', $account->id)
            ->update(['is_primary' => false]);

        // Set this account as primary
        $account->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Account set as primary successfully.',
        ]);
    }

    /**
     * Format account data for response.
     */
    private function formatAccount(BankAccount $account, bool $detailed = false): array
    {
        $data = [
            'uuid' => $account->uuid,
            'account_number' => $account->account_number,
            'type' => $account->accountType?->name ?? 'Unknown',
            'type_slug' => $account->accountType?->slug ?? 'unknown',
            'balance' => $account->balance / 100,
            'balance_formatted' => '$'.number_format($account->balance / 100, 2),
            'currency' => $account->currency,
            'is_primary' => $account->is_primary,
            'is_active' => $account->is_active,
            'opened_at' => $account->opened_at?->toIso8601String(),
        ];

        if ($detailed) {
            $data['limits'] = [
                'daily_transfer_limit' => ($account->accountType?->daily_transfer_limit ?? 0) / 100,
                'monthly_transfer_limit' => ($account->accountType?->monthly_transfer_limit ?? 0) / 100,
            ];
            $data['features'] = $account->accountType?->features ?? [];
        }

        return $data;
    }
}
