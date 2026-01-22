<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DepositStatus;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\CheckDeposit;
use App\Models\CryptoDeposit;
use App\Models\CryptoWallet;
use App\Models\MobileDeposit;
use App\Models\PaymentGateway;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    // ==================== CHECK DEPOSITS ====================

    public function checkIndex(Request $request): JsonResponse
    {
        $deposits = CheckDeposit::where('user_id', $request->user()->id)
            ->with('account')
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'deposits' => $deposits->items(),
                'pagination' => [
                    'current_page' => $deposits->currentPage(),
                    'last_page' => $deposits->lastPage(),
                    'per_page' => $deposits->perPage(),
                    'total' => $deposits->total(),
                ],
            ],
        ]);
    }

    public function checkStore(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:bank_accounts,uuid',
            'amount' => 'required|numeric|min:1|max:50000',
            'check_number' => 'required|string|max:50',
            'routing_number' => 'required|string|size:9',
            'account_number_on_check' => 'required|string|max:50',
            'front_image' => 'required|image|max:5120',
            'back_image' => 'required|image|max:5120',
        ]);

        $account = BankAccount::where('uuid', $request->account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid account.',
            ], 422);
        }

        // Store images
        $frontPath = $request->file('front_image')->store('check-deposits/front', 'public');
        $backPath = $request->file('back_image')->store('check-deposits/back', 'public');

        $deposit = CheckDeposit::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'account_id' => $account->id,
            'reference_number' => 'CHK'.strtoupper(Str::random(12)),
            'amount' => $request->amount * 100,
            'check_number' => $request->check_number,
            'routing_number' => $request->routing_number,
            'account_number_on_check' => $request->account_number_on_check,
            'front_image_path' => $frontPath,
            'back_image_path' => $backPath,
            'status' => DepositStatus::Pending,
            'hold_until' => now()->addDays(2),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check deposit submitted for review. Funds will be available after verification.',
            'data' => [
                'deposit' => $deposit,
            ],
        ], 201);
    }

    public function checkShow(Request $request, CheckDeposit $deposit): JsonResponse
    {
        if ($deposit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'deposit' => $deposit->load('account'),
            ],
        ]);
    }

    // ==================== MOBILE DEPOSITS (PAYMENT GATEWAYS) ====================

    public function gateways(): JsonResponse
    {
        $gateways = PaymentGateway::where('is_active', true)
            ->get()
            ->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'slug' => $g->slug,
                'description' => $g->description,
                'icon' => $g->icon,
                'min_amount' => $g->min_amount / 100,
                'max_amount' => $g->max_amount / 100,
                'fee_type' => $g->fee_type,
                'fee_value' => $g->fee_type === 'percentage' ? $g->fee_value : $g->fee_value / 100,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'gateways' => $gateways,
            ],
        ]);
    }

    public function mobileIndex(Request $request): JsonResponse
    {
        $deposits = MobileDeposit::where('user_id', $request->user()->id)
            ->with(['account', 'gateway'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'deposits' => $deposits->items(),
                'pagination' => [
                    'current_page' => $deposits->currentPage(),
                    'last_page' => $deposits->lastPage(),
                    'per_page' => $deposits->perPage(),
                    'total' => $deposits->total(),
                ],
            ],
        ]);
    }

    public function mobileInitiate(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:bank_accounts,uuid',
            'gateway_id' => 'required|exists:payment_gateways,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $account = BankAccount::where('uuid', $request->account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid account.',
            ], 422);
        }

        $gateway = PaymentGateway::where('id', $request->gateway_id)
            ->where('is_active', true)
            ->first();

        if (! $gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive payment gateway.',
            ], 422);
        }

        $amountInCents = $request->amount * 100;

        // Check limits
        if ($amountInCents < $gateway->min_amount || $amountInCents > $gateway->max_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Amount must be between $'.($gateway->min_amount / 100).' and $'.($gateway->max_amount / 100),
            ], 422);
        }

        // Calculate fee
        $fee = $gateway->fee_type === 'percentage'
            ? (int) ($amountInCents * $gateway->fee_value / 100)
            : $gateway->fee_value;

        $deposit = MobileDeposit::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'account_id' => $account->id,
            'payment_gateway_id' => $gateway->id,
            'reference_number' => 'MOB'.strtoupper(Str::random(12)),
            'amount' => $amountInCents,
            'fee' => $fee,
            'status' => DepositStatus::Pending,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deposit initiated. Please verify your transaction PIN.',
            'data' => [
                'deposit' => $deposit,
                'gateway' => $gateway->name,
                'fee' => $fee / 100,
                'total' => ($amountInCents + $fee) / 100,
                'next_step' => 'verify_pin',
            ],
        ], 201);
    }

    public function mobileVerifyPin(Request $request, MobileDeposit $deposit): JsonResponse
    {
        if ($deposit->user_id !== $request->user()->id) {
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

        // Generate payment URL based on gateway
        $gateway = $deposit->gateway;
        $paymentUrl = $this->generatePaymentUrl($gateway, $deposit);

        return response()->json([
            'success' => true,
            'message' => 'PIN verified. Redirecting to payment gateway.',
            'data' => [
                'deposit' => $deposit,
                'payment_url' => $paymentUrl,
                'next_step' => 'complete_payment',
            ],
        ]);
    }

    public function mobileConfirm(Request $request, MobileDeposit $deposit): JsonResponse
    {
        if ($deposit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'gateway_reference' => 'required|string',
        ]);

        // Verify payment with gateway (in production, this would call the gateway API)
        $deposit->update([
            'gateway_reference' => $request->gateway_reference,
            'status' => DepositStatus::Completed,
            'completed_at' => now(),
        ]);

        // Credit the account
        $deposit->account->increment('balance', $deposit->amount);

        return response()->json([
            'success' => true,
            'message' => 'Deposit completed successfully.',
            'data' => [
                'deposit' => $deposit->fresh(),
            ],
        ]);
    }

    public function mobileShow(Request $request, MobileDeposit $deposit): JsonResponse
    {
        if ($deposit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'deposit' => $deposit->load(['account', 'gateway']),
            ],
        ]);
    }

    // ==================== CRYPTO DEPOSITS ====================

    public function cryptoWallets(): JsonResponse
    {
        $wallets = CryptoWallet::whereHas('cryptocurrency', fn ($q) => $q->where('is_active', true))
            ->where('is_active', true)
            ->with('cryptocurrency')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'currency' => $w->cryptocurrency->name,
                'symbol' => $w->cryptocurrency->symbol,
                'network' => $w->cryptocurrency->network,
                'address' => $w->wallet_address,
                'qr_code' => $w->qr_code_path,
                'min_deposit' => $w->min_deposit,
                'confirmations_required' => $w->confirmations_required,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'wallets' => $wallets,
            ],
        ]);
    }

    public function cryptoIndex(Request $request): JsonResponse
    {
        $deposits = CryptoDeposit::where('user_id', $request->user()->id)
            ->with(['account', 'cryptocurrency', 'wallet'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'deposits' => $deposits->items(),
                'pagination' => [
                    'current_page' => $deposits->currentPage(),
                    'last_page' => $deposits->lastPage(),
                    'per_page' => $deposits->perPage(),
                    'total' => $deposits->total(),
                ],
            ],
        ]);
    }

    public function cryptoInitiate(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:bank_accounts,uuid',
            'wallet_id' => 'required|exists:crypto_wallets,id',
            'amount' => 'required|numeric|min:0.00001',
            'transaction_hash' => 'required|string|max:255',
        ]);

        $account = BankAccount::where('uuid', $request->account_id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid account.',
            ], 422);
        }

        $wallet = CryptoWallet::where('id', $request->wallet_id)
            ->where('is_active', true)
            ->first();

        if (! $wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive crypto wallet.',
            ], 422);
        }

        $deposit = CryptoDeposit::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user()->id,
            'account_id' => $account->id,
            'cryptocurrency_id' => $wallet->cryptocurrency_id,
            'crypto_wallet_id' => $wallet->id,
            'reference_number' => 'CRY'.strtoupper(Str::random(12)),
            'crypto_amount' => $request->amount,
            'transaction_hash' => $request->transaction_hash,
            'status' => DepositStatus::Pending,
            'confirmations' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Crypto deposit submitted. It will be credited after network confirmations.',
            'data' => [
                'deposit' => $deposit,
                'confirmations_required' => $wallet->confirmations_required,
            ],
        ], 201);
    }

    public function cryptoShow(Request $request, CryptoDeposit $deposit): JsonResponse
    {
        if ($deposit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'deposit' => $deposit->load(['account', 'cryptocurrency', 'wallet']),
            ],
        ]);
    }

    // ==================== HELPERS ====================

    private function generatePaymentUrl(PaymentGateway $gateway, MobileDeposit $deposit): string
    {
        // This would integrate with actual payment gateway APIs
        // For now, return a placeholder URL
        return match ($gateway->slug) {
            'stripe' => "https://checkout.stripe.com/pay/{$deposit->uuid}",
            'paypal' => "https://www.paypal.com/checkoutnow?token={$deposit->uuid}",
            'paystack' => "https://checkout.paystack.com/{$deposit->uuid}",
            'flutterwave' => "https://checkout.flutterwave.com/v3/hosted/pay/{$deposit->uuid}",
            'razorpay' => "https://razorpay.com/checkout/{$deposit->uuid}",
            default => config('app.url')."/payment/{$deposit->uuid}",
        };
    }
}
