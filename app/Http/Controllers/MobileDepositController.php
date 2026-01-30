<?php

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Enums\PaymentGatewayType;
use App\Models\BankAccount;
use App\Models\MobileDeposit;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class MobileDepositController extends Controller
{
    /**
     * Display mobile deposit page
     */
    public function index()
    {
        $user = Auth::user();

        // Check if user has permission to make deposits
        if (! $user->can_deposit) {
            return back()->withErrors(['general' => 'You do not have permission to make deposits.']);
        }

        // Get user's active bank accounts
        $accounts = $user->bankAccounts()
            ->where('is_active', true)
            ->with('accountType')
            ->get();

        // Get active AUTOMATIC payment gateways
        $automaticGateways = PaymentGateway::where('is_active', true)
            ->where('type', PaymentGatewayType::AUTOMATIC->value)
            ->orderBy('name')
            ->get();

        $enabledGateways = $automaticGateways->map(fn ($gateway) => [
            'value' => $gateway->code,
            'label' => $gateway->name,
            'logo' => $gateway->logo,
            'type' => 'automatic',
            'public_key' => $gateway->credentials['public_key']
                ?? $gateway->credentials['publishable_key']
                ?? $gateway->credentials['client_id']
                ?? null,
        ])->toArray();

        // Get active MANUAL payment gateways
        $manualGateways = PaymentGateway::where('is_active', true)
            ->where('type', PaymentGatewayType::MANUAL->value)
            ->orderBy('name')
            ->get();

        $manualMethods = $manualGateways->map(fn ($gateway) => [
            'value' => $gateway->code,
            'label' => $gateway->name,
            'logo' => $gateway->logo,
            'type' => 'manual',
            'description' => $gateway->description,
            'instructions' => $gateway->settings ?? [],
        ])->toArray();

        // Merge automatic and manual gateways
        $allGateways = array_merge($enabledGateways, $manualMethods);

        // Get deposit limits
        $depositLimits = [
            'daily' => (int) Setting::getValue('deposits', 'mobile_daily_limit', 10000) * 100,
            'perTransaction' => (int) Setting::getValue('deposits', 'mobile_per_transaction_limit', 5000) * 100,
        ];

        // Get today's mobile deposits total
        $todaysTotal = $user->mobileDeposits()
            ->where('created_at', '>=', now()->startOfDay())
            ->where('status', DepositStatus::Completed)
            ->sum('amount');

        return Inertia::render('Deposits/Mobile', [
            'accounts' => $accounts,
            'enabledGateways' => $allGateways,
            'depositLimits' => $depositLimits,
            'todaysTotal' => $todaysTotal,
        ]);
    }

    /**
     * Initiate mobile deposit
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'gateway' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();

        // Check if user has permission to make deposits
        if (! $user->can_deposit) {
            return back()->withErrors(['general' => 'You do not have permission to make deposits.']);
        }

        // Verify account ownership
        $account = BankAccount::where('id', $validated['bank_account_id'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Check gateway is available and active
        $gatewayModel = PaymentGateway::where('code', $validated['gateway'])
            ->where('is_active', true)
            ->whereIn('type', [PaymentGatewayType::AUTOMATIC->value, PaymentGatewayType::MANUAL->value])
            ->firstOrFail();

        // Check deposit limits
        $dailyLimit = (int) Setting::getValue('deposits', 'mobile_daily_limit', 10000) * 100;
        $perTransactionLimit = (int) Setting::getValue('deposits', 'mobile_per_transaction_limit', 5000) * 100;

        $amountInCents = (int) ($validated['amount'] * 100);

        if ($amountInCents > $perTransactionLimit) {
            return back()->withErrors(['amount' => 'Amount exceeds per-transaction limit']);
        }

        $todaysTotal = $user->mobileDeposits()
            ->where('created_at', '>=', now()->startOfDay())
            ->where('status', DepositStatus::Completed)
            ->sum('amount');

        if ($todaysTotal + $amountInCents > $dailyLimit) {
            return back()->withErrors(['amount' => 'Daily deposit limit exceeded']);
        }

        // Create mobile deposit record
        $deposit = MobileDeposit::create([
            'user_id' => $user->id,
            'bank_account_id' => $account->id,
            'gateway' => $gatewayModel->code,
            'amount' => $amountInCents,
            'currency' => $account->currency,
            'fee' => 0, // Will be set by gateway
            'status' => DepositStatus::Pending,
        ]);

        // Send confirmation email (non-blocking on failure)
        try {
            Mail::to($user->email)->send(
                new \App\Mail\DepositNotificationMail($deposit, $user, 'mobile')
            );
        } catch (\Throwable $e) {
            // Email sending failed, but continue
        }

        $publicKey = $gatewayModel->credentials['public_key']
            ?? $gatewayModel->credentials['publishable_key']
            ?? $gatewayModel->credentials['client_id']
            ?? null;

        // Generate gateway session or redirect URL based on gateway type
        // Return deposit details for frontend to handle payment initiation
        $responseData = [
            'success' => 'Deposit initiated',
            'deposit' => [
                'uuid' => $deposit->uuid,
                'reference' => $deposit->reference_number,
                'amount' => $validated['amount'],
                'gateway' => $gatewayModel->code,
                'gateway_public_key' => $publicKey,
                'gateway_name' => $gatewayModel->name,
            ],
        ];

        return back()->with($responseData);
    }

    /**
     * Handle gateway callback
     */
    public function callback(Request $request, MobileDeposit $deposit)
    {
        $user = Auth::user();

        // Verify ownership
        if ($deposit->user_id !== $user->id) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Unauthorized'], 403)
                : back()->withErrors(['general' => 'Unauthorized']);
        }

        $gatewayReference = $request->input('gateway_reference');
        if (! $gatewayReference) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Missing gateway reference'], 422)
                : back()->withErrors(['general' => 'Missing gateway reference']);
        }

        $deposit->update([
            'gateway_transaction_id' => $gatewayReference,
            'status' => DepositStatus::Completed,
            'completed_at' => now(),
        ]);

        // Credit the bank account balance
        $deposit->bankAccount?->increment('balance', $deposit->amount);

        // Notify admins about completed mobile deposit
        AdminNotificationService::mobileDepositCompleted($deposit, $user);

        $payload = [
            'success' => true,
            'message' => 'Payment completed successfully',
            'deposit' => [
                'uuid' => $deposit->uuid,
                'reference' => $deposit->reference_number,
                'amount' => $deposit->amount,
                'status' => $deposit->status->value,
            ],
        ];

        return $request->expectsJson()
            ? response()->json($payload)
            : back()->with($payload);
    }

    /**
     * Create Stripe PaymentIntent for mobile deposit
     */
    public function createStripeIntent(Request $request)
    {
        $validated = $request->validate([
            'deposit_uuid' => 'required|uuid|exists:mobile_deposits,uuid',
        ]);

        $user = Auth::user();

        // Get the deposit
        $deposit = MobileDeposit::where('uuid', $validated['deposit_uuid'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            // Get Stripe secret key
            $gateway = PaymentGateway::where('code', 'stripe')
                ->where('is_active', true)
                ->firstOrFail();

            $secretKey = $gateway->credentials['secret_key'] ?? null;
            if (! $secretKey) {
                throw new \Exception('Stripe configuration incomplete');
            }

            // Initialize Stripe
            \Stripe\Stripe::setApiKey($secretKey);

            // Create PaymentIntent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $deposit->amount, // Amount in cents
                'currency' => strtolower($deposit->currency),
                'description' => "Deposit to {$deposit->bankAccount?->account_type?->name}",
                'metadata' => [
                    'deposit_uuid' => $deposit->uuid,
                    'user_id' => $user->id,
                    'bank_account_id' => $deposit->bank_account_id,
                ],
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'intent_id' => $paymentIntent->id,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Illuminate\Support\Facades\Log::error('Stripe API Error', [
                'error' => $e->getMessage(),
                'deposit_uuid' => $validated['deposit_uuid'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize Stripe payment: '.$e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stripe Error', [
                'error' => $e->getMessage(),
                'deposit_uuid' => $validated['deposit_uuid'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment',
            ], 500);
        }
    }

    /**
     * Create Flutterwave PaymentLink for mobile deposit
     */
    public function createFlutterwaveLink(Request $request)
    {
        $validated = $request->validate([
            'deposit_uuid' => 'required|uuid|exists:mobile_deposits,uuid',
        ]);

        $user = Auth::user();

        $deposit = MobileDeposit::where('uuid', $validated['deposit_uuid'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $gateway = PaymentGateway::where('code', 'flutterwave')
                ->where('is_active', true)
                ->firstOrFail();

            $publicKey = $gateway->credentials['public_key'] ?? null;
            if (! $publicKey) {
                throw new \Exception('Flutterwave configuration incomplete');
            }

            // Create Flutterwave payment reference
            $flw_ref = 'FLW-'.$deposit->uuid;

            return response()->json([
                'success' => true,
                'public_key' => $publicKey,
                'tx_ref' => $flw_ref,
                'customer_email' => $user->email,
                'customer_name' => $user->first_name.' '.$user->last_name,
                'amount' => (int) ($deposit->amount / 100), // Convert cents to dollars
                'currency' => strtoupper($deposit->currency),
            ]);

        } catch (\Exception $e) {
            Log::error('Flutterwave Error', [
                'error' => $e->getMessage(),
                'deposit_uuid' => $validated['deposit_uuid'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize Flutterwave payment: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Create Razorpay Order for mobile deposit
     */
    public function createRazorpayOrder(Request $request)
    {
        $validated = $request->validate([
            'deposit_uuid' => 'required|uuid|exists:mobile_deposits,uuid',
        ]);

        $user = Auth::user();

        $deposit = MobileDeposit::where('uuid', $validated['deposit_uuid'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $gateway = PaymentGateway::where('code', 'razorpay')
                ->where('is_active', true)
                ->firstOrFail();

            $keyId = $gateway->credentials['key_id'] ?? null;
            $keySecret = $gateway->credentials['key_secret'] ?? null;

            if (! $keyId || ! $keySecret) {
                throw new \Exception('Razorpay configuration incomplete');
            }

            // Initialize Razorpay
            $razorpay = new \Razorpay\Api\Api($keyId, $keySecret);

            // Create Razorpay order
            $order = $razorpay->order->create([
                'amount' => $deposit->amount, // Amount in smallest unit (paise)
                'currency' => strtoupper($deposit->currency),
                'receipt' => $deposit->uuid,
                'notes' => [
                    'deposit_uuid' => $deposit->uuid,
                    'user_id' => $user->id,
                    'bank_account_id' => $deposit->bank_account_id,
                ],
            ]);

            return response()->json([
                'success' => true,
                'key_id' => $keyId,
                'order_id' => $order['id'],
                'amount' => $deposit->amount,
                'currency' => strtoupper($deposit->currency),
                'customer_email' => $user->email,
                'customer_name' => $user->first_name.' '.$user->last_name,
            ]);

        } catch (\Razorpay\Api\Errors\Error $e) {
            Log::error('Razorpay API Error', [
                'error' => $e->getMessage(),
                'deposit_uuid' => $validated['deposit_uuid'],
            ]);

            $errorMessage = $e->getMessage();

            // Provide helpful error messages for common issues
            if (str_contains($errorMessage, 'Authentication failed')) {
                $errorMessage = 'Razorpay authentication failed. Please check your API credentials in the admin panel.';
            } elseif (str_contains($errorMessage, 'Invalid')) {
                $errorMessage = 'Invalid Razorpay configuration. Please verify your API keys.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Razorpay Error', [
                'error' => $e->getMessage(),
                'deposit_uuid' => $validated['deposit_uuid'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment',
            ], 500);
        }
    }
}
