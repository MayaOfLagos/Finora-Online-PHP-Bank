<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PinVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\CardPageController;
use App\Http\Controllers\CheckDepositController;
use App\Http\Controllers\CryptoDepositController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DomesticTransferController;
use App\Http\Controllers\ExchangeMoneyController;
use App\Http\Controllers\GrantController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanRepaymentController;
use App\Http\Controllers\MobileDepositController;
use App\Http\Controllers\MoneyRequestController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WireTransferController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::controller(PublicPageController::class)->group(function () {
    Route::get('/about', '__invoke')->defaults('page', 'about')->name('public.about');
    Route::get('/services', '__invoke')->defaults('page', 'services')->name('public.services');
    Route::get('/personal-banking', '__invoke')->defaults('page', 'personal-banking')->name('public.personal-banking');
    Route::get('/business-banking', '__invoke')->defaults('page', 'business-banking')->name('public.business-banking');
    Route::get('/mobile-banking', '__invoke')->defaults('page', 'mobile-banking')->name('public.mobile-banking');
    Route::get('/loans-and-mortgages', '__invoke')->defaults('page', 'loans-and-mortgages')->name('public.loans');
    Route::get('/credit-cards', '__invoke')->defaults('page', 'credit-cards')->name('public.credit-cards');
    Route::get('/contact', '__invoke')->defaults('page', 'contact')->name('public.contact');
    Route::get('/careers', '__invoke')->defaults('page', 'careers')->name('public.careers');
    Route::get('/press', '__invoke')->defaults('page', 'press')->name('public.press');
    Route::get('/blog', '__invoke')->defaults('page', 'blog')->name('public.blog');
    Route::get('/help-center', '__invoke')->defaults('page', 'help-center')->name('public.help');
    Route::get('/faqs', '__invoke')->defaults('page', 'faqs')->name('public.faqs');
    Route::get('/security-center', '__invoke')->defaults('page', 'security-center')->name('public.security');
    Route::get('/report-fraud', '__invoke')->defaults('page', 'report-fraud')->name('public.report-fraud');
    Route::get('/atm-locator', '__invoke')->defaults('page', 'atm-locator')->name('public.atm-locator');
    Route::get('/fees', '__invoke')->defaults('page', 'fees')->name('public.fees');
    Route::get('/privacy-policy', '__invoke')->defaults('page', 'privacy-policy')->name('public.privacy');
    Route::get('/terms', '__invoke')->defaults('page', 'terms')->name('public.terms');
    Route::get('/cookie-policy', '__invoke')->defaults('page', 'cookie-policy')->name('public.cookies');
    Route::get('/accessibility', '__invoke')->defaults('page', 'accessibility')->name('public.accessibility');
});

