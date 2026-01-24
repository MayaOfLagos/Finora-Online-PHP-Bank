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
        Schema::table('wire_transfers', function (Blueprint $table) {
            $table->string('beneficiary_country', 2)->nullable()->after('beneficiary_bank_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wire_transfers', function (Blueprint $table) {
            $table->dropColumn('beneficiary_country');
        });
    }
};
