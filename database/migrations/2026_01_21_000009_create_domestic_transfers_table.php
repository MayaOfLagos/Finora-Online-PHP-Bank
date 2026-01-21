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
        Schema::create('domestic_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('restrict');
            $table->foreignId('bank_id')->constrained()->onDelete('restrict');
            $table->string('reference_number')->unique();
            $table->string('beneficiary_name');
            $table->string('beneficiary_account');
            $table->bigInteger('amount'); // in cents
            $table->string('currency', 3);
            $table->bigInteger('fee')->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('pin_verified_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
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
        Schema::dropIfExists('domestic_transfers');
    }
};
