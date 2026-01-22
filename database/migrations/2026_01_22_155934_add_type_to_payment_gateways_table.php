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
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->string('type')->default('automatic')->after('code'); // automatic, manual, crypto
            $table->text('description')->nullable()->after('logo');
            $table->string('currency')->nullable()->after('description');
            $table->json('settings')->nullable()->after('credentials'); // Additional settings per type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'currency', 'settings']);
        });
    }
};
