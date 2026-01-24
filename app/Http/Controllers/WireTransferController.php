<?php

namespace App\Http\Controllers;

use App\Enums\TransferStatus;
use App\Enums\TransferStep;
use App\Models\BankAccount;
use App\Models\Setting;
use App\Models\TransactionHistory;
use App\Models\User;
use App\Models\WireTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class WireTransferController extends Controller
{
    /**
     * Display wire transfer page
     */
    public function index()
    {
        $user = Auth::user();

        $accounts = $user->bankAccounts()
            ->where('is_active', true)
            ->with('accountType')
            ->get();

        $transferLimits = [
            'daily' => (int) Setting::getValue('transfers', 'wire_daily_limit', 100000) * 100,
            'perTransaction' => (int) Setting::getValue('transfers', 'wire_per_transaction_limit', 50000) * 100,
        ];

        $fees = [
            'flat' => (float) Setting::getValue('transfers', 'wire_flat_fee', 25),
            'percentage' => (float) Setting::getValue('transfers', 'wire_percentage_fee', 0.5),
        ];

        // Get verification requirements for user
        $verificationConfig = $this->getVerificationConfig($user);

        // Get dynamic beneficiary fields
        $beneficiaryFields = \App\Models\BeneficiaryFieldTemplate::forTransferType('wire')
            ->map(function ($field) {
                return [
                    'key' => $field->field_key,
                    'label' => $field->field_label,
                    'type' => $field->field_type,
                    'required' => $field->is_required,
                    'placeholder' => $field->placeholder,
                    'helperText' => $field->helper_text,
                    'options' => $field->options,
                    'displayOrder' => $field->display_order,
                ];
            });

        // Get countries for country selector
        $countries = \App\Helpers\Countries::forSelect();

        return Inertia::render('Transfers/Wire', [
            'accounts' => $accounts,
            'transferLimits' => $transferLimits,
            'fees' => $fees,
            'verificationConfig' => $verificationConfig,
            'beneficiaryFields' => $beneficiaryFields,
            'countries' => $countries,
        ]);
    }

    /**
     * Get verification configuration for a user
     */
    private function getVerificationConfig(User $user): array
    {
        $globalOtpEnabled = (bool) Setting::getValue('security', 'transfer_otp_enabled', true);

        return [
            'requiresOtp' => $globalOtpEnabled && ! $user->skip_transfer_otp,
            'hasTransferCodes' => $user->hasTransferCodes(),
            'requiredCodes' => $user->getRequiredTransferCodes(),
        ];
    }

    /**
     * Initiate a wire transfer
     */
    public function initiate(Request $request)
    {
        $user = Auth::user();

        // Get beneficiary fields for validation
        $beneficiaryFields = \App\Models\BeneficiaryFieldTemplate::forTransferType('wire');

        // Build dynamic validation rules
        $validationRules = [
            'from_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:500',
        ];

        // Add validation for each beneficiary field
        foreach ($beneficiaryFields as $field) {
            $rule = $field->field_type === 'textarea' ? 'string|max:1000' : 'string|max:255';
            if ($field->field_key === 'swift_code') {
                $rule = 'string|size:8|regex:/^[A-Z]{6}[A-Z0-9]{2}$/i';
            }
            if ($field->is_required) {
                $rule = 'required|' . $rule;
            } else {
                $rule = 'nullable|' . $rule;
            }
            $validationRules[$field->field_key] = $rule;
        }

        $validated = $request->validate($validationRules);

        // Verify account ownership
        $account = BankAccount::where('id', $validated['from_account_id'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Check user permission
        if (! $user->can_send_wire_transfer) {
            return back()->withErrors(['general' => 'You do not have permission to send wire transfers.']);
        }

        $amountInCents = (int) ($validated['amount'] * 100);

        // Calculate fees
        $flatFee = (float) Setting::getValue('transfers', 'wire_flat_fee', 25) * 100;
        $percentageFee = (float) Setting::getValue('transfers', 'wire_percentage_fee', 0.5);
        $calculatedFee = (int) ($flatFee + ($amountInCents * $percentageFee / 100));
        $totalAmount = $amountInCents + $calculatedFee;

        // Check sufficient balance
        if ($account->balance < $totalAmount) {
            return back()->withErrors(['amount' => 'Insufficient balance including fees.']);
        }

        // Check transfer limits
        $perTransactionLimit = (int) Setting::getValue('transfers', 'wire_per_transaction_limit', 50000) * 100;
        if ($amountInCents > $perTransactionLimit) {
            return back()->withErrors(['amount' => 'Amount exceeds per-transaction limit.']);
        }

        // Prepare beneficiary data
        $beneficiaryData = [];
        $transferData = [
            'user_id' => $user->id,
            'bank_account_id' => $account->id,
            'amount' => $amountInCents,
            'currency' => $account->currency ?? 'USD',
            'exchange_rate' => 1.0,
            'fee' => $calculatedFee,
            'total_amount' => $totalAmount,
            'remarks' => $validated['remarks'] ?? '',
            'status' => TransferStatus::Pending,
            'current_step' => TransferStep::Pin,
        ];

        // Map beneficiary fields to model columns or beneficiary_data
        foreach ($beneficiaryFields as $field) {
            $value = $validated[$field->field_key] ?? null;
            if (in_array($field->field_key, ['beneficiary_name', 'beneficiary_account', 'beneficiary_bank_name', 'beneficiary_bank_address', 'beneficiary_country', 'swift_code', 'routing_number'])) {
                $transferData[$field->field_key] = $value;
            } else {
                $beneficiaryData[$field->field_key] = $value;
            }
        }

        $transferData['beneficiary_data'] = $beneficiaryData;

        // Create wire transfer record
        $transfer = WireTransfer::create($transferData);

        return back()->with([
            'success' => 'Transfer initiated. Please complete verification.',
            'transfer' => [
                'uuid' => $transfer->uuid,
                'reference' => $transfer->reference_number,
                'amount' => $validated['amount'],
                'fee' => $calculatedFee / 100,
                'total' => $totalAmount / 100,
                'currentStep' => 'pin',
            ],
        ]);
    }

    /**
     * Verify PIN for wire transfer
     */
    public function verifyPin(Request $request, WireTransfer $wireTransfer)
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify ownership
        if ($wireTransfer->user_id !== $user->id) {
            return back()->withErrors(['general' => 'Unauthorized access.']);
        }

        // Verify transfer status
        if ($wireTransfer->status !== TransferStatus::Pending) {
            return back()->withErrors(['general' => 'Transfer is no longer pending.']);
        }

        // Verify current step
        if ($wireTransfer->current_step !== TransferStep::Pin) {
            return back()->withErrors(['general' => 'Invalid verification step.']);
        }

        // Verify PIN
        if (! Hash::check($validated['pin'], $user->transaction_pin)) {
            return back()->withErrors(['pin' => 'Invalid transaction PIN.']);
        }

        // Update transfer
        $wireTransfer->update([
            'pin_verified_at' => now(),
            'current_step' => $this->getNextStep($user, TransferStep::Pin),
        ]);

        $nextStep = $wireTransfer->fresh()->current_step;

        return back()->with([
            'success' => 'PIN verified successfully.',
            'nextStep' => $nextStep->value,
        ]);
    }

    /**
     * Verify transfer code (IMF, Tax, or COT)
     */
    public function verifyCode(Request $request, WireTransfer $wireTransfer)
    {
        $validated = $request->validate([
            'code_type' => 'required|in:imf,tax,cot',
            'code' => 'required|string|max:50',
        ]);

        $user = Auth::user();

        // Verify ownership
        if ($wireTransfer->user_id !== $user->id) {
            return back()->withErrors(['general' => 'Unauthorized access.']);
        }

        // Verify transfer status
        if ($wireTransfer->status !== TransferStatus::Pending) {
            return back()->withErrors(['general' => 'Transfer is no longer pending.']);
        }

        // Get the expected step for this code type
        $expectedStep = match ($validated['code_type']) {
            'imf' => TransferStep::Imf,
            'tax' => TransferStep::Tax,
            'cot' => TransferStep::Cot,
        };

        // Verify current step
        if ($wireTransfer->current_step !== $expectedStep) {
            return back()->withErrors(['general' => 'Invalid verification step.']);
        }

        // Verify the code against user's stored code
        $userCode = match ($validated['code_type']) {
            'imf' => $user->imf_code,
            'tax' => $user->tax_code,
            'cot' => $user->cot_code,
        };

        if ($userCode !== $validated['code']) {
            return back()->withErrors(['code' => 'Invalid '.strtoupper($validated['code_type']).' code.']);
        }

        // Update transfer with verification timestamp
        $verifiedAtField = $validated['code_type'].'_verified_at';
        $wireTransfer->update([
            $verifiedAtField => now(),
            'current_step' => $this->getNextStep($user, $expectedStep),
        ]);

        $nextStep = $wireTransfer->fresh()->current_step;

        return back()->with([
            'success' => strtoupper($validated['code_type']).' code verified successfully.',
            'nextStep' => $nextStep->value,
        ]);
    }

    /**
     * Request OTP for wire transfer
     */
    public function requestOtp(Request $request, WireTransfer $wireTransfer)
    {
        $user = Auth::user();

        // Verify ownership
        if ($wireTransfer->user_id !== $user->id) {
            return back()->withErrors(['general' => 'Unauthorized access.']);
        }

        // Verify transfer status
        if ($wireTransfer->status !== TransferStatus::Pending) {
            return back()->withErrors(['general' => 'Transfer is no longer pending.']);
        }

        // Verify current step
        if ($wireTransfer->current_step !== TransferStep::Otp) {
            return back()->withErrors(['general' => 'Invalid verification step.']);
        }

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in session
        session([
            'wire_transfer_otp_'.$wireTransfer->uuid => Hash::make($otp),
            'wire_transfer_otp_expires_'.$wireTransfer->uuid => now()->addMinutes(10),
        ]);

        // Send OTP via email (implement actual email sending)
        // Mail::to($user->email)->send(new WireTransferOtpMail($otp, $wireTransfer));

        // For development, flash the OTP
        session()->flash('dev_otp', $otp);

        return back()->with([
            'success' => 'OTP sent to your email.',
        ]);
    }

    /**
     * Verify OTP and complete wire transfer
     */
    public function verifyOtp(Request $request, WireTransfer $wireTransfer)
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify ownership
        if ($wireTransfer->user_id !== $user->id) {
            return back()->withErrors(['general' => 'Unauthorized access.']);
        }

        // Verify transfer status
        if ($wireTransfer->status !== TransferStatus::Pending) {
            return back()->withErrors(['general' => 'Transfer is no longer pending.']);
        }

        // Verify current step
        if ($wireTransfer->current_step !== TransferStep::Otp) {
            return back()->withErrors(['general' => 'Invalid verification step.']);
        }

        // Verify OTP
        $storedOtp = session('wire_transfer_otp_'.$wireTransfer->uuid);
        $otpExpires = session('wire_transfer_otp_expires_'.$wireTransfer->uuid);

        if (! $storedOtp || ! $otpExpires || now()->isAfter($otpExpires)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if (! Hash::check($validated['otp'], $storedOtp)) {
            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        // Complete the transfer
        return $this->completeTransfer($wireTransfer);
    }

    /**
     * Complete the wire transfer (skip OTP path for users without OTP requirement)
     */
    public function completeTransfer(WireTransfer $wireTransfer)
    {
        $user = Auth::user();

        // Get the source account
        $account = BankAccount::where('id', $wireTransfer->bank_account_id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Verify sufficient balance
        if ($account->balance < $wireTransfer->total_amount) {
            $wireTransfer->update([
                'status' => TransferStatus::Failed,
                'failed_reason' => 'Insufficient balance',
            ]);

            return back()->withErrors(['general' => 'Insufficient balance.']);
        }

        DB::beginTransaction();

        try {
            // Debit the account
            $account->balance -= $wireTransfer->total_amount;
            $account->save();

            // Update transfer status
            $wireTransfer->update([
                'otp_verified_at' => now(),
                'current_step' => TransferStep::Completed,
                'status' => TransferStatus::Processing, // Wire transfers need processing time
                'completed_at' => now(),
            ]);

            // Create transaction history
            TransactionHistory::create([
                'user_id' => $user->id,
                'bank_account_id' => $account->id,
                'transaction_type' => 'wire_transfer',
                'reference_number' => $wireTransfer->reference_number,
                'transactionable_type' => WireTransfer::class,
                'transactionable_id' => $wireTransfer->id,
                'amount' => $wireTransfer->amount / 100,
                'type' => 'debit',
                'balance_after' => $account->balance / 100,
                'currency' => $wireTransfer->currency,
                'status' => 'processing',
                'description' => 'Wire transfer to '.$wireTransfer->beneficiary_name,
                'processed_at' => now(),
            ]);

            // Clear OTP session
            session()->forget([
                'wire_transfer_otp_'.$wireTransfer->uuid,
                'wire_transfer_otp_expires_'.$wireTransfer->uuid,
            ]);

            DB::commit();

            return back()->with([
                'success' => 'Wire transfer submitted successfully. It will be processed within 3-5 business days.',
                'transfer' => [
                    'uuid' => $wireTransfer->uuid,
                    'reference' => $wireTransfer->reference_number,
                    'amount' => $wireTransfer->amount / 100,
                    'fee' => $wireTransfer->fee / 100,
                    'total' => $wireTransfer->total_amount / 100,
                    'status' => 'processing',
                    'beneficiary' => $wireTransfer->beneficiary_name,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $wireTransfer->update([
                'status' => TransferStatus::Failed,
                'failed_reason' => 'Processing error',
            ]);

            return back()->withErrors(['general' => 'Transfer failed. Please try again.']);
        }
    }

    /**
     * Get the next verification step based on user configuration
     */
    private function getNextStep(User $user, TransferStep $currentStep): TransferStep
    {
        $steps = [
            TransferStep::Pin->value => [
                'next_if_has_imf' => TransferStep::Imf,
                'next_if_has_tax' => TransferStep::Tax,
                'next_if_has_cot' => TransferStep::Cot,
                'next_if_requires_otp' => TransferStep::Otp,
                'final' => TransferStep::Completed,
            ],
            TransferStep::Imf->value => [
                'next_if_has_tax' => TransferStep::Tax,
                'next_if_has_cot' => TransferStep::Cot,
                'next_if_requires_otp' => TransferStep::Otp,
                'final' => TransferStep::Completed,
            ],
            TransferStep::Tax->value => [
                'next_if_has_cot' => TransferStep::Cot,
                'next_if_requires_otp' => TransferStep::Otp,
                'final' => TransferStep::Completed,
            ],
            TransferStep::Cot->value => [
                'next_if_requires_otp' => TransferStep::Otp,
                'final' => TransferStep::Completed,
            ],
            TransferStep::Otp->value => [
                'final' => TransferStep::Completed,
            ],
        ];

        $config = $steps[$currentStep->value] ?? ['final' => TransferStep::Completed];

        // Check for IMF
        if (isset($config['next_if_has_imf']) && $user->imf_code && ! $this->isCodeVerified($currentStep, 'imf')) {
            return $config['next_if_has_imf'];
        }

        // Check for Tax
        if (isset($config['next_if_has_tax']) && $user->tax_code && ! $this->isCodeVerified($currentStep, 'tax')) {
            return $config['next_if_has_tax'];
        }

        // Check for COT
        if (isset($config['next_if_has_cot']) && $user->cot_code && ! $this->isCodeVerified($currentStep, 'cot')) {
            return $config['next_if_has_cot'];
        }

        // Check if OTP is required
        if (isset($config['next_if_requires_otp']) && $user->requiresTransferOtp()) {
            return $config['next_if_requires_otp'];
        }

        return $config['final'];
    }

    /**
     * Helper to check if a code step has been passed
     */
    private function isCodeVerified(TransferStep $currentStep, string $codeType): bool
    {
        $order = [
            'imf' => TransferStep::Imf->order(),
            'tax' => TransferStep::Tax->order(),
            'cot' => TransferStep::Cot->order(),
        ];

        return $currentStep->order() >= $order[$codeType];
    }
}
