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
        Schema::create('wire_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('restrict');
            $table->string('reference_number')->unique();

            // Beneficiary details
            $table->string('beneficiary_name');
            $table->string('beneficiary_account');
            $table->string('beneficiary_bank_name');
            $table->text('beneficiary_bank_address')->nullable();
            $table->string('swift_code');
            $table->string('routing_number')->nullable();

            // Amount details
            $table->bigInteger('amount'); // in cents
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 12, 6)->nullable();
            $table->bigInteger('fee')->default(0);
            $table->bigInteger('total_amount');

            // Transfer details
            $table->text('purpose')->nullable();
            $table->string('status')->default('pending');
            $table->string('current_step')->nullable();

            // Verification timestamps
            $table->timestamp('pin_verified_at')->nullable();
            $table->timestamp('imf_verified_at')->nullable();
            $table->timestamp('tax_verified_at')->nullable();
            $table->timestamp('cot_verified_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('failed_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wire_transfers');
    }
};
