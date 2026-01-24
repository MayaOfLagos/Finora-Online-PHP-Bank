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
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('user_id')->constrained('bank_accounts')->nullOnDelete();
            $table->enum('type', ['credit', 'debit'])->default('debit')->after('amount');
            $table->decimal('balance_after', 15, 2)->nullable()->after('type');

            $table->index('bank_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn(['bank_account_id', 'type', 'balance_after']);
        });
    }
};
