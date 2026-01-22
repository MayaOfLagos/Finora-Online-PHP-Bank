<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display withdrawals page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $withdrawals = $user->withdrawals()
            ->with('bankAccount')
            ->latest()
            ->paginate(20);

        $bankAccounts = $user->bankAccounts()->active()->get();

        return Inertia::render('Withdrawal/Index', [
            'withdrawals' => $withdrawals,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Store a new withdrawal
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check permission
        if (! $user->can_withdraw) {
            return back()->withErrors(['error' => 'You do not have permission to withdraw.']);
        }

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|integer|min:1',
            'currency' => 'required|string|max:3',
            'reason' => 'nullable|string|max:500',
        ]);

        $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);

        // Check balance
        if ($bankAccount->balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient balance.']);
        }

        $withdrawal = Withdrawal::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'bank_account_id' => $bankAccount->id,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'reason' => $validated['reason'],
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        // Deduct from balance
        $bankAccount->decrement('balance', $validated['amount']);

        // Log activity
        ActivityLogger::logTransaction('withdrawal_created', $user, $withdrawal, [
            'amount' => $validated['amount'],
            'bank_account_id' => $bankAccount->id,
        ]);

        return back()->with('success', 'Withdrawal request submitted. Awaiting admin approval.');
    }

    /**
     * Approve withdrawal (admin only)
     */
    public function approve(Request $request, string $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        $withdrawal->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        ActivityLogger::logAdmin('withdrawal_approved', null, metadata: [
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $withdrawal->user_id,
        ]);

        return back()->with('success', 'Withdrawal approved.');
    }

    /**
     * Reject withdrawal (admin only)
     */
    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        // Refund the amount
        $withdrawal->bankAccount->increment('balance', $withdrawal->amount);

        $withdrawal->update([
            'status' => 'failed',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        ActivityLogger::logAdmin('withdrawal_rejected', null, metadata: [
            'withdrawal_id' => $withdrawal->id,
            'reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Withdrawal rejected and amount refunded.');
    }

    /**
     * Complete withdrawal (admin only)
     */
    public function complete(Request $request, string $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        $withdrawal->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        ActivityLogger::logTransaction('withdrawal_completed', $withdrawal->user, $withdrawal);

        return back()->with('success', 'Withdrawal completed.');
    }
}
