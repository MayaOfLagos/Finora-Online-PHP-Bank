<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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
            ->select('id', 'account_number', 'account_name', 'currency')
            ->get();

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
        ]);

        $user = $request->user();
        $voucher = Voucher::where('code', $validated['voucher_code'])
            ->where('is_used', false)
            ->where('status', 'active')
            ->firstOrFail();

        // Check expiry
        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return back()->withErrors(['voucher_code' => 'This voucher has expired.']);
        }

        $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);

        // Add voucher amount to account
        $bankAccount->increment('balance', $voucher->amount);

        // Mark voucher as used
        $voucher->update([
            'user_id' => $user->id,
            'is_used' => true,
            'status' => 'used',
            'used_at' => now(),
            'times_used' => $voucher->times_used + 1,
        ]);

        // Log activity
        ActivityLogger::logTransaction('voucher_redeemed', $user, $voucher, [
            'code' => $validated['voucher_code'],
            'amount' => $voucher->amount / 100,
        ]);

        return back()->with('success', 'Voucher redeemed successfully!');
    }
}
