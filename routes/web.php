<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomesticTransferController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WireTransferController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Guest routes (unauthenticated)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

// Logout (requires auth)
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // Wire Transfer routes
    Route::get('/transfers/wire', [WireTransferController::class, 'index'])->name('transfers.wire');
    Route::post('/transfers/wire/initiate', [WireTransferController::class, 'initiate'])->name('transfers.wire.initiate');
    Route::post('/transfers/wire/{wireTransfer}/verify-pin', [WireTransferController::class, 'verifyPin'])->name('transfers.wire.verify-pin');
    Route::post('/transfers/wire/{wireTransfer}/verify-code', [WireTransferController::class, 'verifyCode'])->name('transfers.wire.verify-code');
    Route::post('/transfers/wire/{wireTransfer}/request-otp', [WireTransferController::class, 'requestOtp'])->name('transfers.wire.request-otp');
    Route::post('/transfers/wire/{wireTransfer}/verify-otp', [WireTransferController::class, 'verifyOtp'])->name('transfers.wire.verify-otp');

    // Domestic Transfer routes
    Route::get('/transfers/domestic', [DomesticTransferController::class, 'index'])->name('transfers.domestic');
    Route::post('/transfers/domestic/initiate', [DomesticTransferController::class, 'initiate'])->name('transfers.domestic.initiate');
    Route::post('/transfers/domestic/{domesticTransfer}/verify-pin', [DomesticTransferController::class, 'verifyPin'])->name('transfers.domestic.verify-pin');
    Route::post('/transfers/domestic/{domesticTransfer}/request-otp', [DomesticTransferController::class, 'requestOtp'])->name('transfers.domestic.request-otp');
    Route::post('/transfers/domestic/{domesticTransfer}/verify-otp', [DomesticTransferController::class, 'verifyOtp'])->name('transfers.domestic.verify-otp');

    // API-like routes for AJAX calls
    Route::get('/api/accounts/verify/{accountNumber}', [TransferController::class, 'verifyAccount'])->name('api.accounts.verify');

    // Impersonation routes
    Route::post('/admin/stop-impersonation', [ImpersonationController::class, 'stop'])->name('admin.stop-impersonation');
});
