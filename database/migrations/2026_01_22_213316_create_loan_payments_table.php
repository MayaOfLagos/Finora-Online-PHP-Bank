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
            $table->unsignedBigInteger('amount'); // Amount in cents
            $table->date('payment_date');
            $table->string('payment_method')->nullable(); // cash, bank_transfer, card, etc.
            $table->string('reference_number')->unique();
            $table->text('notes')->nullable();
            $table->string('status')->default('completed'); // pending, completed, failed, reversed
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
