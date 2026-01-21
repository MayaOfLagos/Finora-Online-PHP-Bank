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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('loan_application_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained()->onDelete('restrict');
            $table->bigInteger('principal_amount'); // in cents
            $table->bigInteger('outstanding_balance'); // in cents
            $table->decimal('interest_rate', 5, 2);
            $table->bigInteger('monthly_payment'); // in cents
            $table->date('next_payment_date');
            $table->date('final_payment_date');
            $table->string('status')->default('active'); // active, closed, defaulted
            $table->timestamp('disbursed_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
