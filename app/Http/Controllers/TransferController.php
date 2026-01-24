<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\InternalTransfer;
use App\Models\Setting;
use App\Models\TransactionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TransferController extends Controller
{
    /**
     * Display transfers index page
     */
    public function index()
    {
        $user = Auth::user();

        $accounts = $user->bankAccounts()
            ->where('is_active', true)
            ->with('accountType')
            ->get();

        $recentTransfers = TransactionHistory::where('user_id', $user->id)
            ->whereIn('transaction_type', ['internal_transfer', 'wire_transfer', 'domestic_transfer', 'account_transfer'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Transfers/Index', [
            'accounts' => $accounts,
            'recentTransfers' => $recentTransfers,
        ]);
    }

    /**
     * Display internal transfer page
     */
    public function internal(Request $request)
    {
        $user = Auth::user();

        $accounts = $user->bankAccounts()
            ->where('is_active', true)
            ->with('accountType')
            ->get();

        $beneficiaries = $user->beneficiaries()
            ->where('is_active', true)
            ->get();

        $transferLimits = [
            'daily' => (int) Setting::getValue('transfers', 'internal_daily_limit', 100000) * 100,
            'perTransaction' => (int) Setting::getValue('transfers', 'internal_per_transaction_limit', 50000) * 100,
        ];

        // Get verification configuration for user
        $globalOtpEnabled = (bool) Setting::getValue('security', 'transfer_otp_enabled', true);
        $verificationConfig = [
            'requiresOtp' => $globalOtpEnabled && ! $user->skip_transfer_otp,
        ];

        return Inertia::render('Transfers/Internal', [
            'accounts' => $accounts,
            'beneficiaries' => $beneficiaries,
            'transferLimits' => $transferLimits,
            'preselectedAccountId' => $request->get('from_account_id'),
            'verificationConfig' => $verificationConfig,
        ]);
    }

    /**
     * Request OTP for internal transfer
     */
    public function requestInternalOtp(Request $request)
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:6',
            'from_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();

        // Verify PIN
        if (! Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN']);
        }

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in session (expires in 10 minutes)
        session([
            'transfer_otp' => Hash::make($otp),
            'transfer_otp_expires' => now()->addMinutes(10),
        ]);

        // Send OTP via email
        // Mail::to($user->email)->send(new TransferOtpMail($otp, $validated['amount']));

        // For now, flash the OTP for development (remove in production)
        session()->flash('dev_otp', $otp);

        return back()->with('success', 'OTP sent to your email');
    }

    /**
     * Process internal transfer
     */
    public function processInternal(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id',
            'to_account_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'pin' => 'required|string|size:6',
            'otp' => 'required|string',
        ]);

        $user = Auth::user();

        // Verify PIN
        if (! Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN']);
        }

        // Check if OTP verification is required
        $globalOtpEnabled = (bool) Setting::getValue('security', 'transfer_otp_enabled', true);
        $requiresOtp = $globalOtpEnabled && ! $user->skip_transfer_otp;

        // Only verify OTP if required and not skipped
        if ($requiresOtp && $validated['otp'] !== 'skip_otp') {
            // Verify OTP
            $storedOtp = session('transfer_otp');
            $otpExpires = session('transfer_otp_expires');

            if (! $storedOtp || ! $otpExpires || now()->isAfter($otpExpires)) {
                return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
            }

            if (! Hash::check($validated['otp'], $storedOtp)) {
                return back()->withErrors(['otp' => 'Invalid OTP']);
            }
        }

        // Get accounts
        $fromAccount = BankAccount::where('id', $validated['from_account_id'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        $toAccount = BankAccount::where('account_number', $validated['to_account_number'])
            ->where('is_active', true)
            ->first();

        if (! $toAccount) {
            return back()->withErrors(['to_account_number' => 'Recipient account not found']);
        }

        if ($fromAccount->id === $toAccount->id) {
            return back()->withErrors(['to_account_number' => 'Cannot transfer to the same account']);
        }

        $amountInCents = (int) ($validated['amount'] * 100);

        // Check sufficient balance
        if ($fromAccount->balance < $amountInCents) {
            return back()->withErrors(['amount' => 'Insufficient balance']);
        }

        // Process transfer in transaction
        DB::beginTransaction();

        try {
            $reference = 'INT-'.strtoupper(Str::random(12));

            // Debit sender
            $fromAccount->balance -= $amountInCents;
            $fromAccount->save();

            // Credit receiver
            $toAccount->balance += $amountInCents;
            $toAccount->save();

            // Create transfer record
            $transfer = InternalTransfer::create([
                'sender_id' => $user->id,
                'sender_account_id' => $fromAccount->id,
                'receiver_id' => $toAccount->user_id,
                'receiver_account_id' => $toAccount->id,
                'amount' => $amountInCents,
                'currency' => $fromAccount->currency,
                'reference_number' => $reference,
                'description' => $validated['description'],
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Record transaction history for sender
            TransactionHistory::create([
                'user_id' => $user->id,
                'bank_account_id' => $fromAccount->id,
                'transaction_type' => 'internal_transfer',
                'reference_number' => $reference,
                'transactionable_type' => InternalTransfer::class,
                'transactionable_id' => $transfer->id,
                'amount' => $validated['amount'],
                'type' => 'debit',
                'balance_after' => $fromAccount->balance / 100,
                'currency' => $fromAccount->currency,
                'status' => 'completed',
                'description' => 'Internal transfer to '.$toAccount->user->name,
                'processed_at' => now(),
            ]);

            // Record transaction history for receiver
            TransactionHistory::create([
                'user_id' => $toAccount->user_id,
                'bank_account_id' => $toAccount->id,
                'transaction_type' => 'internal_transfer',
                'reference_number' => $reference.'-R',
                'transactionable_type' => InternalTransfer::class,
                'transactionable_id' => $transfer->id,
                'amount' => $validated['amount'],
                'type' => 'credit',
                'balance_after' => $toAccount->balance / 100,
                'currency' => $toAccount->currency,
                'status' => 'completed',
                'description' => 'Internal transfer from '.$user->name,
                'processed_at' => now(),
            ]);

            // Clear OTP session
            session()->forget(['transfer_otp', 'transfer_otp_expires']);

            DB::commit();

            return back()->with([
                'success' => 'Transfer completed successfully',
                'transfer' => [
                    'reference' => $reference,
                    'amount' => $validated['amount'],
                    'recipient' => $toAccount->user->name,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['general' => 'Transfer failed. Please try again.']);
        }
    }

    /**
     * Verify account for transfer
     */
    public function verifyAccount(string $accountNumber)
    {
        $account = BankAccount::where('account_number', $accountNumber)
            ->where('is_active', true)
            ->with('user', 'accountType')
            ->first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found or inactive',
            ]);
        }

        // Don't allow transfer to own account (will be validated later)
        return response()->json([
            'success' => true,
            'recipient' => [
                'name' => $account->user->first_name.' '.substr($account->user->last_name, 0, 1).'.',
                'account_type' => $account->accountType->name ?? 'Account',
            ],
        ]);
    }
}
