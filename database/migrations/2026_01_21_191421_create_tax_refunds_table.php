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
        Schema::create('tax_refunds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->string('tax_year');
            $table->string('ssn_tin')->nullable();
            $table->string('filing_status')->nullable();
            $table->decimal('refund_amount', 15, 2);
            $table->string('currency')->default('USD');
            $table->string('irs_reference_number')->nullable();
            $table->enum('status', ['pending', 'processing', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['tax_year', 'status']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_refunds');
    }
};
