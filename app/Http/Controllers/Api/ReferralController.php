<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;

class ReferralController extends Controller
{
    public function __construct(
        protected ReferralService $referralService
    ) {}

    /**
     * Validate a referral code and return inviter info.
     */
    public function validateCode(string $code): JsonResponse
    {
        // Check if referral system is enabled
        if (! $this->referralService->isEnabled()) {
            return response()->json([
                'valid' => false,
                'message' => 'Referral program is currently unavailable.',
            ]);
        }

        // Validate the code format
        $code = strtoupper(trim($code));

        if (strlen($code) < 6) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid referral code format.',
            ]);
        }

        // Check if code exists
        $validation = $this->referralService->validateReferralCode($code);

        if (! $validation['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $validation['message'] ?? 'Invalid or expired referral code.',
            ]);
        }

        // Get inviter info
        $inviterInfo = $this->referralService->getReferrerInfo($code);

        if (! $inviterInfo) {
            return response()->json([
                'valid' => false,
                'message' => 'Unable to retrieve referrer information.',
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Valid referral code!',
            'inviter' => [
                'name' => $inviterInfo['name'],
                'avatar' => $inviterInfo['avatar'] ?? null,
                'level' => $inviterInfo['level'] ?? null,
            ],
            'bonus_enabled' => $this->referralService->isNewUserBonusEnabled(),
            'bonus_amount' => $this->referralService->isNewUserBonusEnabled()
                ? $this->referralService->getNewUserBonusAmount()
                : 0,
        ]);
    }

    /**
     * Get referral program info (public endpoint).
     */
    public function info(): JsonResponse
    {
        return response()->json([
            'enabled' => $this->referralService->isEnabled(),
            'new_user_bonus_enabled' => $this->referralService->isNewUserBonusEnabled(),
            'new_user_bonus_amount' => $this->referralService->isNewUserBonusEnabled()
                ? $this->referralService->getNewUserBonusAmount()
                : 0,
        ]);
    }
}
