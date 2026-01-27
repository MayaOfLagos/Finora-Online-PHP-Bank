<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\ChangeTransactionPinRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\SetTransactionPinRequest;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'uuid' => Str::uuid(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'address_line_1' => $request->address_line_1,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country ?? 'US',
            'is_active' => true,
            'kyc_level' => 0,
        ]);

        // Create a default checking account for the new user
        $checkingType = AccountType::where('code', 'CHK')->first();

        if ($checkingType) {
            BankAccount::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'account_type_id' => $checkingType->id,
                'account_number' => $this->generateAccountNumber(),
                'balance' => 0,
                'currency' => 'USD',
                'is_primary' => true,
                'is_active' => true,
                'opened_at' => now(),
            ]);
        }

        // Send email verification
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email.',
            'data' => [
                'user' => $user->only(['uuid', 'first_name', 'last_name', 'email', 'phone_number']),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login user and return token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Check if 2FA is enabled
        if ($user->two_factor_secret) {
            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication required.',
                'data' => [
                    'requires_2fa' => true,
                    'user_id' => $user->uuid,
                ],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $this->getUserData($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->getUserData($request->user()),
            ],
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => $this->getUserData($user->fresh()),
            ],
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Set transaction PIN.
     */
    public function setTransactionPin(SetTransactionPinRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction PIN already set. Use change PIN instead.',
            ], 422);
        }

        $user->update([
            'transaction_pin' => Hash::make($request->pin),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction PIN set successfully.',
        ]);
    }

    /**
     * Change transaction PIN.
     */
    public function changeTransactionPin(ChangeTransactionPinRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'No transaction PIN set. Use set PIN instead.',
            ], 422);
        }

        if (! Hash::check($request->current_pin, $user->transaction_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Current PIN is incorrect.',
            ], 422);
        }

        $user->update([
            'transaction_pin' => Hash::make($request->new_pin),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction PIN changed successfully.',
        ]);
    }

    /**
     * Send forgot password email.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send password reset link.',
        ], 422);
    }

    /**
     * Reset password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired reset token.',
        ], 422);
    }

    /**
     * Verify email address.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string',
            'hash' => 'required|string',
        ]);

        $user = User::where('uuid', $request->id)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if (! hash_equals(sha1($user->email), $request->hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link.',
            ], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
            ]);
        }

        $user->markEmailAsVerified();
        $user->update(['is_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ]);
    }

    /**
     * Resend verification email.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.',
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent.',
        ]);
    }

    /**
     * Enable two-factor authentication.
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        // This would integrate with a 2FA package like Laravel Fortify
        // For now, returning a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication setup initiated.',
            'data' => [
                'qr_code' => 'QR code would be generated here',
                'secret' => 'Secret would be here',
            ],
        ]);
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication disabled.',
        ]);
    }

    /**
     * Verify two-factor code.
     */
    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('uuid', $request->user_id)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Verify the 2FA code here (would use a 2FA package)
        // For now, just create a token

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Two-factor verification successful.',
            'data' => [
                'user' => $this->getUserData($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Get formatted user data.
     */
    private function getUserData(User $user): array
    {
        $user->load(['accounts' => function ($query) {
            $query->where('is_active', true)->with('accountType');
        }]);

        return [
            'uuid' => $user->uuid,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
            'address' => [
                'line_1' => $user->address_line_1,
                'line_2' => $user->address_line_2,
                'city' => $user->city,
                'state' => $user->state,
                'postal_code' => $user->postal_code,
                'country' => $user->country,
            ],
            'profile_photo' => $user->profile_photo_path,
            'is_verified' => $user->is_verified,
            'kyc_level' => $user->kyc_level,
            'has_transaction_pin' => (bool) $user->transaction_pin,
            'has_2fa' => (bool) $user->two_factor_secret,
            'accounts' => $user->accounts->map(fn ($account) => [
                'uuid' => $account->uuid,
                'account_number' => $account->account_number,
                'type' => $account->accountType->name,
                'balance' => $account->balance / 100,
                'balance_formatted' => '$'.number_format($account->balance / 100, 2),
                'currency' => $account->currency,
                'is_primary' => $account->is_primary,
            ]),
            'last_login_at' => $user->last_login_at?->toIso8601String(),
        ];
    }

    /**
     * Generate unique account number.
     */
    private function generateAccountNumber(): string
    {
        do {
            $number = '10'.rand(10000000, 99999999);
        } while (BankAccount::where('account_number', $number)->exists());

        return $number;
    }
}
