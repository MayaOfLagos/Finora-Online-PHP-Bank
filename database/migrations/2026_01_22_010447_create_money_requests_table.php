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
        Schema::create('money_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('responder_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('reference_number')->unique();
            $table->bigInteger('amount'); // in cents
            $table->string('currency')->default('USD');
            $table->enum('status', ['pending', 'accepted', 'completed', 'rejected', 'cancelled', 'expired'])->default('pending');
            $table->text('reason')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('type')->default('personal');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['requester_id', 'status']);
            $table->index(['responder_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_requests');
    }
};
