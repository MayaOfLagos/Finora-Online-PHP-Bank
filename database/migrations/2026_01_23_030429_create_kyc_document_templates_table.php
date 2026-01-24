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
        Schema::create('kyc_document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Passport", "National ID", "Driver's License"
            $table->string('slug')->unique(); // e.g., "passport", "national_id"
            $table->text('description')->nullable();
            $table->text('instructions')->nullable(); // Instructions for users
            $table->boolean('is_required')->default(true);
            $table->boolean('requires_front_image')->default(true);
            $table->boolean('requires_back_image')->default(false);
            $table->boolean('requires_selfie')->default(false);
            $table->boolean('requires_document_number')->default(true);
            $table->json('accepted_formats')->nullable(); // ['jpg', 'png', 'pdf']
            $table->integer('max_file_size')->default(5120); // in KB, default 5MB
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add template_id to kyc_verifications if not exists
        if (! Schema::hasColumn('kyc_verifications', 'template_id')) {
            Schema::table('kyc_verifications', function (Blueprint $table) {
                $table->foreignId('template_id')->nullable()->after('user_id')->constrained('kyc_document_templates')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('kyc_verifications', 'template_id')) {
            Schema::table('kyc_verifications', function (Blueprint $table) {
                $table->dropConstrainedForeignId('template_id');
            });
        }

        Schema::dropIfExists('kyc_document_templates');
    }
};
