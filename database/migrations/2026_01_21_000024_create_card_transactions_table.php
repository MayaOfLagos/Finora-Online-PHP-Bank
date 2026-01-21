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
        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->string('merchant_name')->nullable();
            $table->string('merchant_category')->nullable();
            $table->bigInteger('amount'); // in cents
            $table->string('currency', 3);
            $table->string('type'); // purchase, atm, refund
            $table->string('status')->default('completed');
            $table->timestamp('transaction_at');
            $table->timestamps();

            $table->index(['card_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_transactions');
    }
};
