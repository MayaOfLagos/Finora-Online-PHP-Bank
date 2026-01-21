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
        Schema::create('grant_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->bigInteger('amount'); // in cents
            $table->string('currency', 3)->default('USD');
            $table->json('eligibility_criteria')->nullable();
            $table->json('required_documents')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('max_recipients')->nullable();
            $table->string('status')->default('upcoming'); // open, closed, upcoming
            $table->timestamps();

            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grant_programs');
    }
};
