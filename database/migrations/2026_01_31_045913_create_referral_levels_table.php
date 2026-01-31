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
        Schema::create('referral_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('level')->unique(); // 1, 2, 3...
            $table->string('name'); // Starter, Bronze, Silver, Gold, Platinum
            $table->string('reward_type')->default('fixed'); // fixed, percentage
            $table->unsignedBigInteger('reward_amount')->default(500); // Amount in cents OR percentage * 100
            $table->unsignedInteger('min_referrals_required')->default(0);
            $table->string('color')->nullable(); // Hex color for UI
            $table->string('icon')->nullable(); // Heroicon name
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('level');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_levels');
    }
};
