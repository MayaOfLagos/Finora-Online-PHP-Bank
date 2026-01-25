<?php

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Models\BankAccount;
use App\Models\CheckDeposit;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class CheckDepositController extends Controller
{
    /**
     * Display check deposit page
     */
    public function index()
    {
        $user = Auth::user();

        // Check if user has permission to make deposits
        if (! $user->can_deposit) {
            return back()->withErrors(['general' => 'You do not have permission to make deposits.']);
        }

        // Get user's active bank accounts
        $accounts = $user->bankAccounts()
            ->where('is_active', true)
            ->with('accountType')
            ->get();

        // Get deposit limits and hold period
        $depositLimits = [
            'daily' => (int) Setting::getValue('deposits', 'check_daily_limit', 5000) * 100,
            'perTransaction' => (int) Setting::getValue('deposits', 'check_per_transaction_limit', 2500) * 100,
            'holdDays' => (int) Setting::getValue('deposits', 'check_hold_period', 3),
        ];

        // Get today's deposits total
        $todaysTotal = CheckDeposit::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->where('status', '!=', DepositStatus::Rejected)
            ->sum('amount');

        return Inertia::render('Deposits/Check', [
            'accounts' => $accounts,
            'depositLimits' => $depositLimits,
            'todaysTotal' => $todaysTotal,
        ]);
    }

    /**
     * Submit check deposit
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'check_number' => 'required|string|max:50',
            'amount' => 'required|numeric|min:1',
            'check_front_image' => 'required|image|max:5120',
            'check_back_image' => 'required|image|max:5120',
        ]);

        $user = Auth::user();

        // Check if user has permission to make deposits
        if (! $user->can_deposit) {
            return back()->withErrors(['general' => 'You do not have permission to make deposits.']);
        }

        // Verify account ownership
        $account = BankAccount::where('id', $validated['bank_account_id'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Check deposit limits
        $dailyLimit = (int) Setting::getValue('deposits', 'check_daily_limit', 5000) * 100;
        $perTransactionLimit = (int) Setting::getValue('deposits', 'check_per_transaction_limit', 2500) * 100;

        $amountInCents = (int) ($validated['amount'] * 100);

        if ($amountInCents > $perTransactionLimit) {
            return back()->withErrors(['amount' => 'Amount exceeds per-transaction limit']);
        }

        $todaysTotal = CheckDeposit::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->where('status', '!=', DepositStatus::Rejected)
            ->sum('amount');

        if ($todaysTotal + $amountInCents > $dailyLimit) {
            return back()->withErrors(['amount' => 'Daily deposit limit exceeded']);
        }

        try {
            // Store check images
            $frontPath = $request->file('check_front_image')->store('deposits/checks', 'public');
            $backPath = $request->file('check_back_image')->store('deposits/checks', 'public');

            // Get hold period from settings (default 5 days)
            $holdDays = (int) Setting::getValue('deposits', 'check_hold_days', 5);
            $holdUntil = now()->addDays($holdDays);

            // Create check deposit record
            $deposit = CheckDeposit::create([
                'user_id' => $user->id,
                'bank_account_id' => $account->id,
                'check_number' => $validated['check_number'],
                'amount' => $amountInCents,
                'currency' => $account->currency,
                'check_front_image' => $frontPath,
                'check_back_image' => $backPath,
                'status' => DepositStatus::Pending,
                'hold_until' => $holdUntil,
            ]);

            // Send confirmation email (non-blocking on failure)
            try {
                Mail::to($user->email)->send(
                    new \App\Mail\DepositNotificationMail($deposit, $user, 'check')
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to send check deposit email', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'deposit_id' => $deposit->id,
                ]);
            }

            return back()->with([
                'success' => 'Check deposit submitted for approval',
                'deposit' => [
                    'uuid' => $deposit->uuid,
                    'reference' => $deposit->reference_number,
                    'amount' => $validated['amount'],
                    'holdUntil' => $holdUntil->format('M d, Y'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Check deposit submission failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->withErrors(['general' => 'Failed to submit check deposit']);
        }
    }
}
