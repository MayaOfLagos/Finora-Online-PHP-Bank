<?php

namespace App\Http\Controllers;

use App\Models\ExchangeMoney;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExchangeMoneyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display exchange money page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $exchangeRates = [
            'USD' => ['EUR' => 0.92, 'GBP' => 0.79, 'NGN' => 1650.00],
            'EUR' => ['USD' => 1.09, 'GBP' => 0.86, 'NGN' => 1800.00],
            'GBP' => ['USD' => 1.27, 'EUR' => 1.16, 'NGN' => 2100.00],
            'NGN' => ['USD' => 0.0006, 'EUR' => 0.0006, 'GBP' => 0.0005],
        ];

        $bankAccounts = $user->bankAccounts()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->latest()
            ->get();

        return Inertia::render('ExchangeMoney/Index', [
            'bankAccounts' => $bankAccounts,
            'exchangeRates' => $exchangeRates,
        ]);
    }

    /**
     * Get exchange rate
     */
    public function getRate(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => 'required|string|max:3',
            'to_currency' => 'required|string|max:3',
            'amount' => 'required|numeric|min:1',
        ]);

        // Mock exchange rates
        $rates = [
            'USD' => ['EUR' => 0.92, 'GBP' => 0.79, 'NGN' => 1650.00],
            'EUR' => ['USD' => 1.09, 'GBP' => 0.86, 'NGN' => 1800.00],
            'GBP' => ['USD' => 1.27, 'EUR' => 1.16, 'NGN' => 2100.00],
            'NGN' => ['USD' => 0.0006, 'EUR' => 0.0006, 'GBP' => 0.0005],
        ];

        $from = $validated['from_currency'];
        $to = $validated['to_currency'];
        $amount = $validated['amount'];

        $rate = $rates[$from][$to] ?? 1;
        $converted = $amount * $rate;
        $fee = $converted * 0.01; // 1% fee

        return response()->json([
            'rate' => $rate,
            'amount' => $amount,
            'converted_amount' => $converted,
            'fee' => $fee,
            'total' => $converted - $fee,
        ]);
    }

    /**
     * Execute exchange
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'from_currency' => 'required|string|max:3',
            'to_currency' => 'required|string|max:3',
            'amount' => 'required|integer|min:1',
        ]);

        $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);

        // Get exchange rates
        $rates = [
            'USD' => ['EUR' => 0.92, 'GBP' => 0.79, 'NGN' => 1650.00],
            'EUR' => ['USD' => 1.09, 'GBP' => 0.86, 'NGN' => 1800.00],
            'GBP' => ['USD' => 1.27, 'EUR' => 1.16, 'NGN' => 2100.00],
            'NGN' => ['USD' => 0.0006, 'EUR' => 0.0006, 'GBP' => 0.0005],
        ];

        $from = $validated['from_currency'];
        $to = $validated['to_currency'];
        $rate = $rates[$from][$to] ?? 1;

        // Calculate amounts (already in cents from frontend)
        $fromAmountInCents = $validated['amount'];
        $convertedAmount = intval($fromAmountInCents * $rate);
        $fee = intval($convertedAmount * 0.01); // 1% fee
        $toAmountInCents = $convertedAmount - $fee;

        // Create exchange record
        $exchange = ExchangeMoney::create([
            'user_id' => $user->id,
            'bank_account_id' => $bankAccount->id,
            'reference_number' => ExchangeMoney::generateReferenceNumber(),
            'from_currency' => $from,
            'to_currency' => $to,
            'from_amount' => $fromAmountInCents,
            'to_amount' => $toAmountInCents,
            'exchange_rate' => $rate,
            'fee' => $fee,
            'status' => 'completed',
            'completed_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        // Update balance (deduct from_amount, add to_amount)
        $bankAccount->balance -= $fromAmountInCents;
        $bankAccount->balance += $toAmountInCents;
        $bankAccount->save();

        // Log activity
        ActivityLogger::logTransaction('exchange_money', $exchange, $user, [
            'from_currency' => $from,
            'to_currency' => $to,
            'amount' => $fromAmountInCents / 100,
            'converted_amount' => $toAmountInCents / 100,
            'fee' => $fee / 100,
            'rate' => $rate,
        ]);

        return back()->with('success', 'Currency exchanged successfully!');
    }
}
