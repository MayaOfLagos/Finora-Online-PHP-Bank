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
            // User preferences
            $table->string('theme', 20)->default('system')->after('profile_photo_path'); // light, dark, system
            $table->string('language', 5)->default('en')->after('theme');
            $table->string('currency_display', 20)->default('symbol')->after('language'); // symbol, code, name
            $table->string('date_format', 20)->default('M d, Y')->after('currency_display');
            $table->string('time_format', 5)->default('12h')->after('date_format'); // 12h, 24h
            $table->string('timezone', 50)->default('UTC')->after('time_format');
            
            // Notification preferences (denormalized for simplicity)
            $table->boolean('notify_email_transactions')->default(true)->after('timezone');
            $table->boolean('notify_email_security')->default(true)->after('notify_email_transactions');
            $table->boolean('notify_email_marketing')->default(false)->after('notify_email_security');
            $table->boolean('notify_push_transactions')->default(true)->after('notify_email_marketing');
            $table->boolean('notify_push_security')->default(true)->after('notify_push_transactions');
            $table->boolean('notify_sms_transactions')->default(false)->after('notify_push_security');
            $table->boolean('notify_sms_security')->default(true)->after('notify_sms_transactions');
            
            // Lockscreen settings
            $table->boolean('lockscreen_enabled')->default(false)->after('notify_sms_security');
            $table->integer('lockscreen_timeout')->default(5)->after('lockscreen_enabled'); // minutes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'theme',
                'language',
                'currency_display',
                'date_format',
                'time_format',
                'timezone',
                'notify_email_transactions',
                'notify_email_security',
                'notify_email_marketing',
                'notify_push_transactions',
                'notify_push_security',
                'notify_sms_transactions',
                'notify_sms_security',
                'lockscreen_enabled',
                'lockscreen_timeout',
            ]);
        });
    }
};
