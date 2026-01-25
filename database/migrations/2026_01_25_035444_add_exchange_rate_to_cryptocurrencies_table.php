<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->decimal('exchange_rate_usd', 20, 8)->nullable()->after('network')->comment('Manual exchange rate (1 crypto = X USD)');
            $table->string('coingecko_id')->nullable()->after('exchange_rate_usd')->comment('CoinGecko API ID for live rates');
            $table->text('description')->nullable()->after('coingecko_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate_usd', 'coingecko_id', 'description']);
        });
    }
};
