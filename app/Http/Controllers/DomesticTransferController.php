<?php

namespace App\Http\Controllers;

use App\Enums\TransferStatus;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\DomesticTransfer;
use App\Models\Setting;
use App\Models\TransactionHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DomesticTransferController extends Controller
{
    /**
     * Display domestic transfer page
     */
    public function index()
    {
        $user = Auth::user();

        $accounts = $user->bankAccounts()
            ->where('is_active', true)
            ->with('accountType')
            ->get();

        $banks = Bank::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'swift_code']);

        $transferLimits = [
            'daily' => (int) Setting::getValue('transfers', 'domestic_daily_limit', 50000) * 100,
            'perTransaction' => (int) Setting::getValue('transfers', 'domestic_per_transaction_limit', 25000) * 100,
        ];

        $fees = [
            'flat' => (float) Setting::getValue('transfers', 'domestic_flat_fee', 5),
            'percentage' => (float) Setting::getValue('transfers', 'domestic_percentage_fee', 0),
        ];

        // Get verification requirements for user
        $verificationConfig = $this->getVerificationConfig($user);

        return Inertia::render('Transfers/Domestic', [
            'accounts' => $accounts,
            'banks' => $banks,
            'transferLimits' => $transferLimits,
            'fees' => $fees,
            'verificationConfig' => $verificationConfig,
        ]);
    }

    /**
     * Get verification configuration for a user
     */
    private function getVerificationConfig(User $user): array
    {
        $globalOtpEnabled = (bool) Setting::getValue('security', 'transfer_otp_enabled', true);

        return [
            'requiresOtp' => $globalOtpEnabled && ! $user->skip_transfer_otp,
        ];
    }

    /**
     * Initiate a domestic transfer
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id',
            'bank_id' => 'required|exists:banks,id',
            'beneficiary_name' => 'required|string|max:255',
            'beneficiary_account' => 'required|string|max:50',
            'amount' => 'required|integer|min:1', // Already in cents from frontend
            'description' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Verify account ownership
        $account = BankAccount::where('id', $validated['from_account_id'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Check user permission
        if (! $user->can_send_domestic_transfer) {
            return back()->withErrors(['general' => 'You do not have permission to send domestic transfers.']);
        }

        $amountInCents = (int) $validated['amount']; // Already in cents

        // Calculate fees
        $flatFee = (float) Setting::getValue('transfers', 'domestic_flat_fee', 5) * 100;
        $percentageFee = (float) Setting::getValue('transfers', 'domestic_percentage_fee', 0);
        $calculatedFee = (int) ($flatFee + ($amountInCents * $percentageFee / 100));
        $totalAmount = $amountInCents + $calculatedFee;

        // Check sufficient balance
        if ($account->balance < $totalAmount) {
            return back()->withErrors(['amount' => 'Insufficient balance including fees.']);
        }

        // Check transfer limits
        $perTransactionLimit = (int) Setting::getValue('transfers', 'domestic_per_transaction_limit', 25000) * 100;
        if ($amountInCents > $perTransactionLimit) {
            return back()->withErrors(['amount' => 'Amount exceeds per-transaction limit.']);
        }

        // Create domestic transfer record
        $transfer = DomesticTransfer::create([
            'user_id' => $user->id,
            'bank_account_id' => $account->id,
            'bank_id' => $validated['bank_id'],
            'beneficiary_name' => $validated['beneficiary_name'],
            'beneficiary_account' => $validated['beneficiary_account'],
            'amount' => $amountInCents,
            'currency' => $account->currency,
            'fee' => $calculatedFee,
            'description' => $validated['description'],
            'status' => TransferStatus::Pending,
        ]);

        return back()->with([
            'success' => 'Transfer initiated. Please complete verification.',
            'transfer' => [
                'uuid' => $transfer->uuid,
                'reference' => $transfer->reference_number,
                'amount' => $amountInCents / 100, // Convert back to dollars for display
                'fee' => $calculatedFee / 100,
                'total' => $totalAmount / 100,
                'currentStep' => 'pin',
            ],
        ]);
    }

    /**
     * Verify PIN for domestic transfer
     */
    public function verifyPin(Request $request, DomesticTransfer $domesticTransfer)
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify ownership
        if ($domesticTransfer->user_id !== $user->id) {
            return back()->withErrors(['general' => 'Unauthorized access.']);
        }

        // Verify transfer status
        if ($domesticTransfer->status !== TransferStatus::Pending) {
            return back()->withErrors(['general' => 'Transfer is no longer pending.']);
        }

        // Verify PIN
        if (! Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN.']);
        }

        // Update transfer
        $domesticTransfer->update([
            'pin_verified_at' => now(),
        ]);

        // Determine next step
        $requiresOtp = $user->requiresTransferOtp();

        if (! $requiresOtp) {
            // Complete transfer directly
            return $this->completeTransfer($domesticTransfer);
        }

        return back()->with([
            'success' => 'PIN verified successfully.',
            'nextStep' => 'otp',
        ]);
    }

    /**
     * Request OTP for domestic transfer
     */
    public function requestOtp(Request $request, DomesticTransfer $domesticTransfer)
    {
        $user = Auth::user();

        // Verify ownership
        if ($domesticTransfer->user_id !== $user->id) {
            return back()->withErrors(['general' => 'Unauthorized access.']);
        }

        // Verify transfer status
        if ($domesticTransfer->status !== TransferStatus::Pending) {
            return back()->withErrors(['general' => 'Transfer is no longer pending.']);
        }

        // Verify PIN was verified
        if (! $domesticTransfer->pin_verified_at) {
            return back()->withErrors(['general' => 'Please verify PIN first.']);
        }

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database (more reliable than session)
        $domesticTransfer->update([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via email
        try {
            Mail::to($user->email)->send(
                new \App\Mail\TransferOtpMail($otp, $domesticTransfer, $user, 'domestic')
            );
        } catch (\Exception $e) {
            Log::error('Failed to send domestic transfer OTP email: '.$e->getMessage());
            // Continue anyway - OTP is still valid in database
        }

        // For development, flash the OTP
        if (config('app.debug')) {
            session()->flash('dev_otp', $otp);
        }

        return back()->with([
            'success' => 'OTP sent to your email.',
        ]);
    }

    /**
     * Verify OTP and complete domestic transfer
     */
    public function verifyOtp(Request $request, DomesticTransfer $domesticTransfer)
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify ownership
        if ($domesticTransfer->user_id !== $user->id) {
            return back()->withErrors(['general' => 'Unauthorized access.']);
        }

        // Verify transfer status
        if ($domesticTransfer->status !== TransferStatus::Pending) {
            return back()->withErrors(['general' => 'Transfer is no longer pending.']);
        }

        // Verify PIN was verified
        if (! $domesticTransfer->pin_verified_at) {
            return back()->withErrors(['general' => 'Please verify PIN first.']);
        }

        // Verify OTP from database
        $storedOtp = $domesticTransfer->otp_code;
        $otpExpires = $domesticTransfer->otp_expires_at;

        if (! $storedOtp) {
            return back()->withErrors(['otp' => 'OTP not found. Please request a new one.']);
        }

        if (! $otpExpires) {
            return back()->withErrors(['otp' => 'OTP expiry not set. Please request a new one.']);
        }

        if (now()->isAfter($otpExpires)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if (! Hash::check($validated['otp'], $storedOtp)) {
            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        // Update OTP verified timestamp and clear OTP
        $domesticTransfer->update([
            'otp_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Complete the transfer
        return $this->completeTransfer($domesticTransfer);
    }

    /**
     * Complete the domestic transfer
     */
    private function completeTransfer(DomesticTransfer $domesticTransfer)
    {
        $user = Auth::user();

        // Get the source account
        $account = BankAccount::where('id', $domesticTransfer->bank_account_id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        $totalAmount = $domesticTransfer->amount + $domesticTransfer->fee;

        // Verify sufficient balance
        if ($account->balance < $totalAmount) {
            $domesticTransfer->update([
                'status' => TransferStatus::Failed,
            ]);

            return back()->withErrors(['general' => 'Insufficient balance.']);
        }

        DB::beginTransaction();

        try {
            // Debit the account
            $account->balance -= $totalAmount;
            $account->save();

            // Update transfer status
            $domesticTransfer->update([
                'status' => TransferStatus::Processing, // Domestic transfers need processing time (1-3 days)
                'completed_at' => now(),
            ]);

            // Create transaction history
            TransactionHistory::create([
                'user_id' => $user->id,
                'bank_account_id' => $account->id,
                'transaction_type' => 'domestic_transfer',
                'transactionable_type' => DomesticTransfer::class,
                'transactionable_id' => $domesticTransfer->id,
                'amount' => $domesticTransfer->amount / 100,
                'type' => 'debit',
                'balance_after' => $account->balance / 100,
                'currency' => $domesticTransfer->currency,
                'status' => 'processing',
                'description' => 'Domestic transfer to '.$domesticTransfer->beneficiary_name.' (Ref: '.$domesticTransfer->reference_number.')',
                'processed_at' => now(),
            ]);

            // Clear OTP session
            session()->forget([
                'domestic_transfer_otp_'.$domesticTransfer->uuid,
                'domestic_transfer_otp_expires_'.$domesticTransfer->uuid,
            ]);

            DB::commit();

            return back()->with([
                'success' => 'Domestic transfer submitted successfully. It will be processed within 1-3 business days.',
                'transfer' => [
                    'uuid' => $domesticTransfer->uuid,
                    'reference' => $domesticTransfer->reference_number,
                    'amount' => $domesticTransfer->amount / 100,
                    'fee' => $domesticTransfer->fee / 100,
                    'total' => $totalAmount / 100,
                    'status' => 'processing',
                    'beneficiary' => $domesticTransfer->beneficiary_name,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $domesticTransfer->update([
                'status' => TransferStatus::Failed,
            ]);

            return back()->withErrors(['general' => 'Transfer failed. Please try again.']);
        }
    }
}
