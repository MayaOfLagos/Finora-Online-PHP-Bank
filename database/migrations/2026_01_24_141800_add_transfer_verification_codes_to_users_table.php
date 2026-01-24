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
            // Wire transfer verification codes
            $table->string('imf_code')->nullable()->after('can_create_beneficiary');
            $table->string('tax_code')->nullable()->after('imf_code');
            $table->string('cot_code')->nullable()->after('tax_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['imf_code', 'tax_code', 'cot_code']);
        });
    }
};