// Guest routes (unauthenticated)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Password reset routes
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('email/verify', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Login Verification Routes (Email OTP & PIN)
Route::middleware('auth')->group(function () {
    // Email OTP Verification
    Route::get('verify-email-otp', [EmailOtpController::class, 'show'])
        ->name('verify-email-otp.show');
    
    Route::post('verify-email-otp', [EmailOtpController::class, 'verify'])
        ->name('verify-email-otp.verify');
    
    Route::post('verify-email-otp/send', [EmailOtpController::class, 'send'])
        ->name('verify-email-otp.send');
    
    // PIN Verification
    Route::get('verify-pin', [PinVerificationController::class, 'show'])
        ->name('verify-pin.show');
    
    Route::post('verify-pin', [PinVerificationController::class, 'verify'])
        ->name('verify-pin.verify');
});

// Logout (requires auth)
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'verified.email.otp', 'verified.pin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cards routes
    Route::get('/cards', [CardPageController::class, 'index'])->name('cards.index');
    Route::post('/cards', [CardPageController::class, 'store'])->name('cards.store');
    Route::get('/cards/{card}', [CardPageController::class, 'show'])->name('cards.show');
    Route::post('/cards/{card}/freeze', [CardPageController::class, 'toggleFreeze'])->name('cards.freeze');

    // Accounts routes
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
    Route::patch('/accounts/{account}/freeze', [AccountController::class, 'freeze'])->name('accounts.freeze');
    Route::patch('/accounts/{account}/unfreeze', [AccountController::class, 'unfreeze'])->name('accounts.unfreeze');
    Route::patch('/accounts/{account}/set-primary', [AccountController::class, 'setPrimary'])->name('accounts.set-primary');
    Route::get('/accounts/{account}/statement', [AccountController::class, 'downloadStatement'])->name('accounts.statement');

    // Transaction History routes
    Route::get('/transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionHistoryController::class, 'show'])->name('transactions.show');

    // Beneficiaries routes
    Route::get('/beneficiaries', [BeneficiaryController::class, 'index'])->name('beneficiaries.index');
    Route::post('/beneficiaries', [BeneficiaryController::class, 'store'])->name('beneficiaries.store');
    Route::patch('/beneficiaries/{beneficiary}', [BeneficiaryController::class, 'update'])->name('beneficiaries.update');
    Route::patch('/beneficiaries/{beneficiary}/toggle-favorite', [BeneficiaryController::class, 'toggleFavorite'])->name('beneficiaries.toggle-favorite');
    Route::delete('/beneficiaries/{beneficiary}', [BeneficiaryController::class, 'destroy'])->name('beneficiaries.destroy');

    // Transfers routes
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/internal', [TransferController::class, 'internal'])->name('transfers.internal');
    Route::post('/transfers/internal/request-otp', [TransferController::class, 'requestInternalOtp'])->name('transfers.internal.request-otp');
    Route::post('/transfers/internal', [TransferController::class, 'processInternal'])->name('transfers.internal.process');

    // Account Transfer routes
    Route::get('/transfers/account', [AccountTransferController::class, 'index'])->name('transfers.account');
    Route::post('/transfers/account', [AccountTransferController::class, 'process'])->name('transfers.account.process');

    // Wire Transfer routes
    Route::get('/transfers/wire', [WireTransferController::class, 'index'])->name('transfers.wire');
    Route::post('/transfers/wire/initiate', [WireTransferController::class, 'initiate'])->name('transfers.wire.initiate');
    Route::post('/transfers/wire/{wireTransfer}/verify-pin', [WireTransferController::class, 'verifyPin'])->name('transfers.wire.verify-pin');
    Route::post('/transfers/wire/{wireTransfer}/verify-code', [WireTransferController::class, 'verifyCode'])->name('transfers.wire.verify-code');
    Route::post('/transfers/wire/{wireTransfer}/request-otp', [WireTransferController::class, 'requestOtp'])->name('transfers.wire.request-otp');
    Route::post('/transfers/wire/{wireTransfer}/verify-otp', [WireTransferController::class, 'verifyOtp'])->name('transfers.wire.verify-otp');
    Route::post('/transfers/wire/{wireTransfer}/complete', [WireTransferController::class, 'completeTransfer'])->name('transfers.wire.complete');

    // Domestic Transfer routes
    Route::get('/transfers/domestic', [DomesticTransferController::class, 'index'])->name('transfers.domestic');
    Route::post('/transfers/domestic/initiate', [DomesticTransferController::class, 'initiate'])->name('transfers.domestic.initiate');
    Route::post('/transfers/domestic/{domesticTransfer}/verify-pin', [DomesticTransferController::class, 'verifyPin'])->name('transfers.domestic.verify-pin');
    Route::post('/transfers/domestic/{domesticTransfer}/request-otp', [DomesticTransferController::class, 'requestOtp'])->name('transfers.domestic.request-otp');
    Route::post('/transfers/domestic/{domesticTransfer}/verify-otp', [DomesticTransferController::class, 'verifyOtp'])->name('transfers.domestic.verify-otp');

    // Deposit routes
    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');

    // Mobile Deposit routes
    Route::get('/deposits/mobile', [MobileDepositController::class, 'index'])->name('deposits.mobile');
    Route::post('/deposits/mobile/initiate', [MobileDepositController::class, 'initiate'])->name('deposits.mobile.initiate');
    Route::post('/deposits/mobile/stripe/intent', [MobileDepositController::class, 'createStripeIntent'])->name('deposits.mobile.stripe.intent');
    Route::post('/deposits/mobile/flutterwave/link', [MobileDepositController::class, 'createFlutterwaveLink'])->name('deposits.mobile.flutterwave.link');
    Route::post('/deposits/mobile/razorpay/order', [MobileDepositController::class, 'createRazorpayOrder'])->name('deposits.mobile.razorpay.order');
    Route::post('/deposits/mobile/{mobileDeposit}/callback', [MobileDepositController::class, 'callback'])->name('deposits.mobile.callback');

    // Check Deposit routes
    Route::get('/deposits/check', [CheckDepositController::class, 'index'])->name('deposits.check');
    Route::post('/deposits/check/submit', [CheckDepositController::class, 'submit'])->name('deposits.check.submit');

    // Crypto Deposit routes
    Route::get('/deposits/crypto', [CryptoDepositController::class, 'index'])->name('deposits.crypto');
    Route::get('/deposits/crypto/{cryptocurrency}/wallet', [CryptoDepositController::class, 'getWallet'])->name('deposits.crypto.wallet');
    Route::get('/deposits/crypto/{cryptocurrency}/rate', [CryptoDepositController::class, 'getExchangeRate'])->name('deposits.crypto.rate');
    Route::post('/deposits/crypto/register', [CryptoDepositController::class, 'register'])->name('deposits.crypto.register');

    // Withdrawal routes
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals');
    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::post('/withdrawals/{withdrawal}/cancel', [WithdrawalController::class, 'cancel'])->name('withdrawals.cancel');

    // Loan routes
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/programs', [LoanController::class, 'programs'])->name('loans.programs');
    Route::get('/loans/applications', [LoanController::class, 'applications'])->name('loans.applications');
    Route::get('/loans/apply/{program}', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans/apply', [LoanController::class, 'store'])->name('loans.store');
    Route::post('/loans/{loan}/repay', [LoanRepaymentController::class, 'store'])->name('loans.repay');

    // Grant routes
    Route::get('/grants', [GrantController::class, 'programs'])->name('grants.programs');
    Route::get('/grants/applications', [GrantController::class, 'applications'])->name('grants.applications');
    Route::get('/grants/applications/{application}', [GrantController::class, 'show'])->name('grants.show');
    Route::get('/grants/{program}/apply', [GrantController::class, 'create'])->name('grants.create');
    Route::post('/grants/apply', [GrantController::class, 'store'])->name('grants.store');

    // Money Requests
    Route::get('/money-requests', [MoneyRequestController::class, 'index'])->name('money-requests.index');
    Route::post('/money-requests', [MoneyRequestController::class, 'store'])->name('money-requests.store');
    Route::post('/money-requests/{id}/accept', [MoneyRequestController::class, 'accept'])->name('money-requests.accept');
    Route::post('/money-requests/{id}/reject', [MoneyRequestController::class, 'reject'])->name('money-requests.reject');
    Route::delete('/money-requests/{id}', [MoneyRequestController::class, 'cancel'])->name('money-requests.cancel');

    // Exchange Money
    Route::get('/exchange', [ExchangeMoneyController::class, 'index'])->name('exchange.index');
    Route::post('/exchange/rate', [ExchangeMoneyController::class, 'getRate'])->name('exchange.rate');
    Route::post('/exchange', [ExchangeMoneyController::class, 'store'])->name('exchange.store');

    // Vouchers
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers/redeem', [VoucherController::class, 'redeem'])->name('vouchers.redeem');

    // Rewards
    Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
    Route::post('/rewards/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');

    // Tax Refunds
    Route::get('/tax-refunds', fn () => Inertia::render('TaxRefunds/Index'))->name('tax-refunds.index');

    // Support
    Route::get('/support', fn () => Inertia::render('Support/Index'))->name('support.index');

    // API-like routes for AJAX calls
    Route::get('/api/accounts/verify/{accountNumber}', [TransferController::class, 'verifyAccount'])->name('api.accounts.verify');

    // Impersonation routes
    Route::post('/admin/stop-impersonation', [ImpersonationController::class, 'stop'])->name('admin.stop-impersonation');
});
