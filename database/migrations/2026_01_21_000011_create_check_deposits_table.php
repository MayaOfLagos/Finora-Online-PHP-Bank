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
        Schema::create('check_deposits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('restrict');
            $table->string('reference_number')->unique();
            $table->string('check_number')->nullable();
            $table->string('check_front_image');
            $table->string('check_back_image')->nullable();
            $table->bigInteger('amount'); // in cents
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('hold_until')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('check_deposits');
    }
};
