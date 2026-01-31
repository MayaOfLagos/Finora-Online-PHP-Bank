<?php

namespace App\Http\Controllers;

use App\Mail\TransferCompletedMail;
use App\Models\BankAccount;
use App\Models\TransactionHistory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AccountTransferController extends Controller
{
    /**
     * Display account transfer page
     */
    public function index()
    {
        $user = Auth::user();

        $accounts = $user->bankAccounts()
            ->where('is_active', true)
            ->with('accountType')
            ->get();

        return Inertia::render('Transfers/AccountTransfer', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Process account-to-account transfer
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id',
            'to_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|integer|min:1', // Already in cents from frontend
            'description' => 'nullable|string|max:255',
            'pin' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify PIN
        if (! Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN']);
        }

        // Get from account
        $fromAccount = BankAccount::where('id', $validated['from_account_id'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Get to account
        $toAccount = BankAccount::where('id', $validated['to_account_id'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Cannot transfer to same account
        if ($fromAccount->id === $toAccount->id) {
            return back()->withErrors(['general' => 'Cannot transfer to the same account']);
        }

        $amountInCents = (int) $validated['amount']; // Already in cents

        // Check sufficient balance
        if ($fromAccount->balance < $amountInCents) {
            return back()->withErrors(['amount' => 'Insufficient balance']);
        }

        // Process transfer in transaction
        DB::beginTransaction();

        try {
            $reference = 'AT-'.strtoupper(Str::random(12));

            // Debit from account
            $fromAccount->balance -= $amountInCents;
            $fromAccount->save();

            // Credit to account
            $toAccount->balance += $amountInCents;
            $toAccount->save();

            // Create debit transaction history
            TransactionHistory::create([
                'user_id' => $user->id,
                'bank_account_id' => $fromAccount->id,
                'transaction_type' => 'account_transfer',
                'amount' => $amountInCents, // Store in cents
                'type' => 'debit',
                'balance_after' => $fromAccount->balance / 100,
                'currency' => $fromAccount->currency,
                'status' => 'completed',
                'description' => 'Transfer to '.$toAccount->account_type?->name.' account (Ref: '.$reference.')',
                'processed_at' => now(),
            ]);

            // Create credit transaction history
            TransactionHistory::create([
                'user_id' => $user->id,
                'bank_account_id' => $toAccount->id,
                'transaction_type' => 'account_transfer',
                'amount' => $amountInCents, // Store in cents
                'type' => 'credit',
                'balance_after' => $toAccount->balance / 100,
                'currency' => $toAccount->currency,
                'status' => 'completed',
                'description' => 'Transfer from '.$fromAccount->account_type?->name.' account (Ref: '.$reference.')',
                'processed_at' => now(),
            ]);

            DB::commit();

            // Send account transfer completed email
            try {
                // Create a simple object with transfer details for the email
                $transferData = (object) [
                    'amount' => $amountInCents,
                    'currency' => $fromAccount->currency,
                    'reference_number' => $reference,
                    'description' => $validated['description'] ?? null,
                ];

                Mail::to($user->email)->send(
                    new TransferCompletedMail(
                        $transferData,
                        $user,
                        'account',
                        $toAccount->account_type?->name.' Account'
                    )
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send account transfer completed email: '.$e->getMessage());
            }

            // Log account transfer
            ActivityLogger::logTransaction('account_transfer_created', null, $user, [
                'amount' => $amountInCents / 100,
                'reference' => $reference,
                'from_account' => $fromAccount->account_number,
                'to_account' => $toAccount->account_number,
            ]);

            return back()->with([
                'success' => 'Transfer completed successfully',
                'transfer' => [
                    'reference' => $reference,
                    'amount' => $amountInCents / 100, // Convert back to dollars for display
                    'from_account' => $fromAccount->account_type?->name,
                    'to_account' => $toAccount->account_type?->name,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Account transfer processing failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'from_account_id' => $validated['from_account_id'] ?? null,
                'to_account_id' => $validated['to_account_id'] ?? null,
            ]);

            return back()->withErrors(['general' => 'Transfer failed. Please try again.']);
        }
    }
}
