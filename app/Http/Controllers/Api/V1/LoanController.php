<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\LoanApplicationStatus;
use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\LoanDocument;
use App\Models\LoanRepayment;
use App\Models\LoanType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    /**
     * Get available loan types.
     */
    public function types(): JsonResponse
    {
        $types = LoanType::where('is_active', true)
            ->get()
            ->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'min_amount' => $type->min_amount / 100,
                'max_amount' => $type->max_amount / 100,
                'min_term_months' => $type->min_term_months,
                'max_term_months' => $type->max_term_months,
                'interest_rate' => $type->interest_rate,
                'required_documents' => $type->required_documents,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'loan_types' => $types,
            ],
        ]);
    }

    /**
     * Calculate loan repayment.
     */
    public function calculator(Request $request): JsonResponse
    {
        $request->validate([
            'loan_type_id' => 'required|exists:loan_types,id',
            'amount' => 'required|numeric|min:100',
            'term_months' => 'required|integer|min:1',
        ]);

        $loanType = LoanType::findOrFail($request->loan_type_id);

        $amountInCents = $request->amount * 100;

        // Validate against loan type limits
        if ($amountInCents < $loanType->min_amount || $amountInCents > $loanType->max_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Amount must be between $'.($loanType->min_amount / 100).' and $'.($loanType->max_amount / 100),
            ], 422);
        }

        if ($request->term_months < $loanType->min_term_months || $request->term_months > $loanType->max_term_months) {
            return response()->json([
                'success' => false,
                'message' => "Term must be between {$loanType->min_term_months} and {$loanType->max_term_months} months",
            ], 422);
        }

        // Calculate monthly payment using amortization formula
        $monthlyRate = $loanType->interest_rate / 100 / 12;
        $monthlyPayment = $this->calculateMonthlyPayment($amountInCents, $monthlyRate, $request->term_months);
        $totalPayment = $monthlyPayment * $request->term_months;
        $totalInterest = $totalPayment - $amountInCents;

        // Generate repayment schedule
        $schedule = $this->generateRepaymentSchedule($amountInCents, $monthlyRate, $request->term_months);

        return response()->json([
            'success' => true,
            'data' => [
                'loan_type' => $loanType->name,
                'principal' => $request->amount,
                'term_months' => $request->term_months,
                'interest_rate' => $loanType->interest_rate,
                'monthly_payment' => round($monthlyPayment / 100, 2),
                'total_payment' => round($totalPayment / 100, 2),
                'total_interest' => round($totalInterest / 100, 2),
                'schedule' => array_map(fn ($s) => [
                    'month' => $s['month'],
                    'payment' => round($s['payment'] / 100, 2),
                    'principal' => round($s['principal'] / 100, 2),
                    'interest' => round($s['interest'] / 100, 2),
                    'balance' => round($s['balance'] / 100, 2),
                ], $schedule),
            ],
        ]);
    }

    /**
     * Get user's loans.
     */
    public function index(Request $request): JsonResponse
    {
        $loans = Loan::where('user_id', $request->user()->id)
            ->with(['loanType', 'disbursementAccount'])
            ->latest()
            ->get()
            ->map(fn ($loan) => $this->formatLoan($loan));

        return response()->json([
            'success' => true,
            'data' => [
                'loans' => $loans,
            ],
        ]);
    }

    /**
     * Get user's loan applications.
     */
    public function applications(Request $request): JsonResponse
    {
        $applications = LoanApplication::where('user_id', $request->user()->id)
            ->with(['loanType'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'applications' => $applications->items(),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                ],
            ],
        ]);
    }

    /**
     * Apply for a loan.
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'loan_type_id' => 'required|exists:loan_types,id',
            'amount' => 'required|numeric|min:100',
            'term_months' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'employment_status' => 'required|string|in:employed,self_employed,unemployed,retired',
            'monthly_income' => 'required|numeric|min:0',
            'employer_name' => 'nullable|string|max:255',
            'employer_phone' => 'nullable|string|max:20',
        ]);

        $loanType = LoanType::findOrFail($request->loan_type_id);
        $amountInCents = $request->amount * 100;

        // Validate limits
        if ($amountInCents < $loanType->min_amount || $amountInCents > $loanType->max_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Amount must be between $'.($loanType->min_amount / 100).' and $'.($loanType->max_amount / 100),
            ], 422);
        }

        // Check for existing pending applications
        $existingApplication = LoanApplication::where('user_id', $request->user()->id)
            ->where('status', LoanApplicationStatus::Pending)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending loan application.',
            ], 422);
        }

        // Calculate interest rate and monthly payment
        $monthlyRate = $loanType->interest_rate / 100 / 12;
        $monthlyPayment = $this->calculateMonthlyPayment($amountInCents, $monthlyRate, $request->term_months);

        $application = LoanApplication::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'loan_type_id' => $loanType->id,
            'application_number' => 'LA'.strtoupper(Str::random(12)),
            'amount' => $amountInCents,
            'term_months' => $request->term_months,
            'interest_rate' => $loanType->interest_rate,
            'monthly_payment' => $monthlyPayment,
            'purpose' => $request->purpose,
            'employment_status' => $request->employment_status,
            'monthly_income' => $request->monthly_income * 100,
            'employer_name' => $request->employer_name,
            'employer_phone' => $request->employer_phone,
            'status' => LoanApplicationStatus::Pending,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan application submitted successfully. Please upload required documents.',
            'data' => [
                'application' => $application,
                'required_documents' => $loanType->required_documents,
            ],
        ], 201);
    }

    /**
     * Show loan application.
     */
    public function showApplication(Request $request, LoanApplication $application): JsonResponse
    {
        if ($application->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'application' => $application->load(['loanType', 'documents']),
            ],
        ]);
    }

    /**
     * Upload loan documents.
     */
    public function uploadDocuments(Request $request, LoanApplication $application): JsonResponse
    {
        if ($application->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($application->status !== LoanApplicationStatus::Pending &&
            $application->status !== LoanApplicationStatus::DocumentsRequired) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot upload documents for this application.',
            ], 422);
        }

        $request->validate([
            'documents' => 'required|array|min:1',
            'documents.*.type' => 'required|string|max:100',
            'documents.*.file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $uploadedDocuments = [];
        foreach ($request->documents as $doc) {
            $path = $doc['file']->store('loan-documents/'.$application->id, 'public');

            $document = LoanDocument::create([
                'loan_application_id' => $application->id,
                'document_type' => $doc['type'],
                'file_path' => $path,
                'file_name' => $doc['file']->getClientOriginalName(),
                'file_size' => $doc['file']->getSize(),
            ]);

            $uploadedDocuments[] = $document;
        }

        // Update application status to Under Review if all documents uploaded
        $application->update([
            'status' => LoanApplicationStatus::UnderReview,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully. Your application is under review.',
            'data' => [
                'documents' => $uploadedDocuments,
            ],
        ]);
    }

    /**
     * Show loan details.
     */
    public function show(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'loan' => $this->formatLoan($loan->load(['loanType', 'disbursementAccount', 'repayments']), true),
            ],
        ]);
    }

    /**
     * Get loan repayment schedule.
     */
    public function repaymentSchedule(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $monthlyRate = $loan->interest_rate / 100 / 12;
        $schedule = $this->generateRepaymentSchedule(
            $loan->principal_amount,
            $monthlyRate,
            $loan->term_months,
            $loan->start_date
        );

        return response()->json([
            'success' => true,
            'data' => [
                'schedule' => array_map(fn ($s) => [
                    'month' => $s['month'],
                    'due_date' => $s['due_date'] ?? null,
                    'payment' => round($s['payment'] / 100, 2),
                    'principal' => round($s['principal'] / 100, 2),
                    'interest' => round($s['interest'] / 100, 2),
                    'balance' => round($s['balance'] / 100, 2),
                ], $schedule),
            ],
        ]);
    }

    /**
     * Make a loan repayment.
     */
    public function makeRepayment(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($loan->status !== LoanStatus::Active) {
            return response()->json([
                'success' => false,
                'message' => 'Loan is not active.',
            ], 422);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'account_id' => 'required|exists:bank_accounts,uuid',
        ]);

        $account = $request->user()->accounts()
            ->where('uuid', $request->account_id)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid account.',
            ], 422);
        }

        $amountInCents = $request->amount * 100;

        if ($account->balance < $amountInCents) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance.',
            ], 422);
        }

        // Calculate how much goes to principal vs interest
        $monthlyRate = $loan->interest_rate / 100 / 12;
        $interestDue = $loan->outstanding_balance * $monthlyRate;
        $principalPaid = max(0, $amountInCents - $interestDue);
        $interestPaid = min($amountInCents, $interestDue);

        // Deduct from account
        $account->decrement('balance', $amountInCents);

        // Record repayment
        $repayment = LoanRepayment::create([
            'loan_id' => $loan->id,
            'reference_number' => 'LR'.strtoupper(Str::random(12)),
            'amount' => $amountInCents,
            'principal_paid' => $principalPaid,
            'interest_paid' => $interestPaid,
            'payment_method' => 'account',
            'payment_date' => now(),
            'status' => 'completed',
        ]);

        // Update loan balance
        $newBalance = $loan->outstanding_balance - $principalPaid;
        $loan->update([
            'outstanding_balance' => max(0, $newBalance),
            'total_paid' => $loan->total_paid + $amountInCents,
            'status' => $newBalance <= 0 ? LoanStatus::PaidOff : LoanStatus::Active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $newBalance <= 0
                ? 'Congratulations! Your loan has been fully paid off.'
                : 'Payment successful.',
            'data' => [
                'repayment' => $repayment,
                'remaining_balance' => round(max(0, $newBalance) / 100, 2),
            ],
        ]);
    }

    // ==================== HELPERS ====================

    private function calculateMonthlyPayment(int $principal, float $monthlyRate, int $termMonths): int
    {
        if ($monthlyRate === 0.0) {
            return (int) ceil($principal / $termMonths);
        }

        $payment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / (pow(1 + $monthlyRate, $termMonths) - 1);

        return (int) ceil($payment);
    }

    private function generateRepaymentSchedule(int $principal, float $monthlyRate, int $termMonths, $startDate = null): array
    {
        $schedule = [];
        $balance = $principal;
        $monthlyPayment = $this->calculateMonthlyPayment($principal, $monthlyRate, $termMonths);
        $date = $startDate ? \Carbon\Carbon::parse($startDate) : now();

        for ($month = 1; $month <= $termMonths; $month++) {
            $interestPayment = (int) round($balance * $monthlyRate);
            $principalPayment = min($balance, $monthlyPayment - $interestPayment);

            // Last payment adjustment
            if ($month === $termMonths) {
                $principalPayment = $balance;
                $monthlyPayment = $principalPayment + $interestPayment;
            }

            $balance -= $principalPayment;

            $schedule[] = [
                'month' => $month,
                'due_date' => $date->copy()->addMonths($month)->format('Y-m-d'),
                'payment' => $monthlyPayment,
                'principal' => $principalPayment,
                'interest' => $interestPayment,
                'balance' => max(0, $balance),
            ];
        }

        return $schedule;
    }

    private function formatLoan(Loan $loan, bool $detailed = false): array
    {
        $data = [
            'uuid' => $loan->uuid,
            'loan_number' => $loan->loan_number,
            'type' => $loan->loanType?->name,
            'principal_amount' => $loan->principal_amount / 100,
            'outstanding_balance' => $loan->outstanding_balance / 100,
            'total_paid' => $loan->total_paid / 100,
            'interest_rate' => $loan->interest_rate,
            'term_months' => $loan->term_months,
            'monthly_payment' => $loan->monthly_payment / 100,
            'status' => $loan->status->value,
            'start_date' => $loan->start_date?->format('Y-m-d'),
            'end_date' => $loan->end_date?->format('Y-m-d'),
            'next_payment_date' => $loan->next_payment_date?->format('Y-m-d'),
        ];

        if ($detailed) {
            $data['repayments'] = $loan->repayments->map(fn ($r) => [
                'reference' => $r->reference_number,
                'amount' => $r->amount / 100,
                'principal_paid' => $r->principal_paid / 100,
                'interest_paid' => $r->interest_paid / 100,
                'payment_date' => $r->payment_date->format('Y-m-d'),
            ]);
        }

        return $data;
    }
}
