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
        Schema::create('rewards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->bigInteger('points')->default(0);
            $table->enum('type', ['referral', 'cashback', 'loyalty', 'bonus', 'achievement'])->default('loyalty');
            $table->enum('status', ['pending', 'earned', 'redeemed', 'expired'])->default('pending');
            $table->date('earned_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->text('source')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['earned_date', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
