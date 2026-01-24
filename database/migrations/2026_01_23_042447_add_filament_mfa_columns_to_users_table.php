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
        Schema::table('users', function (Blueprint $table) {
            // Filament v5 App Authentication (Google Authenticator)
            $table->text('app_authentication_secret')->nullable()->after('two_factor_secret');

            // Filament v5 App Authentication Recovery Codes
            $table->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret');

            // Filament v5 Email Authentication
            $table->boolean('has_email_authentication')->default(false)->after('app_authentication_recovery_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'app_authentication_secret',
                'app_authentication_recovery_codes',
                'has_email_authentication',
            ]);
        });
    }
};
