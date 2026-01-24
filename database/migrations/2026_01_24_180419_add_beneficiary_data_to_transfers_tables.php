<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add beneficiary_data JSON column to wire_transfers
        Schema::table('wire_transfers', function (Blueprint $table) {
            $table->json('beneficiary_data')->nullable()->after('beneficiary_country');
        });

        // Add beneficiary_data JSON column to domestic_transfers
        Schema::table('domestic_transfers', function (Blueprint $table) {
            $table->json('beneficiary_data')->nullable()->after('beneficiary_account');
        });

        // Add beneficiary_data JSON column to internal_transfers
        Schema::table('internal_transfers', function (Blueprint $table) {
            $table->json('beneficiary_data')->nullable()->after('receiver_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('wire_transfers', function (Blueprint $table) {
            $table->dropColumn('beneficiary_data');
        });

        Schema::table('domestic_transfers', function (Blueprint $table) {
            $table->dropColumn('beneficiary_data');
        });

        Schema::table('internal_transfers', function (Blueprint $table) {
            $table->dropColumn('beneficiary_data');
        });
    }
};
