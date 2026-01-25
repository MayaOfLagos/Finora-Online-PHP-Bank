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
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount'); // Amount in cents
            $table->date('payment_date');
            $table->string('currency')->default('USD');
            $table->string('payment_method')->nullable(); // manual, crypto, card, etc.
            $table->string('payment_type')->nullable(); // repayment type (manual/crypto)
            $table->string('asset')->nullable();
            $table->string('tx_hash')->nullable();
            $table->decimal('exchange_rate', 20, 8)->nullable();
            $table->string('reference_number')->unique();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed, reversed, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('loan_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
