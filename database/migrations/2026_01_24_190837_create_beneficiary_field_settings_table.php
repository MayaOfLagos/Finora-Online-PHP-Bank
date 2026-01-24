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
        Schema::create('beneficiary_field_settings', function (Blueprint $table) {
            $table->id();
            $table->string('field_key')->unique(); // e.g., 'beneficiary_name', 'swift_code'
            $table->string('field_label'); // Display label
            $table->boolean('is_enabled')->default(true); // Show/hide field
            $table->boolean('is_required')->default(true); // Required validation
            $table->string('field_type')->default('text'); // text, textarea, select
            $table->string('transfer_type')->default('wire'); // wire, domestic, both
            $table->integer('sort_order')->default(0); // Display order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_field_settings');
    }
};
