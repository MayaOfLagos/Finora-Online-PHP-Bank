<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class LoanRepaymentController extends Controller
{
    /**
     * Store a pending repayment (manual or crypto) for approval.
     */
    public function store(Request $request, Loan $loan)
    {
        $user = $request->user();

        // Ensure loan belongs to user
        if ($loan->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:manual,crypto',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'tx_hash' => 'required_if:payment_method,crypto|string|max:255',
            'asset' => 'required_if:payment_method,crypto|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $amountInCents = (int) round($validated['amount'] * 100);

        if ($amountInCents > $loan->outstanding_balance) {
            return back()->withErrors(['amount' => 'Amount exceeds outstanding balance.']);
        }

        $payment = LoanPayment::create([
            'loan_id' => $loan->id,
            'user_id' => $user->id,
            'bank_account_id' => $validated['bank_account_id'] ?? $loan->bank_account_id,
            'amount' => $amountInCents,
            'payment_date' => now(),
            'currency' => $loan->bankAccount->currency ?? 'USD',
            'payment_method' => $validated['payment_method'],
            'payment_type' => $validated['payment_method'],
            'asset' => $validated['asset'] ?? null,
            'tx_hash' => $validated['tx_hash'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'metadata' => [
                'submitted_by' => $user->id,
            ],
        ]);

        // Log loan repayment
        ActivityLogger::logLoan('loan_repayment', $loan, $user, [
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $payment->reference_number,
        ]);

        return back()->with('success', 'Repayment submitted for review. Reference: '.$payment->reference_number);
    }
}
