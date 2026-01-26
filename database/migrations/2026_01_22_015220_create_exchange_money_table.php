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
        Schema::create('exchange_money', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->string('reference_number', 50)->unique();
            $table->string('from_currency', 10);
            $table->string('to_currency', 10);
            $table->bigInteger('from_amount'); // in cents
            $table->bigInteger('to_amount'); // in cents
            $table->decimal('exchange_rate', 18, 8);
            $table->bigInteger('fee')->default(0); // in cents
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('from_currency');
            $table->index('to_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_money');
    }
};
