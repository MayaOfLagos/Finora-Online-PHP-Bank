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
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // wire_transfer, domestic_transfer, internal_transfer, check_deposit, mobile_deposit, crypto_deposit
            $table->string('reference_number')->unique();
            $table->morphs('transactionable'); // Polymorphic relation to actual transaction
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'reversed'])->default('pending');
            $table->text('description')->nullable();
            $table->string('generated_by')->nullable(); // admin user ID
            $table->boolean('email_sent')->default(false);
            $table->boolean('wallet_debited')->default(false);
            $table->json('metadata')->nullable(); // Store additional data
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['transaction_type', 'status']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_histories');
    }
};
