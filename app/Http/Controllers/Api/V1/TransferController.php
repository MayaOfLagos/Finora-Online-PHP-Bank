<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransferStatus;
use App\Enums\TransferStep;
use App\Http\Controllers\Controller;
use App\Models\AccountTransfer;
use App\Models\BankAccount;
use App\Models\DomesticTransfer;
use App\Models\InternalTransfer;
use App\Models\User;
use App\Models\VerificationCode;
use App\Models\WireTransfer;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    // ==================== WIRE TRANSFERS ====================

    /**
     * List user's wire transfers.
     */
    public function wireIndex(Request $request): JsonResponse
    {
        $transfers = WireTransfer::where('user_id', $request->user()->id)
            ->with(['sourceAccount'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'transfers' => $transfers->items(),
                'pagination' => [
                    'current_page' => $transfers->currentPage(),
                    'last_page' => $transfers->lastPage(),
                    'per_page' => $transfers->perPage(),
                    'total' => $transfers->total(),
                ],
            ],
        ]);
    }

    /**
     * Initiate a wire transfer.
     */
    public function wireInitiate(Request $request): JsonResponse
    {
        $request->validate([
            'source_account_id' => 'required|exists:bank_accounts,uuid',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'beneficiary_name' => 'required|string|max:255',
            'beneficiary_account_number' => 'required|string|max:50',
            'beneficiary_bank_name' => 'required|string|max:255',
            'beneficiary_bank_address' => 'nullable|string|max:500',
            'swift_code' => 'required|string|max:11',
            'beneficiary_country' => 'required|string|size:2',
            'purpose' => 'nullable|string|max:500',
        ]);

        $sourceAccount = BankAccount::where('uuid', $request->source_account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $sourceAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid source account.',
            ], 422);
        }

        $amountInCents = $request->amount * 100;

        if ($sourceAccount->balance < $amountInCents) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance.',
            ], 422);
        }

        $transfer = WireTransfer::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'source_account_id' => $sourceAccount->id,
            'reference_number' => 'WT'.strtoupper(Str::random(12)),
            'amount' => $amountInCents,
            'currency' => $request->currency,
            'fee' => 2500, // $25 wire transfer fee
            'beneficiary_name' => $request->beneficiary_name,
            'beneficiary_account_number' => $request->beneficiary_account_number,
            'beneficiary_bank_name' => $request->beneficiary_bank_name,
            'beneficiary_bank_address' => $request->beneficiary_bank_address,
            'swift_code' => $request->swift_code,
            'beneficiary_country' => $request->beneficiary_country,
            'purpose' => $request->purpose,
            'status' => TransferStatus::Pending,
            'current_step' => TransferStep::Pin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wire transfer initiated. Please verify your transaction PIN.',
            'data' => [
                'transfer' => $this->formatWireTransfer($transfer),
                'next_step' => 'verify_pin',
            ],
        ], 201);
    }

    /**
     * Verify transaction PIN for wire transfer.
     */
    public function verifyPin(Request $request, WireTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        if (! Hash::check($request->pin, $request->user()->transaction_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN.',
            ], 422);
        }

        $transfer->update([
            'current_step' => TransferStep::ImfCode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN verified. Please enter IMF code.',
            'data' => [
                'transfer' => $this->formatWireTransfer($transfer->fresh()),
                'next_step' => 'verify_imf',
            ],
        ]);
    }

    /**
     * Verify IMF code for wire transfer.
     */
    public function verifyImfCode(Request $request, WireTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'code' => 'required|string',
        ]);

        // Check verification code in database
        $verification = VerificationCode::where('user_id', $request->user()->id)
            ->where('type', 'imf')
            ->where('code', $request->code)
            ->where('is_used', false)
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid IMF code. Please contact support.',
            ], 422);
        }

        $transfer->update([
            'current_step' => TransferStep::TaxCode,
            'imf_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'IMF code verified. Please enter Tax code.',
            'data' => [
                'transfer' => $this->formatWireTransfer($transfer->fresh()),
                'next_step' => 'verify_tax',
            ],
        ]);
    }

    /**
     * Verify Tax code for wire transfer.
     */
    public function verifyTaxCode(Request $request, WireTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'code' => 'required|string',
        ]);

        // Check verification code in database
        $verification = VerificationCode::where('user_id', $request->user()->id)
            ->where('type', 'tax')
            ->where('code', $request->code)
            ->where('is_used', false)
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Tax code. Please contact support.',
            ], 422);
        }

        $transfer->update([
            'current_step' => TransferStep::CotCode,
            'tax_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tax code verified. Please enter COT code.',
            'data' => [
                'transfer' => $this->formatWireTransfer($transfer->fresh()),
                'next_step' => 'verify_cot',
            ],
        ]);
    }

    /**
     * Verify COT code for wire transfer.
     */
    public function verifyCotCode(Request $request, WireTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'code' => 'required|string',
        ]);

        // Check verification code in database
        $verification = VerificationCode::where('user_id', $request->user()->id)
            ->where('type', 'cot')
            ->where('code', $request->code)
            ->where('is_used', false)
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid COT code. Please contact support.',
            ], 422);
        }

        // Send OTP to user's email
        $this->otpService->send($request->user(), 'wire_transfer', $transfer->id);

        $transfer->update([
            'current_step' => TransferStep::Otp,
            'cot_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'COT code verified. An OTP has been sent to your email.',
            'data' => [
                'transfer' => $this->formatWireTransfer($transfer->fresh()),
                'next_step' => 'verify_otp',
            ],
        ]);
    }

    /**
     * Verify OTP and complete wire transfer.
     */
    public function verifyOtp(Request $request, WireTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if (! $this->otpService->verify($request->user(), $request->otp, 'wire_transfer', $transfer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        // Deduct from source account
        $sourceAccount = $transfer->sourceAccount;
        $totalAmount = $transfer->amount + $transfer->fee;
        $sourceAccount->decrement('balance', $totalAmount);

        // Mark transfer as completed
        $transfer->update([
            'status' => TransferStatus::Completed,
            'current_step' => TransferStep::Completed,
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wire transfer completed successfully.',
            'data' => [
                'transfer' => $this->formatWireTransfer($transfer->fresh()),
            ],
        ]);
    }

    /**
     * Show wire transfer details.
     */
    public function wireShow(Request $request, WireTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transfer' => $this->formatWireTransfer($transfer),
            ],
        ]);
    }

    // ==================== INTERNAL TRANSFERS ====================

    /**
     * List internal transfers.
     */
    public function internalIndex(Request $request): JsonResponse
    {
        $transfers = InternalTransfer::where('user_id', $request->user()->id)
            ->with(['sourceAccount', 'destinationAccount', 'recipient'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'transfers' => $transfers->items(),
                'pagination' => [
                    'current_page' => $transfers->currentPage(),
                    'last_page' => $transfers->lastPage(),
                    'per_page' => $transfers->perPage(),
                    'total' => $transfers->total(),
                ],
            ],
        ]);
    }

    /**
     * Initiate internal transfer.
     */
    public function internalInitiate(Request $request): JsonResponse
    {
        $request->validate([
            'source_account_id' => 'required|exists:bank_accounts,uuid',
            'destination_account_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $sourceAccount = BankAccount::where('uuid', $request->source_account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $sourceAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid source account.',
            ], 422);
        }

        $destinationAccount = BankAccount::where('account_number', $request->destination_account_number)
            ->where('is_active', true)
            ->first();

        if (! $destinationAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Destination account not found.',
            ], 422);
        }

        if ($sourceAccount->id === $destinationAccount->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transfer to the same account.',
            ], 422);
        }

        $amountInCents = $request->amount * 100;

        if ($sourceAccount->balance < $amountInCents) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance.',
            ], 422);
        }

        $transfer = InternalTransfer::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'source_account_id' => $sourceAccount->id,
            'destination_account_id' => $destinationAccount->id,
            'recipient_id' => $destinationAccount->user_id,
            'reference_number' => 'IT'.strtoupper(Str::random(12)),
            'amount' => $amountInCents,
            'fee' => 0, // No fee for internal transfers
            'description' => $request->description,
            'status' => TransferStatus::Pending,
            'current_step' => TransferStep::Pin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Internal transfer initiated. Please verify your transaction PIN.',
            'data' => [
                'transfer' => $transfer,
                'recipient_name' => $destinationAccount->user->full_name,
                'next_step' => 'verify_pin',
            ],
        ], 201);
    }

    /**
     * Verify PIN for internal transfer.
     */
    public function verifyInternalPin(Request $request, InternalTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        if (! Hash::check($request->pin, $request->user()->transaction_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN.',
            ], 422);
        }

        // Send OTP
        $this->otpService->send($request->user(), 'internal_transfer', $transfer->id);

        $transfer->update([
            'current_step' => TransferStep::Otp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN verified. An OTP has been sent to your email.',
            'data' => [
                'transfer' => $transfer->fresh(),
                'next_step' => 'verify_otp',
            ],
        ]);
    }

    /**
     * Verify OTP and complete internal transfer.
     */
    public function verifyInternalOtp(Request $request, InternalTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if (! $this->otpService->verify($request->user(), $request->otp, 'internal_transfer', $transfer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        // Perform the transfer
        $transfer->sourceAccount->decrement('balance', $transfer->amount);
        $transfer->destinationAccount->increment('balance', $transfer->amount);

        $transfer->update([
            'status' => TransferStatus::Completed,
            'current_step' => TransferStep::Completed,
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Internal transfer completed successfully.',
            'data' => [
                'transfer' => $transfer->fresh(),
            ],
        ]);
    }

    /**
     * Show internal transfer details.
     */
    public function internalShow(Request $request, InternalTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transfer' => $transfer->load(['sourceAccount', 'destinationAccount', 'recipient']),
            ],
        ]);
    }

    // ==================== DOMESTIC TRANSFERS ====================

    public function domesticIndex(Request $request): JsonResponse
    {
        $transfers = DomesticTransfer::where('user_id', $request->user()->id)
            ->with(['sourceAccount', 'bank'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'transfers' => $transfers->items(),
                'pagination' => [
                    'current_page' => $transfers->currentPage(),
                    'last_page' => $transfers->lastPage(),
                    'per_page' => $transfers->perPage(),
                    'total' => $transfers->total(),
                ],
            ],
        ]);
    }

    public function domesticInitiate(Request $request): JsonResponse
    {
        $request->validate([
            'source_account_id' => 'required|exists:bank_accounts,uuid',
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'routing_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $sourceAccount = BankAccount::where('uuid', $request->source_account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $sourceAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid source account.',
            ], 422);
        }

        $amountInCents = $request->amount * 100;
        $fee = 500; // $5 domestic transfer fee

        if ($sourceAccount->balance < ($amountInCents + $fee)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance.',
            ], 422);
        }

        $transfer = DomesticTransfer::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'source_account_id' => $sourceAccount->id,
            'bank_id' => $request->bank_id,
            'reference_number' => 'DT'.strtoupper(Str::random(12)),
            'amount' => $amountInCents,
            'fee' => $fee,
            'beneficiary_account_number' => $request->account_number,
            'beneficiary_name' => $request->account_name,
            'routing_number' => $request->routing_number,
            'description' => $request->description,
            'status' => TransferStatus::Pending,
            'current_step' => TransferStep::Pin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Domestic transfer initiated. Please verify your transaction PIN.',
            'data' => [
                'transfer' => $transfer,
                'next_step' => 'verify_pin',
            ],
        ], 201);
    }

    public function verifyDomesticPin(Request $request, DomesticTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        if (! Hash::check($request->pin, $request->user()->transaction_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN.',
            ], 422);
        }

        $this->otpService->send($request->user(), 'domestic_transfer', $transfer->id);

        $transfer->update([
            'current_step' => TransferStep::Otp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN verified. An OTP has been sent to your email.',
            'data' => [
                'transfer' => $transfer->fresh(),
                'next_step' => 'verify_otp',
            ],
        ]);
    }

    public function verifyDomesticOtp(Request $request, DomesticTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if (! $this->otpService->verify($request->user(), $request->otp, 'domestic_transfer', $transfer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $transfer->sourceAccount->decrement('balance', $transfer->amount + $transfer->fee);

        $transfer->update([
            'status' => TransferStatus::Processing,
            'current_step' => TransferStep::Completed,
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Domestic transfer submitted for processing.',
            'data' => [
                'transfer' => $transfer->fresh(),
            ],
        ]);
    }

    public function domesticShow(Request $request, DomesticTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transfer' => $transfer->load(['sourceAccount', 'bank']),
            ],
        ]);
    }

    // ==================== ACCOUNT TO ACCOUNT TRANSFERS ====================

    public function a2aIndex(Request $request): JsonResponse
    {
        $transfers = AccountTransfer::where('user_id', $request->user()->id)
            ->with(['sourceAccount', 'destinationAccount'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'transfers' => $transfers->items(),
                'pagination' => [
                    'current_page' => $transfers->currentPage(),
                    'last_page' => $transfers->lastPage(),
                    'per_page' => $transfers->perPage(),
                    'total' => $transfers->total(),
                ],
            ],
        ]);
    }

    public function a2aInitiate(Request $request): JsonResponse
    {
        $request->validate([
            'source_account_id' => 'required|exists:bank_accounts,uuid',
            'destination_account_id' => 'required|exists:bank_accounts,uuid',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $sourceAccount = BankAccount::where('uuid', $request->source_account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        $destinationAccount = BankAccount::where('uuid', $request->destination_account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $sourceAccount || ! $destinationAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid account.',
            ], 422);
        }

        if ($sourceAccount->id === $destinationAccount->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transfer to the same account.',
            ], 422);
        }

        $amountInCents = $request->amount * 100;

        if ($sourceAccount->balance < $amountInCents) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance.',
            ], 422);
        }

        $transfer = AccountTransfer::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'source_account_id' => $sourceAccount->id,
            'destination_account_id' => $destinationAccount->id,
            'reference_number' => 'A2A'.strtoupper(Str::random(12)),
            'amount' => $amountInCents,
            'fee' => 0,
            'description' => $request->description,
            'status' => TransferStatus::Pending,
            'current_step' => TransferStep::Pin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transfer initiated. Please verify your transaction PIN.',
            'data' => [
                'transfer' => $transfer,
                'next_step' => 'verify_pin',
            ],
        ], 201);
    }

    public function verifyA2aPin(Request $request, AccountTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        if (! Hash::check($request->pin, $request->user()->transaction_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN.',
            ], 422);
        }

        $this->otpService->send($request->user(), 'a2a_transfer', $transfer->id);

        $transfer->update([
            'current_step' => TransferStep::Otp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN verified. An OTP has been sent to your email.',
            'data' => [
                'transfer' => $transfer->fresh(),
                'next_step' => 'verify_otp',
            ],
        ]);
    }

    public function verifyA2aOtp(Request $request, AccountTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if (! $this->otpService->verify($request->user(), $request->otp, 'a2a_transfer', $transfer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $transfer->sourceAccount->decrement('balance', $transfer->amount);
        $transfer->destinationAccount->increment('balance', $transfer->amount);

        $transfer->update([
            'status' => TransferStatus::Completed,
            'current_step' => TransferStep::Completed,
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transfer completed successfully.',
            'data' => [
                'transfer' => $transfer->fresh(),
            ],
        ]);
    }

    public function a2aShow(Request $request, AccountTransfer $transfer): JsonResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transfer' => $transfer->load(['sourceAccount', 'destinationAccount']),
            ],
        ]);
    }

    // ==================== HELPERS ====================

    private function formatWireTransfer(WireTransfer $transfer): array
    {
        return [
            'uuid' => $transfer->uuid,
            'reference_number' => $transfer->reference_number,
            'amount' => $transfer->amount / 100,
            'fee' => $transfer->fee / 100,
            'total' => ($transfer->amount + $transfer->fee) / 100,
            'currency' => $transfer->currency,
            'beneficiary' => [
                'name' => $transfer->beneficiary_name,
                'account_number' => $transfer->beneficiary_account_number,
                'bank_name' => $transfer->beneficiary_bank_name,
                'swift_code' => $transfer->swift_code,
                'country' => $transfer->beneficiary_country,
            ],
            'status' => $transfer->status->value,
            'current_step' => $transfer->current_step->value,
            'created_at' => $transfer->created_at->toIso8601String(),
            'completed_at' => $transfer->completed_at?->toIso8601String(),
        ];
    }
}
