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
        Schema::create('beneficiary_field_templates', function (Blueprint $table) {
            $table->id();
            $table->string('field_key');
            $table->string('field_label');
            $table->enum('field_type', ['text', 'textarea', 'select', 'country'])->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->enum('applies_to', ['wire', 'domestic', 'internal', 'all'])->default('all');
            $table->json('options')->nullable();
            $table->integer('display_order')->default(0);
            $table->string('placeholder')->nullable();
            $table->string('helper_text')->nullable();
            $table->timestamps();

            $table->unique(['field_key', 'applies_to']);
            $table->index(['applies_to', 'is_enabled', 'display_order'], 'bft_applies_enabled_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_field_templates');
    }
};
