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
        Schema::table('tax_refunds', function (Blueprint $table) {
            // ID.me verification fields
            $table->boolean('idme_verified')->default(false);
            $table->string('idme_verification_id')->nullable();
            $table->timestamp('idme_verified_at')->nullable();
            $table->json('idme_verification_data')->nullable();

            // Additional tax information fields
            $table->string('employer_name')->nullable();
            $table->string('employer_ein')->nullable();
            $table->decimal('gross_income', 15, 2)->nullable();
            $table->decimal('federal_withheld', 15, 2)->nullable();
            $table->decimal('state_withheld', 15, 2)->nullable();
            $table->string('state')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_refunds', function (Blueprint $table) {
            $table->dropColumn([
                'idme_verified',
                'idme_verification_id',
                'idme_verified_at',
                'idme_verification_data',
                'employer_name',
                'employer_ein',
                'gross_income',
                'federal_withheld',
                'state_withheld',
                'state',
            ]);
        });
    }
};
