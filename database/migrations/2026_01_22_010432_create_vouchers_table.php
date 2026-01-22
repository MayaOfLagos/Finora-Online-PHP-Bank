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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->bigInteger('amount'); // in cents
            $table->string('currency')->default('USD');
            $table->enum('type', ['discount', 'cashback', 'bonus', 'referral'])->default('bonus');
            $table->enum('status', ['active', 'used', 'expired', 'cancelled'])->default('active');
            $table->integer('usage_limit')->default(1);
            $table->integer('times_used')->default(0);
            $table->boolean('is_used')->default(false);
            $table->date('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'status']);
            $table->index(['user_id', 'is_used']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
