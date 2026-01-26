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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('restrict');
            $table->foreignId('card_type_id')->constrained()->onDelete('restrict');
            $table->string('card_number'); // encrypted
            $table->string('card_holder_name');
            $table->string('expiry_month'); // encrypted
            $table->string('expiry_year'); // encrypted
            $table->string('cvv'); // encrypted
            $table->string('pin')->nullable(); // hashed
            $table->bigInteger('spending_limit')->nullable(); // in cents
            $table->bigInteger('daily_limit')->nullable(); // in cents
            $table->string('status')->default('active'); // active, frozen, blocked, expired
            $table->boolean('is_virtual')->default(false);
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
