<?php

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Models\BankAccount;
use App\Models\Cryptocurrency;
use App\Models\CryptoDeposit;
use App\Models\CryptoWallet;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use App\Services\CryptoExchangeRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class CryptoDepositController extends Controller
{
    /**
     * Display crypto deposit page
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

        // Get active cryptocurrencies with their wallets
        $cryptocurrencies = Cryptocurrency::where('is_active', true)
            ->with(['wallets' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        // Get exchange rates
        $exchangeRateService = app(CryptoExchangeRateService::class);

        $cryptocurrenciesData = $cryptocurrencies->map(function ($crypto) use ($exchangeRateService) {
            $exchangeRate = $exchangeRateService->getExchangeRate($crypto);

            return [
                'id' => $crypto->id,
                'name' => $crypto->name,
                'symbol' => $crypto->symbol,
                'network' => $crypto->network,
                'icon' => $crypto->icon,
                'exchange_rate' => $exchangeRate,
                'wallets' => $crypto->wallets->map(function ($wallet) {
                    return [
                        'id' => $wallet->id,
                        'address' => $wallet->wallet_address,
                        'label' => $wallet->label,
                    ];
                }),
            ];
        });

        // Get deposit limits
        $depositLimits = [
            'daily' => (int) Setting::getValue('deposits', 'crypto_daily_limit', 10000) * 100,
            'perTransaction' => (int) Setting::getValue('deposits', 'crypto_per_transaction_limit', 5000) * 100,
        ];

        // Get today's deposits total
        $todaysTotal = CryptoDeposit::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->where('status', DepositStatus::Completed)
            ->sum('usd_amount');

        return Inertia::render('Deposits/Crypto', [
            'accounts' => $accounts,
            'cryptocurrencies' => $cryptocurrenciesData,
            'depositLimits' => $depositLimits,
            'todaysTotal' => $todaysTotal,
        ]);
    }

    /**
     * Get wallet details for a cryptocurrency
     */
    public function getWallet(Cryptocurrency $cryptocurrency)
    {
        $wallets = $cryptocurrency->wallets()
            ->where('is_active', true)
            ->get();

        // Get current exchange rate
        $exchangeRateService = app(CryptoExchangeRateService::class);
        $exchangeRate = $exchangeRateService->getExchangeRate($cryptocurrency);

        return response()->json([
            'success' => true,
            'cryptocurrency' => [
                'id' => $cryptocurrency->id,
                'name' => $cryptocurrency->name,
                'symbol' => $cryptocurrency->symbol,
                'network' => $cryptocurrency->network,
                'exchange_rate' => $exchangeRate,
            ],
            'wallets' => $wallets->map(function ($wallet) {
                return [
                    'id' => $wallet->id,
                    'wallet_address' => $wallet->wallet_address,
                    'label' => $wallet->label,
                ];
            }),
        ]);
    }

    /**
     * Get exchange rate for a cryptocurrency
     */
    public function getExchangeRate(Cryptocurrency $cryptocurrency)
    {
        $exchangeRateService = app(CryptoExchangeRateService::class);
        $exchangeRate = $exchangeRateService->getExchangeRate($cryptocurrency);

        if (! $exchangeRate) {
            return response()->json([
                'success' => false,
                'message' => 'Exchange rate not available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'exchange_rate' => $exchangeRate,
            'symbol' => $cryptocurrency->symbol,
            'name' => $cryptocurrency->name,
        ]);
    }

    /**
     * Register crypto deposit (manual entry for user)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'cryptocurrency_id' => 'required|exists:cryptocurrencies,id',
            'crypto_wallet_id' => 'required|exists:crypto_wallets,id',
            'transaction_hash' => 'required|string|max:255',
            'crypto_amount' => 'required|numeric|min:0.00000001',
            'usd_amount' => 'required|numeric|min:1',
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

        // Verify cryptocurrency exists and is active
        $cryptocurrency = Cryptocurrency::where('id', $validated['cryptocurrency_id'])
            ->where('is_active', true)
            ->firstOrFail();

        // Verify wallet exists and belongs to the cryptocurrency
        $wallet = CryptoWallet::where('id', $validated['crypto_wallet_id'])
            ->where('cryptocurrency_id', $validated['cryptocurrency_id'])
            ->where('is_active', true)
            ->firstOrFail();

        // Check deposit limits
        $dailyLimit = (int) Setting::getValue('deposits', 'crypto_daily_limit', 10000) * 100;
        $perTransactionLimit = (int) Setting::getValue('deposits', 'crypto_per_transaction_limit', 5000) * 100;

        $usdAmountInCents = (int) ($validated['usd_amount'] * 100);

        if ($usdAmountInCents > $perTransactionLimit) {
            return back()->withErrors(['usd_amount' => 'Amount exceeds per-transaction limit']);
        }

        $todaysTotal = CryptoDeposit::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->where('status', DepositStatus::Completed)
            ->sum('usd_amount');

        if ($todaysTotal + $usdAmountInCents > $dailyLimit) {
            return back()->withErrors(['usd_amount' => 'Daily deposit limit exceeded']);
        }

        try {
            // Create crypto deposit record with Pending status (requires admin verification)
            $deposit = CryptoDeposit::create([
                'user_id' => $user->id,
                'bank_account_id' => $account->id,
                'cryptocurrency_id' => $cryptocurrency->id,
                'crypto_wallet_id' => $wallet->id,
                'transaction_hash' => $validated['transaction_hash'],
                'crypto_amount' => $validated['crypto_amount'],
                'usd_amount' => $usdAmountInCents,
                'status' => DepositStatus::Pending,
            ]);

            // Send confirmation email (non-blocking on failure)
            try {
                Mail::to($user->email)->send(
                    new \App\Mail\DepositNotificationMail($deposit, $user, 'crypto')
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to send crypto deposit email', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'deposit_id' => $deposit->id,
                ]);
            }

            // Notify admins about new crypto deposit
            AdminNotificationService::cryptoDepositRegistered($deposit, $user);

            // Log crypto deposit registration
            ActivityLogger::logTransaction('crypto_deposit_created', $deposit, $user, [
                'cryptocurrency' => $cryptocurrency->symbol,
                'crypto_amount' => $validated['crypto_amount'],
                'usd_amount' => $validated['usd_amount'],
                'transaction_hash' => $validated['transaction_hash'],
            ]);

            return back()->with([
                'success' => 'Crypto deposit registered and awaiting verification',
                'deposit' => [
                    'uuid' => $deposit->uuid,
                    'reference' => $deposit->reference_number,
                    'crypto_amount' => $validated['crypto_amount'],
                    'usd_amount' => $validated['usd_amount'],
                    'symbol' => $cryptocurrency->symbol,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Crypto deposit registration failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->withErrors(['general' => 'Failed to register crypto deposit']);
        }
    }
}
