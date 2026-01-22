<?php

namespace App\Providers;

use App\Models\AccountTransfer;
use App\Models\CheckDeposit;
use App\Models\CryptoDeposit;
use App\Models\DomesticTransfer;
use App\Models\InternalTransfer;
use App\Models\MobileDeposit;
use App\Models\WireTransfer;
use App\Observers\AccountTransferObserver;
use App\Observers\CheckDepositObserver;
use App\Observers\CryptoDepositObserver;
use App\Observers\DomesticTransferObserver;
use App\Observers\InternalTransferObserver;
use App\Observers\MobileDepositObserver;
use App\Observers\WireTransferObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Transaction Observers
        WireTransfer::observe(WireTransferObserver::class);
        DomesticTransfer::observe(DomesticTransferObserver::class);
        InternalTransfer::observe(InternalTransferObserver::class);
        AccountTransfer::observe(AccountTransferObserver::class);
        
        // Register Deposit Observers
        CheckDeposit::observe(CheckDepositObserver::class);
        MobileDeposit::observe(MobileDepositObserver::class);
        CryptoDeposit::observe(CryptoDepositObserver::class);
    }
}
