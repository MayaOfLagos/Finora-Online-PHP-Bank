<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanController extends Controller
{
    /**
     * Display loan programs
     */
    public function programs()
    {
        $programs = LoanType::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($program) => [
                'id' => $program->id,
                'name' => $program->name,
                'code' => $program->code,
                'description' => $program->description,
                'min_amount' => $program->min_amount / 100,
                'max_amount' => $program->max_amount / 100,
                'min_term_months' => $program->min_term_months,
                'max_term_months' => $program->max_term_months,
                'interest_rate' => $program->interest_rate,
            ]);

        return Inertia::render('Loans/Programs', [
            'programs' => $programs,
        ]);
    }

    /**
     * Display user's loan applications
     */
    public function applications(Request $request)
    {
        $applications = $request->user()
            ->loanApplications()
            ->with(['loanType', 'loan'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn ($app) => [
                'id' => $app->id,
                'uuid' => $app->uuid,
                'reference_number' => $app->reference_number,
                'loan_type' => $app->loanType->name ?? null,
                'amount' => $app->amount / 100,
                'term_months' => $app->term_months,
                'interest_rate' => $app->interest_rate,
                'monthly_payment' => $app->monthly_payment / 100,
                'total_payable' => $app->total_payable / 100,
                'purpose' => $app->purpose,
                'status' => $app->status->value,
                'status_label' => $app->status->label(),
                'status_color' => $app->status->color(),
                'rejection_reason' => $app->rejection_reason,
                'approved_at' => $app->approved_at?->format('M d, Y'),
                'created_at' => $app->created_at->format('M d, Y'),
                'loan_id' => $app->loan?->uuid,
            ]);

        return Inertia::render('Loans/Applications', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show loan application form
     */
    public function create(Request $request, $programId)
    {
        $program = LoanType::where('is_active', true)
            ->findOrFail($programId);

        $accounts = $request->user()
            ->bankAccounts()
            ->with('accountType')
            ->get()
            ->map(fn ($account) => [
                'id' => $account->id,
                'account_number' => $account->account_number,
                'account_name' => $account->accountType->name ?? 'Account',
                'balance' => $account->balance / 100,
                'currency' => $account->currency,
            ]);

        return Inertia::render('Loans/Apply', [
            'program' => [
                'id' => $program->id,
                'name' => $program->name,
                'description' => $program->description,
                'min_amount' => $program->min_amount / 100,
                'max_amount' => $program->max_amount / 100,
                'min_term_months' => $program->min_term_months,
                'max_term_months' => $program->max_term_months,
                'interest_rate' => $program->interest_rate,
            ],
            'accounts' => $accounts,
        ]);
    }

    /**
     * Store loan application
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'loan_type_id' => 'required|exists:loan_types,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'purpose' => 'required|string|max:1000',
        ]);

        $loanType = LoanType::findOrFail($validated['loan_type_id']);
        $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);

        $amountInCents = (int) ($validated['amount'] * 100);

        // Validate amount range
        if ($amountInCents < $loanType->min_amount || $amountInCents > $loanType->max_amount) {
            return back()->withErrors([
                'amount' => 'Amount must be between $'.number_format($loanType->min_amount / 100, 2).
                           ' and $'.number_format($loanType->max_amount / 100, 2),
            ]);
        }

        // Validate term range
        if ($validated['term_months'] < $loanType->min_term_months ||
            $validated['term_months'] > $loanType->max_term_months) {
            return back()->withErrors([
                'term_months' => "Term must be between {$loanType->min_term_months} and {$loanType->max_term_months} months",
            ]);
        }

        // Calculate monthly payment and total payable
        $monthlyRate = $loanType->interest_rate / 100 / 12;
        $monthlyPayment = $amountInCents * ($monthlyRate * pow(1 + $monthlyRate, $validated['term_months'])) /
                         (pow(1 + $monthlyRate, $validated['term_months']) - 1);
        $totalPayable = $monthlyPayment * $validated['term_months'];

        $application = LoanApplication::create([
            'user_id' => $user->id,
            'loan_type_id' => $loanType->id,
            'bank_account_id' => $bankAccount->id,
            'amount' => $amountInCents,
            'term_months' => $validated['term_months'],
            'interest_rate' => $loanType->interest_rate,
            'monthly_payment' => (int) round($monthlyPayment),
            'total_payable' => (int) round($totalPayable),
            'purpose' => $validated['purpose'],
            'status' => 'pending',
        ]);

        // Notify admins about new loan application
        AdminNotificationService::loanApplicationSubmitted($application, $user);

        return redirect()->route('loans.applications')
            ->with('success', 'Loan application submitted successfully. Reference: '.$application->reference_number);
    }

    /**
     * Display user's active loans
     */
    public function index(Request $request)
    {
        $loans = $request->user()
            ->loans()
            ->with(['loanApplication.loanType', 'bankAccount'])
            ->whereIn('status', ['active', 'disbursed'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn ($loan) => [
                'id' => $loan->id,
                'uuid' => $loan->uuid,
                'loan_type' => $loan->loanApplication->loanType->name ?? null,
                'bank_account_id' => $loan->bank_account_id,
                'account_number' => $loan->bankAccount->account_number ?? null,
                'principal_amount' => $loan->principal_amount / 100,
                'outstanding_balance' => $loan->outstanding_balance / 100,
                'interest_rate' => $loan->interest_rate,
                'monthly_payment' => $loan->monthly_payment / 100,
                'next_payment_date' => $loan->next_payment_date?->format('M d, Y'),
                'final_payment_date' => $loan->final_payment_date?->format('M d, Y'),
                'status' => $loan->status->value,
                'status_label' => $loan->status->label(),
                'status_color' => $loan->status->color(),
                'disbursed_at' => $loan->disbursed_at?->format('M d, Y'),
            ]);

        $stats = [
            'total_loans' => $request->user()->loans()->whereIn('status', ['active', 'disbursed'])->count(),
            'total_outstanding' => $request->user()->loans()->whereIn('status', ['active', 'disbursed'])->sum('outstanding_balance') / 100,
            'next_payment' => $request->user()->loans()
                ->whereIn('status', ['active', 'disbursed'])
                ->orderBy('next_payment_date')
                ->value('monthly_payment') / 100 ?? 0,
        ];

        return Inertia::render('Loans/Index', [
            'loans' => $loans,
            'stats' => $stats,
        ]);
    }
}
