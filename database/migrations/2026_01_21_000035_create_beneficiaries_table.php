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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('beneficiary_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('beneficiary_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->string('nickname');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->bigInteger('transfer_limit')->nullable(); // in cents
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'beneficiary_account_id']);
            $table->index(['user_id', 'is_favorite']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
