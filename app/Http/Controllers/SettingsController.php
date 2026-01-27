<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display user settings page
     */
    public function index()
    {
        $user = Auth::user();

        // User preferences pulled from columns
        $preferences = [
            'theme' => $user->theme ?? 'system',
            'language' => $user->language ?? 'en',
            'currency_display' => $user->currency_display ?? 'symbol',
            'date_format' => $user->date_format ?? 'M d, Y',
            'time_format' => $user->time_format ?? '12h',
            'timezone' => $user->timezone ?? 'UTC',
            'lockscreen_enabled' => $user->lockscreen_enabled ?? false,
            'lockscreen_timeout' => $user->lockscreen_timeout ?? 5,
        ];

        // Notification preferences
        $notifications = [
            'email_transactions' => $user->notify_email_transactions ?? true,
            'email_security' => $user->notify_email_security ?? true,
            'email_marketing' => $user->notify_email_marketing ?? false,
            'push_transactions' => $user->notify_push_transactions ?? true,
            'push_security' => $user->notify_push_security ?? true,
            'sms_transactions' => $user->notify_sms_transactions ?? false,
            'sms_security' => $user->notify_sms_security ?? true,
        ];

        // Available options
        $availableLanguages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'pt', 'name' => 'Portuguese'],
        ];

        $availableTimezones = [
            ['code' => 'UTC', 'name' => 'UTC (Coordinated Universal Time)'],
            ['code' => 'America/New_York', 'name' => 'Eastern Time (ET)'],
            ['code' => 'America/Chicago', 'name' => 'Central Time (CT)'],
            ['code' => 'America/Denver', 'name' => 'Mountain Time (MT)'],
            ['code' => 'America/Los_Angeles', 'name' => 'Pacific Time (PT)'],
            ['code' => 'Europe/London', 'name' => 'London (GMT)'],
            ['code' => 'Europe/Paris', 'name' => 'Paris (CET)'],
            ['code' => 'Asia/Tokyo', 'name' => 'Tokyo (JST)'],
            ['code' => 'Asia/Singapore', 'name' => 'Singapore (SGT)'],
            ['code' => 'Africa/Lagos', 'name' => 'Lagos (WAT)'],
        ];

        return Inertia::render('Settings/Index', [
            'preferences' => $preferences,
            'notifications' => $notifications,
            'availableLanguages' => $availableLanguages,
            'availableTimezones' => $availableTimezones,
        ]);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
            'language' => ['required', 'string', 'size:2'],
            'currency_display' => ['required', 'in:symbol,code,name'],
            'date_format' => ['required', 'string'],
            'time_format' => ['required', 'in:12h,24h'],
            'timezone' => ['required', 'string'],
            'lockscreen_enabled' => ['nullable', 'boolean'],
            'lockscreen_timeout' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        $user = Auth::user();

        $user->update([
            'theme' => $validated['theme'],
            'language' => $validated['language'],
            'currency_display' => $validated['currency_display'],
            'date_format' => $validated['date_format'],
            'time_format' => $validated['time_format'],
            'timezone' => $validated['timezone'],
            'lockscreen_enabled' => $validated['lockscreen_enabled'] ?? false,
            'lockscreen_timeout' => $validated['lockscreen_timeout'] ?? 5,
        ]);

        return back()->with('success', 'Preferences updated successfully.');
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_transactions' => ['boolean'],
            'email_security' => ['boolean'],
            'email_marketing' => ['boolean'],
            'push_transactions' => ['boolean'],
            'push_security' => ['boolean'],
            'sms_transactions' => ['boolean'],
            'sms_security' => ['boolean'],
        ]);

        $user = Auth::user();

        $user->update([
            'notify_email_transactions' => $validated['email_transactions'] ?? false,
            'notify_email_security' => $validated['email_security'] ?? false,
            'notify_email_marketing' => $validated['email_marketing'] ?? false,
            'notify_push_transactions' => $validated['push_transactions'] ?? false,
            'notify_push_security' => $validated['push_security'] ?? false,
            'notify_sms_transactions' => $validated['sms_transactions'] ?? false,
            'notify_sms_security' => $validated['sms_security'] ?? false,
        ]);

        return back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Export user data (GDPR compliance)
     */
    public function exportData()
    {
        $user = Auth::user();

        // Generate user data export
        $data = [
            'profile' => [
                'name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'address' => [
                    'line1' => $user->address_line_1,
                    'line2' => $user->address_line_2,
                    'city' => $user->city,
                    'state' => $user->state,
                    'postal_code' => $user->postal_code,
                    'country' => $user->country,
                ],
                'created_at' => $user->created_at,
            ],
            'accounts' => $user->bankAccounts()->get(['account_number', 'currency', 'balance', 'status', 'created_at']),
            'transactions' => $user->transactions()->latest()->take(100)->get(),
        ];

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="user_data_export.json"');
    }

    /**
     * Delete user account (soft delete)
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:DELETE'],
        ]);

        $user = Auth::user();

        // Check if user has any active accounts with balance
        $hasBalance = $user->bankAccounts()->where('balance', '>', 0)->exists();
        if ($hasBalance) {
            return back()->withErrors([
                'balance' => 'Please withdraw or transfer all funds before closing your account.',
            ]);
        }

        // Soft delete the user
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
