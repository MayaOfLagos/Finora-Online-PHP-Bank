<?php

use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BeneficiaryController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\DepositController;
use App\Http\Controllers\Api\V1\LoanController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SupportController;
use App\Http\Controllers\Api\V1\TransferController;
use App\Http\Controllers\ExchangeMoneyController;
use App\Http\Controllers\MoneyRequestController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public Referral Routes (no auth required)
Route::prefix('referral')->group(function () {
    Route::get('/validate/{code}', [ReferralController::class, 'validateCode'])->name('api.referral.validate');
    Route::get('/info', [ReferralController::class, 'info'])->name('api.referral.info');
});

// API Version 1
Route::prefix('v1')->group(function () {
    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    });

    // Protected Routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth Routes
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
            Route::post('/set-transaction-pin', [AuthController::class, 'setTransactionPin']);
            Route::post('/change-transaction-pin', [AuthController::class, 'changeTransactionPin']);
            Route::post('/enable-2fa', [AuthController::class, 'enableTwoFactor']);
            Route::post('/disable-2fa', [AuthController::class, 'disableTwoFactor']);
            Route::post('/verify-2fa', [AuthController::class, 'verifyTwoFactor']);
        });

        // Account Routes
        Route::prefix('accounts')->group(function () {
            Route::get('/', [AccountController::class, 'index']);
            Route::get('/{account}', [AccountController::class, 'show']);
            Route::get('/{account}/transactions', [AccountController::class, 'transactions']);
            Route::get('/{account}/statement', [AccountController::class, 'statement']);
            Route::post('/{account}/set-primary', [AccountController::class, 'setPrimary']);
        });

        // Transfer Routes
        Route::prefix('transfers')->group(function () {
            // Wire Transfers (International)
            Route::prefix('wire')->group(function () {
                Route::get('/', [TransferController::class, 'wireIndex']);
                Route::post('/', [TransferController::class, 'wireInitiate']);
                Route::post('/{transfer}/verify-pin', [TransferController::class, 'verifyPin']);
                Route::post('/{transfer}/verify-imf', [TransferController::class, 'verifyImfCode']);
                Route::post('/{transfer}/verify-tax', [TransferController::class, 'verifyTaxCode']);
                Route::post('/{transfer}/verify-cot', [TransferController::class, 'verifyCotCode']);
                Route::post('/{transfer}/verify-otp', [TransferController::class, 'verifyOtp']);
                Route::get('/{transfer}', [TransferController::class, 'wireShow']);
            });

            // Internal Transfers
            Route::prefix('internal')->group(function () {
                Route::get('/', [TransferController::class, 'internalIndex']);
                Route::post('/', [TransferController::class, 'internalInitiate']);
                Route::post('/{transfer}/verify-pin', [TransferController::class, 'verifyInternalPin']);
                Route::post('/{transfer}/verify-otp', [TransferController::class, 'verifyInternalOtp']);
                Route::get('/{transfer}', [TransferController::class, 'internalShow']);
            });

            // Domestic Transfers
            Route::prefix('domestic')->group(function () {
                Route::get('/', [TransferController::class, 'domesticIndex']);
                Route::post('/', [TransferController::class, 'domesticInitiate']);
                Route::post('/{transfer}/verify-pin', [TransferController::class, 'verifyDomesticPin']);
                Route::post('/{transfer}/verify-otp', [TransferController::class, 'verifyDomesticOtp']);
                Route::get('/{transfer}', [TransferController::class, 'domesticShow']);
            });

            // Account-to-Account Transfers
            Route::prefix('a2a')->group(function () {
                Route::get('/', [TransferController::class, 'a2aIndex']);
                Route::post('/', [TransferController::class, 'a2aInitiate']);
                Route::post('/{transfer}/verify-pin', [TransferController::class, 'verifyA2aPin']);
                Route::post('/{transfer}/verify-otp', [TransferController::class, 'verifyA2aOtp']);
                Route::get('/{transfer}', [TransferController::class, 'a2aShow']);
            });
        });

        // Deposit Routes
        Route::prefix('deposits')->group(function () {
            // Check Deposits
            Route::prefix('check')->group(function () {
                Route::get('/', [DepositController::class, 'checkIndex']);
                Route::post('/', [DepositController::class, 'checkStore']);
                Route::get('/{deposit}', [DepositController::class, 'checkShow']);
            });

            // Mobile Deposits (Payment Gateway)
            Route::prefix('mobile')->group(function () {
                Route::get('/gateways', [DepositController::class, 'gateways']);
                Route::get('/', [DepositController::class, 'mobileIndex']);
                Route::post('/', [DepositController::class, 'mobileInitiate']);
                Route::post('/{deposit}/verify-pin', [DepositController::class, 'mobileVerifyPin']);
                Route::post('/{deposit}/confirm', [DepositController::class, 'mobileConfirm']);
                Route::get('/{deposit}', [DepositController::class, 'mobileShow']);
            });

            // Crypto Deposits
            Route::prefix('crypto')->group(function () {
                Route::get('/wallets', [DepositController::class, 'cryptoWallets']);
                Route::get('/', [DepositController::class, 'cryptoIndex']);
                Route::post('/', [DepositController::class, 'cryptoInitiate']);
                Route::get('/{deposit}', [DepositController::class, 'cryptoShow']);
            });
        });

        // Card Routes
        Route::prefix('cards')->group(function () {
            Route::get('/', [CardController::class, 'index']);
            Route::post('/', [CardController::class, 'store']);
            Route::get('/{card}', [CardController::class, 'show']);
            Route::post('/{card}/activate', [CardController::class, 'activate']);
            Route::post('/{card}/freeze', [CardController::class, 'freeze']);
            Route::post('/{card}/unfreeze', [CardController::class, 'unfreeze']);
            Route::post('/{card}/block', [CardController::class, 'block']);
            Route::put('/{card}/limits', [CardController::class, 'updateLimits']);
            Route::post('/{card}/change-pin', [CardController::class, 'changePin']);
            Route::get('/{card}/transactions', [CardController::class, 'transactions']);
        });

        // Loan Routes
        Route::prefix('loans')->group(function () {
            Route::get('/types', [LoanController::class, 'types']);
            Route::get('/calculator', [LoanController::class, 'calculator']);
            Route::get('/', [LoanController::class, 'index']);
            Route::post('/apply', [LoanController::class, 'apply']);
            Route::get('/applications', [LoanController::class, 'applications']);
            Route::get('/applications/{application}', [LoanController::class, 'showApplication']);
            Route::post('/applications/{application}/documents', [LoanController::class, 'uploadDocuments']);
            Route::get('/{loan}', [LoanController::class, 'show']);
            Route::get('/{loan}/schedule', [LoanController::class, 'repaymentSchedule']);
            Route::post('/{loan}/repay', [LoanController::class, 'makeRepayment']);
        });

        // Beneficiary Routes
        Route::prefix('beneficiaries')->group(function () {
            Route::get('/', [BeneficiaryController::class, 'index']);
            Route::post('/', [BeneficiaryController::class, 'store']);
            Route::get('/{beneficiary}', [BeneficiaryController::class, 'show']);
            Route::put('/{beneficiary}', [BeneficiaryController::class, 'update']);
            Route::delete('/{beneficiary}', [BeneficiaryController::class, 'destroy']);
        });

        // Notification Routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        });

        // Support Routes
        Route::prefix('support')->group(function () {
            Route::get('/categories', [SupportController::class, 'categories']);
            Route::get('/faq', [SupportController::class, 'faq']);
            Route::get('/knowledge-base', [SupportController::class, 'knowledgeBase']);
            Route::get('/knowledge-base/search', [SupportController::class, 'searchKnowledgeBase']);
            Route::get('/knowledge-base/{slug}', [SupportController::class, 'knowledgeBaseCategory']);
            Route::get('/knowledge-base/article/{slug}', [SupportController::class, 'knowledgeBaseArticle']);
            Route::get('/tickets', [SupportController::class, 'tickets']);
            Route::post('/tickets', [SupportController::class, 'createTicket']);
            Route::get('/tickets/{ticket}', [SupportController::class, 'showTicket']);
            Route::post('/tickets/{ticket}/reply', [SupportController::class, 'replyToTicket']);
            Route::post('/tickets/{ticket}/close', [SupportController::class, 'closeTicket']);
            Route::post('/tickets/{ticket}/rate', [SupportController::class, 'rateTicket']);
        });

        // Money Requests
        Route::prefix('money-requests')->group(function () {
            Route::get('/', [MoneyRequestController::class, 'index']);
            Route::post('/', [MoneyRequestController::class, 'store']);
            Route::post('/{id}/accept', [MoneyRequestController::class, 'accept']);
            Route::post('/{id}/reject', [MoneyRequestController::class, 'reject']);
            Route::delete('/{id}/cancel', [MoneyRequestController::class, 'cancel']);
        });

        // Withdrawals
        Route::prefix('withdrawals')->group(function () {
            Route::get('/', [WithdrawalController::class, 'index']);
            Route::post('/', [WithdrawalController::class, 'store']);
        });

        // Exchange Money
        Route::prefix('exchange-money')->group(function () {
            Route::get('/', [ExchangeMoneyController::class, 'index']);
            Route::post('/rate', [ExchangeMoneyController::class, 'getRate']);
            Route::post('/', [ExchangeMoneyController::class, 'store']);
        });

        // Vouchers
        Route::prefix('vouchers')->group(function () {
            Route::get('/', [VoucherController::class, 'index']);
            Route::post('/redeem', [VoucherController::class, 'redeem']);
        });

        // Rewards
        Route::prefix('rewards')->group(function () {
            Route::get('/', [RewardController::class, 'index']);
            Route::post('/redeem', [RewardController::class, 'redeem']);
        });
    });
});
