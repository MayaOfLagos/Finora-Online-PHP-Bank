<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display user profile page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get active tab from query param (default: personal)
        $activeTab = $request->query('tab', 'personal');

        // Get login history (recent sessions)
        $loginHistory = collect([
            [
                'device' => $this->getDeviceInfo($request),
                'ip' => $request->ip(),
                'location' => 'Current Session',
                'last_active' => now(),
                'is_current' => true,
            ],
        ]);

        return Inertia::render('Profile/Index', [
            'user' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'middle_name' => $user->middle_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'username' => $user->username,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'address_line_1' => $user->address_line_1,
                'address_line_2' => $user->address_line_2,
                'city' => $user->city,
                'state' => $user->state,
                'postal_code' => $user->postal_code,
                'country' => $user->country,
                'avatar_url' => $user->avatar_url,
                'kyc_level' => $user->kyc_level,
                'is_verified' => $user->is_verified,
                'two_factor_enabled' => $user->two_factor_enabled,
                'has_transaction_pin' => !empty($user->transaction_pin),
                'email_verified_at' => $user->email_verified_at?->format('M d, Y'),
                'created_at' => $user->created_at?->format('M d, Y'),
                'last_login_at' => $user->last_login_at?->format('M d, Y g:i A'),
            ],
            'activeTab' => $activeTab,
            'loginHistory' => $loginHistory,
            // Settings data (moved from SettingsController)
            'preferences' => [
                'theme' => 'system',
                'language' => 'en',
                'currency_display' => 'symbol',
                'date_format' => 'M d, Y',
                'time_format' => '12h',
                'timezone' => 'UTC',
            ],
            'notifications' => [
                'email_transactions' => true,
                'email_security' => true,
                'email_marketing' => false,
                'push_transactions' => true,
                'push_security' => true,
                'sms_transactions' => false,
                'sms_security' => true,
            ],
            'availableLanguages' => [
                ['code' => 'en', 'name' => 'English'],
                ['code' => 'es', 'name' => 'Spanish'],
                ['code' => 'fr', 'name' => 'French'],
                ['code' => 'de', 'name' => 'German'],
                ['code' => 'pt', 'name' => 'Portuguese'],
            ],
            'availableTimezones' => [
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
            ],
        ]);
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update user avatar/profile photo
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['profile_photo_path' => $path]);

        return back()->with('success', 'Profile photo updated successfully.');
    }

    /**
     * Remove user avatar
     */
    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->update(['profile_photo_path' => null]);

        return back()->with('success', 'Profile photo removed successfully.');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Update transaction PIN
     */
    public function updatePin(Request $request)
    {
        $validated = $request->validate([
            'current_pin' => ['required', 'string', 'size:4'],
            'pin' => ['required', 'string', 'size:4', 'confirmed'],
        ]);

        $user = Auth::user();

        // Verify current PIN
        if (!Hash::check($validated['current_pin'], $user->transaction_pin)) {
            return back()->withErrors(['current_pin' => 'Current PIN is incorrect.']);
        }

        $user->update([
            'transaction_pin' => Hash::make($validated['pin']),
        ]);

        return back()->with('success', 'Transaction PIN updated successfully.');
    }

    /**
     * Set transaction PIN (for users without one)
     */
    public function setPin(Request $request)
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'size:4', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!empty($user->transaction_pin)) {
            return back()->withErrors(['pin' => 'You already have a PIN set. Please use the change PIN feature.']);
        }

        $user->update([
            'transaction_pin' => Hash::make($validated['pin']),
        ]);

        return back()->with('success', 'Transaction PIN set successfully.');
    }

    /**
     * Toggle two-factor authentication
     */
    public function toggleTwoFactor(Request $request)
    {
        $user = Auth::user();
        
        $user->update([
            'two_factor_enabled' => !$user->two_factor_enabled,
        ]);

        $status = $user->two_factor_enabled ? 'enabled' : 'disabled';

        return back()->with('success', "Two-factor authentication {$status} successfully.");
    }

    /**
     * Get device info from request
     */
    private function getDeviceInfo(Request $request): string
    {
        $userAgent = $request->userAgent();
        
        // Simple browser detection
        if (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $browser = 'Edge';
        } else {
            $browser = 'Unknown';
        }

        // Simple OS detection
        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } else {
            $os = 'Unknown';
        }

        return "{$browser} on {$os}";
    }
}
