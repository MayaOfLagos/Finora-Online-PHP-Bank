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
        Schema::table('domestic_transfers', function (Blueprint $table) {
            $table->string('otp_code', 255)->nullable()->after('status');
            $table->dateTime('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domestic_transfers', function (Blueprint $table) {
            $table->dropColumn(['otp_code', 'otp_expires_at']);
        });
    }
};
