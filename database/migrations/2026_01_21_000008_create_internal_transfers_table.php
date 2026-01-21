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
        Schema::create('internal_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_account_id')->constrained('bank_accounts')->onDelete('restrict');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_account_id')->constrained('bank_accounts')->onDelete('restrict');
            $table->string('reference_number')->unique();
            $table->bigInteger('amount'); // in cents
            $table->string('currency', 3);
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('pin_verified_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'status']);
            $table->index(['sender_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_transfers');
    }
};
