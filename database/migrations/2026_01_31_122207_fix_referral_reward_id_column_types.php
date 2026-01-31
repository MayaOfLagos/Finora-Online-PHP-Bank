<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix: The referrer_reward_id and referred_reward_id columns were created as INTEGER
     * but the rewards table uses UUID (VARCHAR) as primary key. This migration fixes the type mismatch.
     */
    public function up(): void
    {
        // SQLite doesn't support modifying columns directly, so we need to recreate
        if (DB::connection()->getDriverName() === 'sqlite') {
            // For SQLite, we'll use raw SQL to change column types
            DB::statement('CREATE TABLE referrals_temp AS SELECT * FROM referrals');
            DB::statement('DROP TABLE referrals');

            Schema::create('referrals', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
                $table->string('referral_code_used')->nullable()->index();
                $table->foreignId('referrer_level_id')->nullable()->constrained('referral_levels')->nullOnDelete();
                $table->string('referrer_reward_id', 36)->nullable(); // UUID
                $table->string('referred_reward_id', 36)->nullable(); // UUID
                $table->unsignedBigInteger('referrer_earned')->default(0);
                $table->unsignedBigInteger('referred_earned')->default(0);
                $table->string('status')->default('pending')->index();
                $table->unsignedBigInteger('reward_amount')->default(0);
                $table->string('reward_currency', 3)->default('USD');
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('rewarded_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['referrer_id', 'referred_id']);
            });

            // Copy data back (this will work since we're widening the column type)
            DB::statement('INSERT INTO referrals SELECT * FROM referrals_temp');
            DB::statement('DROP TABLE referrals_temp');
        } else {
            // For MySQL/PostgreSQL, we can modify columns directly
            Schema::table('referrals', function (Blueprint $table) {
                $table->string('referrer_reward_id', 36)->nullable()->change();
                $table->string('referred_reward_id', 36)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For SQLite, reverting would require similar table recreation
        // For simplicity, we'll leave the columns as VARCHAR (which is more flexible)
        // This is a one-way migration as it's fixing a bug
    }
};
