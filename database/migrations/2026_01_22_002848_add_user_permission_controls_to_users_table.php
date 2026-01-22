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
            $table->boolean('can_transfer')->default(true)->after('two_factor_secret');
            $table->boolean('can_withdraw')->default(true)->after('can_transfer');
            $table->boolean('can_deposit')->default(true)->after('can_withdraw');
            $table->boolean('can_request_loan')->default(true)->after('can_deposit');
            $table->boolean('can_request_card')->default(true)->after('can_request_loan');
            $table->boolean('can_apply_grant')->default(true)->after('can_request_card');
            $table->boolean('can_send_wire_transfer')->default(true)->after('can_apply_grant');
            $table->boolean('can_send_internal_transfer')->default(true)->after('can_send_wire_transfer');
            $table->boolean('can_send_domestic_transfer')->default(true)->after('can_send_internal_transfer');
            $table->boolean('can_create_beneficiary')->default(true)->after('can_send_domestic_transfer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'can_transfer',
                'can_withdraw',
                'can_deposit',
                'can_request_loan',
                'can_request_card',
                'can_apply_grant',
                'can_send_wire_transfer',
                'can_send_internal_transfer',
                'can_send_domestic_transfer',
                'can_create_beneficiary',
            ]);
        });
    }
};
