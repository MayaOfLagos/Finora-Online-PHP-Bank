<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BeneficiaryType;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Beneficiary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BeneficiaryController extends Controller
{
    /**
     * Get user's beneficiaries.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Beneficiary::where('user_id', $request->user()->id)
            ->with('bank');

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $beneficiaries = $query->orderBy('nickname')
            ->get()
            ->map(fn ($b) => $this->formatBeneficiary($b));

        return response()->json([
            'success' => true,
            'data' => [
                'beneficiaries' => $beneficiaries,
            ],
        ]);
    }

    /**
     * Create a new beneficiary.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:internal,domestic,international',
            'nickname' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'bank_id' => 'nullable|exists:banks,id',
            'bank_name' => 'nullable|string|max:255',
            'routing_number' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:11',
            'country' => 'nullable|string|size:2',
        ]);

        $type = BeneficiaryType::from($request->type);

        // Validate type-specific requirements
        if ($type === BeneficiaryType::Internal) {
            // Verify internal account exists
            $internalAccount = BankAccount::where('account_number', $request->account_number)
                ->where('is_active', true)
                ->first();

            if (! $internalAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not found within Finora Bank.',
                ], 422);
            }
        }

        if ($type === BeneficiaryType::Domestic && ! $request->bank_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bank selection is required for domestic beneficiaries.',
            ], 422);
        }

        if ($type === BeneficiaryType::International && (! $request->swift_code || ! $request->country)) {
            return response()->json([
                'success' => false,
                'message' => 'SWIFT code and country are required for international beneficiaries.',
            ], 422);
        }

        // Check for duplicates
        $existing = Beneficiary::where('user_id', $request->user()->id)
            ->where('account_number', $request->account_number)
            ->where('type', $type)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This beneficiary already exists.',
            ], 422);
        }

        $beneficiary = Beneficiary::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'type' => $type,
            'nickname' => $request->nickname,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'bank_id' => $request->bank_id,
            'bank_name' => $request->bank_name ?? Bank::find($request->bank_id)?->name,
            'routing_number' => $request->routing_number,
            'swift_code' => $request->swift_code,
            'country' => $request->country ?? 'US',
            'is_verified' => $type === BeneficiaryType::Internal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary added successfully.',
            'data' => [
                'beneficiary' => $this->formatBeneficiary($beneficiary),
            ],
        ], 201);
    }

    /**
     * Get beneficiary details.
     */
    public function show(Request $request, Beneficiary $beneficiary): JsonResponse
    {
        if ($beneficiary->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'beneficiary' => $this->formatBeneficiary($beneficiary->load('bank')),
            ],
        ]);
    }

    /**
     * Update beneficiary.
     */
    public function update(Request $request, Beneficiary $beneficiary): JsonResponse
    {
        if ($beneficiary->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'nickname' => 'sometimes|string|max:100',
        ]);

        $beneficiary->update([
            'nickname' => $request->nickname ?? $beneficiary->nickname,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary updated successfully.',
            'data' => [
                'beneficiary' => $this->formatBeneficiary($beneficiary),
            ],
        ]);
    }

    /**
     * Delete beneficiary.
     */
    public function destroy(Request $request, Beneficiary $beneficiary): JsonResponse
    {
        if ($beneficiary->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $beneficiary->delete();

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary deleted successfully.',
        ]);
    }

    // ==================== HELPERS ====================

    private function formatBeneficiary(Beneficiary $beneficiary): array
    {
        return [
            'uuid' => $beneficiary->uuid,
            'type' => $beneficiary->type->value,
            'nickname' => $beneficiary->nickname,
            'account_number' => $beneficiary->account_number,
            'account_name' => $beneficiary->account_name,
            'bank_name' => $beneficiary->bank_name ?? $beneficiary->bank?->name,
            'routing_number' => $beneficiary->routing_number,
            'swift_code' => $beneficiary->swift_code,
            'country' => $beneficiary->country,
            'is_verified' => $beneficiary->is_verified,
            'created_at' => $beneficiary->created_at->toIso8601String(),
        ];
    }
}
