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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->bigInteger('amount'); // in cents
            $table->bigInteger('principal_portion'); // in cents
            $table->bigInteger('interest_portion'); // in cents
            $table->bigInteger('penalty_amount')->default(0); // in cents
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('status')->default('pending'); // pending, paid, overdue, partial
            $table->timestamps();

            $table->index(['loan_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
