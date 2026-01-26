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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('skip_email_otp')->default(false)->after('skip_transfer_otp');
            $table->timestamp('email_otp_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('pin_verified_at')->nullable()->after('transaction_pin');
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'skip_email_otp',
                'email_otp_verified_at',
                'pin_verified_at',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
