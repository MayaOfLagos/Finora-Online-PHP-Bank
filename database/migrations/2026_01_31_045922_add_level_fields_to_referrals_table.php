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
        Schema::table('referrals', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('referral_code_used')->nullable()->after('referred_id');
            $table->foreignId('referrer_level_id')->nullable()->after('referral_code_used')
                ->constrained('referral_levels')->nullOnDelete();
            $table->foreignId('referrer_reward_id')->nullable()->after('referrer_level_id');
            $table->foreignId('referred_reward_id')->nullable()->after('referrer_reward_id');
            $table->unsignedBigInteger('referrer_earned')->default(0)->after('referred_reward_id'); // cents
            $table->unsignedBigInteger('referred_earned')->default(0)->after('referrer_earned'); // cents

            // Update status column to use enum values
            $table->dropColumn('status');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('referred_earned'); // pending, completed, cancelled
            $table->index('status');
            $table->index('referral_code_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropForeign(['referrer_level_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['referral_code_used']);
            $table->dropColumn([
                'uuid',
                'referral_code_used',
                'referrer_level_id',
                'referrer_reward_id',
                'referred_reward_id',
                'referrer_earned',
                'referred_earned',
            ]);
        });
    }
};
