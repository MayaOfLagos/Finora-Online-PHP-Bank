<?php

namespace App\Http\Controllers;

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

        $bankAccounts = $user->bankAccounts()->active()->get();

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
            'amount' => 'required|numeric|min:1',
        ]);

        $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);

        // Mock conversion
        $rate = 0.92; // EUR/USD
        $convertedAmount = intval($validated['amount'] * $rate * 100);
        $fee = intval($convertedAmount * 0.01);

        // Update balance
        $bankAccount->balance -= intval($validated['amount'] * 100);
        $bankAccount->balance += $convertedAmount - $fee;
        $bankAccount->save();

        // Log activity
        ActivityLogger::logTransaction('exchange_money', $user, $bankAccount, [
            'from_currency' => $validated['from_currency'],
            'to_currency' => $validated['to_currency'],
            'amount' => $validated['amount'],
            'converted_amount' => $convertedAmount / 100,
            'fee' => $fee / 100,
        ]);

        return back()->with('success', 'Currency exchanged successfully!');
    }
}
