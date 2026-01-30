<?php

namespace App\Http\Controllers;

use App\Mail\VoucherRedeemedMail;
use App\Models\Voucher;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class VoucherController extends Controller
{
    /**
     * Display vouchers page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $vouchers = $user->vouchers()
            ->where(function ($query) {
                $query->where('is_used', false)
                    ->orWhere('is_used', true);
            })
            ->latest()
            ->paginate(20);

        $bankAccounts = $user->bankAccounts()
            ->with('accountType:id,name')
            ->get()
            ->map(fn ($account) => [
                'id' => $account->id,
                'account_number' => $account->account_number,
                'account_name' => $account->accountType?->name ?? 'Account',
                'currency' => $account->currency,
            ]);

        $stats = [
            'active' => $user->vouchers()->where('is_used', false)->where('status', 'active')->count(),
            'used' => $user->vouchers()->where('is_used', true)->count(),
            'expired' => $user->vouchers()->where('status', 'expired')->count(),
            'total_value' => $user->vouchers()->where('is_used', true)->sum('amount') / 100,
        ];

        return Inertia::render('Voucher/Index', [
            'vouchers' => $vouchers,
            'stats' => $stats,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Redeem a voucher
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'voucher_code' => 'required|string',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'pin' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // Verify PIN
        if (! $user->transaction_pin || ! Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN. Please try again.']);
        }

        // Find voucher - for multi-use, check times_used < usage_limit
        $voucher = Voucher::where('code', strtoupper($validated['voucher_code']))
            ->where('status', 'active')
            ->where(function ($query) {
                $query->where('is_used', false)
                    ->orWhereColumn('times_used', '<', 'usage_limit');
            })
            ->first();

        if (! $voucher) {
            return back()->withErrors(['voucher_code' => 'Invalid or already used voucher code.']);
        }

        // Check expiry
        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return back()->withErrors(['voucher_code' => 'This voucher has expired.']);
        }

        // Check if usage limit reached
        if ($voucher->times_used >= $voucher->usage_limit) {
            return back()->withErrors(['voucher_code' => 'This voucher has reached its usage limit.']);
        }

        $bankAccount = $user->bankAccounts()->with('accountType:id,name')->findOrFail($validated['bank_account_id']);

        // Add voucher amount to account
        $bankAccount->increment('balance', $voucher->amount);

        // Update voucher usage
        $newTimesUsed = $voucher->times_used + 1;
        $isFullyUsed = $newTimesUsed >= $voucher->usage_limit;

        $voucher->update([
            'times_used' => $newTimesUsed,
            'is_used' => $isFullyUsed,
            'status' => $isFullyUsed ? 'used' : 'active',
            'used_at' => $isFullyUsed ? now() : $voucher->used_at,
            'user_id' => $isFullyUsed ? $user->id : $voucher->user_id,
        ]);

        // Log activity
        ActivityLogger::logTransaction('voucher_redeemed', $voucher, $user, [
            'code' => $validated['voucher_code'],
            'amount' => $voucher->amount / 100,
            'times_used' => $newTimesUsed,
            'usage_limit' => $voucher->usage_limit,
        ]);

        // Send email notification
        try {
            Mail::to($user->email)->send(new VoucherRedeemedMail($user, $voucher, $bankAccount));
        } catch (\Exception $e) {
            // Log the error but don't fail the redemption
            Log::error('Failed to send voucher redemption email: '.$e->getMessage());
        }

        // Notify admins about voucher redemption
        AdminNotificationService::voucherRedeemed($voucher, $user);

        $amountFormatted = number_format($voucher->amount / 100, 2);

        return back()->with('success', "Voucher redeemed! \${$amountFormatted} has been added to your account.");
    }
}
