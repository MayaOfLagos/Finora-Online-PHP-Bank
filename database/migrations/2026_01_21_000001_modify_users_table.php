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
            // Add UUID after id
            $table->uuid('uuid')->unique()->after('id');

            // Rename 'name' to 'first_name' and add 'last_name'
            $table->renameColumn('name', 'first_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->after('first_name');

            // Contact information
            $table->string('phone_number')->nullable()->after('email_verified_at');
            $table->date('date_of_birth')->nullable()->after('phone_number');

            // Address fields
            $table->string('address_line_1')->nullable()->after('date_of_birth');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city')->nullable()->after('address_line_2');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country')->nullable()->after('postal_code');

            // Profile
            $table->string('profile_photo_path')->nullable()->after('country');

            // Security
            $table->string('transaction_pin')->nullable()->after('profile_photo_path');

            // Status flags
            $table->boolean('is_active')->default(true)->after('transaction_pin');
            $table->boolean('is_verified')->default(false)->after('is_active');
            $table->tinyInteger('kyc_level')->default(1)->after('is_verified');

            // Login tracking
            $table->timestamp('last_login_at')->nullable()->after('kyc_level');
            $table->string('last_login_ip')->nullable()->after('last_login_at');

            // 2FA
            $table->text('two_factor_secret')->nullable()->after('remember_token');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');

            // Soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'last_name',
                'phone_number',
                'date_of_birth',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'postal_code',
                'country',
                'profile_photo_path',
                'transaction_pin',
                'is_active',
                'is_verified',
                'kyc_level',
                'last_login_at',
                'last_login_ip',
                'two_factor_secret',
                'two_factor_recovery_codes',
            ]);

            $table->dropSoftDeletes();
            $table->renameColumn('first_name', 'name');
        });
    }
};
