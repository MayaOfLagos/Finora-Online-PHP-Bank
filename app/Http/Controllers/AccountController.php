<?php

namespace App\Http\Controllers;

use App\Mail\AccountStatementMail;
use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index()
    {
        $user = Auth::user();

        $accounts = $user->bankAccounts()
            ->with('accountType')
            ->latest()
            ->get();

        $accountTypes = AccountType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        $currencies = config('finora.currencies', [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦'],
        ]);

        $stats = [
            'totalBalance' => $accounts->where('status', 'active')->sum('balance'),
            'activeAccounts' => $accounts->where('status', 'active')->count(),
            'primaryAccount' => $accounts->where('is_primary', true)->first(),
        ];

        // Get max accounts per user from settings
        $maxAccountsPerUser = Setting::getValue('accounts', 'max_accounts_per_user', 2);

        return Inertia::render('Accounts/Index', [
            'accounts' => $accounts,
            'accountTypes' => $accountTypes,
            'currencies' => $currencies,
            'stats' => $stats,
            'maxAccountsPerUser' => (int) $maxAccountsPerUser,
        ]);
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        // Check account limit
        $maxAccountsPerUser = Setting::getValue('accounts', 'max_accounts_per_user', 2);
        $currentAccountCount = $request->user()->bankAccounts()->count();

        if ($currentAccountCount >= $maxAccountsPerUser) {
            return back()->withErrors([
                'general' => "You have reached the maximum limit of {$maxAccountsPerUser} account(s). Contact support to request more.",
            ]);
        }

        $validated = $request->validate([
            'account_type_id' => 'required|exists:account_types,id',
            'currency' => 'required|string|size:3',
            'terms_accepted' => 'required|accepted',
            'pin' => 'required|string|size:6',
        ]);

        // Verify transaction PIN
        if (! Hash::check($validated['pin'], $request->user()->transaction_pin)) {
            return back()->withErrors([
                'pin' => 'Invalid transaction PIN',
            ]);
        }

        // Generate unique account number
        $accountNumber = $this->generateAccountNumber();

        // Create account
        $account = $request->user()->bankAccounts()->create([
            'account_type_id' => $validated['account_type_id'],
            'account_number' => $accountNumber,
            'balance' => 0,
            'currency' => $validated['currency'],
            'status' => 'active',
            'is_primary' => $request->user()->bankAccounts()->count() === 0, // First account is primary
        ]);

        return back()->with('success', 'Account created successfully');
    }

    /**
     * Display the specified account
     */
    public function show(BankAccount $account)
    {
        // Ensure user owns this account
        $this->authorize('view', $account);

        $account->load('accountType');

        // Get transactions for this account
        $transactions = $account->transactionHistories()
            ->latest()
            ->take(100)
            ->get();

        // Calculate stats
        $stats = [
            'totalIn' => $transactions->where('type', 'credit')->sum('amount'),
            'totalOut' => $transactions->where('type', 'debit')->sum('amount'),
            'transactionCount' => $transactions->count(),
        ];

        return Inertia::render('Accounts/Show', [
            'account' => $account,
            'transactions' => $transactions,
            'stats' => $stats,
        ]);
    }

    /**
     * Freeze account
     */
    public function freeze(Request $request, BankAccount $account)
    {
        $this->authorize('update', $account);

        $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        // Verify PIN
        if (! Hash::check($request->pin, $request->user()->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN']);
        }

        $account->update(['status' => 'frozen']);

        return back()->with('success', 'Account frozen successfully');
    }

    /**
     * Unfreeze account
     */
    public function unfreeze(Request $request, BankAccount $account)
    {
        $this->authorize('update', $account);

        $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        // Verify PIN
        if (! Hash::check($request->pin, $request->user()->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN']);
        }

        $account->update(['status' => 'active']);

        return back()->with('success', 'Account unfrozen successfully');
    }

    /**
     * Set account as primary
     */
    public function setPrimary(Request $request, BankAccount $account)
    {
        $this->authorize('update', $account);

        // Remove primary from other accounts
        $request->user()->bankAccounts()->update(['is_primary' => false]);

        // Set this as primary
        $account->update(['is_primary' => true]);

        return back()->with('success', 'Primary account updated successfully');
    }

    /**
     * Download account statement (send via email)
     */
    public function downloadStatement(Request $request, BankAccount $account)
    {
        $this->authorize('view', $account);

        $user = $request->user();

        // Get transactions for this account
        $transactions = $account->transactionHistories()
            ->orderBy('created_at', 'desc')
            ->get();

        // Check if there are any transactions
        if ($transactions->isEmpty()) {
            return back()->withErrors([
                'statement' => 'You have no transactions to generate a statement. Please make at least one transaction before requesting a statement.',
            ]);
        }

        try {
            // Calculate totals
            $totalCredits = $transactions->where('type', 'credit')->sum('amount');
            $totalDebits = $transactions->where('type', 'debit')->sum('amount');

            // Currency symbols
            $currencySymbols = [
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                'NGN' => '₦',
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.account-statement', [
                'user' => $user,
                'account' => $account->load('accountType'),
                'transactions' => $transactions,
                'totalCredits' => $totalCredits,
                'totalDebits' => $totalDebits,
                'currencySymbol' => $currencySymbols[$account->currency] ?? '$',
                'generatedAt' => now(),
                'dateFrom' => $transactions->last()?->created_at,
                'dateTo' => $transactions->first()?->created_at,
                'documentId' => strtoupper(Str::random(16)),
            ]);

            $pdfContent = $pdf->output();

            // Send email with PDF attachment
            Mail::to($user->email)->send(new AccountStatementMail(
                user: $user,
                account: $account,
                transactions: $transactions,
                pdfContent: $pdfContent,
                dateFrom: $transactions->last()?->created_at ? Carbon::parse($transactions->last()->created_at) : null,
                dateTo: $transactions->first()?->created_at ? Carbon::parse($transactions->first()->created_at) : null,
            ));

            return back()->with('success', 'Your account statement has been sent to your email address ('.$user->email.'). Please check your inbox.');

        } catch (\Exception $e) {
            Log::error('Failed to generate/send account statement', [
                'account_id' => $account->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'statement' => 'We encountered an issue while generating your statement. Please try again later or contact support if the problem persists.',
            ]);
        }
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber(): string
    {
        do {
            // Generate 10-digit account number
            $accountNumber = mt_rand(1000000000, 9999999999);
        } while (BankAccount::where('account_number', $accountNumber)->exists());

        return (string) $accountNumber;
    }
}
