# System Settings Guide

This guide explains how to configure and manage system-wide settings for logos, branding, and copyright information in Finora Bank.

## Overview

The application uses a flexible settings system that allows administrators to configure branding elements (logos, copyright text) from the Filament admin panel. All frontend components automatically use these settings with intelligent fallbacks.

---

## Database Structure

### Settings Table

```sql
CREATE TABLE settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    `group` VARCHAR(255),
    `key` VARCHAR(255),
    value TEXT,
    type VARCHAR(255) DEFAULT 'string',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY (`group`, `key`),
    INDEX (`group`)
);
```

### Setting Groups

#### 1. **general** - General Application Settings
- `app_name` - Application name (default: "Finora Bank")
- `app_tagline` - Tagline (default: "Banking Made Simple")
- `support_email` - Support email address
- `support_phone` - Support phone number

#### 2. **branding** - Visual Branding Settings
- `logo_light` - Path to light theme logo image (empty = use fallback)
- `logo_dark` - Path to dark theme logo image (empty = use fallback)
- `favicon` - Path to favicon image
- `copyright_text` - Copyright holder name (default: "Finora Bank")
- `copyright_year` - Copyright year (default: current year)
- `footer_extra_text` - Additional footer text (default: "Member FDIC | Equal Housing Lender")

---

## How It Works

### 1. Settings Model (`app/Models/Setting.php`)

The Setting model provides convenient methods to get and set values:

```php
// Get a single setting
Setting::getValue('branding', 'logo_light', '/default-logo.png');

// Set a setting
Setting::setValue('branding', 'logo_light', '/images/logo-light.png', 'string');

// Get all settings in a group
Setting::getGroup('branding');
```

### 2. HandleInertiaRequests Middleware

Settings are automatically shared with all Inertia pages:

```php
'settings' => [
    'general' => [
        'app_name' => Setting::getValue('general', 'app_name', 'Finora Bank'),
        // ... other general settings
    ],
    'branding' => [
        'logo_light' => Setting::getValue('branding', 'logo_light', ''),
        'logo_dark' => Setting::getValue('branding', 'logo_dark', ''),
        // ... other branding settings
    ],
],
```

All Vue components can access these via `usePage().props.settings`.

---

## Frontend Components

### AppLogo Component

**Location:** `resources/js/Components/Common/AppLogo.vue`

**Purpose:** Displays the application logo with automatic theme switching and fallback.

**Usage:**
```vue
<AppLogo 
    :show-text="true" 
    size="lg" 
/>
```

**Props:**
- `showText` (Boolean, default: `true`) - Show app name alongside logo
- `size` (String, default: `'md'`) - Size: `'sm'`, `'md'`, `'lg'`
- `dark` (Boolean, default: `false`) - Force dark theme (usually auto-detected)

**Features:**
- Shows `logo_light` in light mode
- Shows `logo_dark` in dark mode (when available)
- Falls back to gradient circle with first letter of app name
- Automatically uses app name from settings
- Responsive sizing

**Example Output:**
- **With logo configured:** Displays uploaded logo image
- **Without logo:** Displays "F" in gradient circle + "Finora Bank" text

---

### CopyrightText Component

**Location:** `resources/js/Components/Common/CopyrightText.vue`

**Purpose:** Displays copyright information from system settings.

**Usage:**
```vue
<CopyrightText 
    :show-extra-text="true" 
    text-class="text-xs text-gray-500"
/>
```

**Props:**
- `showExtraText` (Boolean, default: `false`) - Show footer extra text (e.g., "Member FDIC")
- `textClass` (String) - Custom CSS classes for styling

**Output Examples:**

*Without extra text:*
```
© 2026 Finora Bank. All Rights Reserved.
```

*With extra text:*
```
© 2026 Finora Bank. All Rights Reserved.
Member FDIC | Equal Housing Lender
```

---

## Implementation in Pages

### Auth Pages (Login, Register, ForgotPassword, ResetPassword, VerifyEmail)

All auth pages now use the reusable components:

**Before (Hardcoded):**
```vue
<div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-primary-600 to-primary-700 rounded-2xl shadow-lg">
    <span class="text-2xl font-bold text-white">F</span>
</div>
<h1>Finora Bank</h1>

<!-- ... later in footer ... -->
<p>© 2026 Finora Bank. All Rights Reserved.</p>
```

**After (Dynamic with Settings):**
```vue
<AppLogo :show-text="true" size="lg" />

<!-- ... later in footer ... -->
<CopyrightText />
```

### Landing Pages (MainHeader, MainFooter)

```vue
<!-- MainHeader mobile menu footer -->
<CopyrightText :show-extra-text="true" />
```

---

## Configuration via Admin Panel

### Step 1: Access Filament Admin Panel

Navigate to `/admin` and login with admin credentials.

### Step 2: Go to Settings Resource

Navigate to **Settings** in the admin sidebar.

### Step 3: Configure Branding Settings

#### Upload Logos

1. Find the **branding** group
2. Edit `logo_light` - Upload light theme logo (PNG/SVG recommended)
3. Edit `logo_dark` - Upload dark theme logo (optional, but recommended)
4. Edit `favicon` - Upload favicon (ICO/PNG, 32x32 or 64x64)

**Logo Upload Guidelines:**
- **Format:** PNG with transparent background or SVG
- **Size:** Recommended height: 56-64px, width: auto
- **Light Logo:** Should work on white/light backgrounds
- **Dark Logo:** Should work on dark backgrounds (white/light colored)
- **Storage Path:** Store in `public/storage/branding/` or `public/images/`

#### Configure Copyright

1. Edit `copyright_text` - Company name (e.g., "Finora Bank")
2. Edit `copyright_year` - Current year (e.g., "2026")
3. Edit `footer_extra_text` - Legal text (e.g., "Member FDIC | Equal Housing Lender")

### Step 4: Clear Cache (if needed)

Settings are cached for performance. To see immediate changes:

```bash
php artisan cache:clear
```

---

## Fallback Behavior

### Logo Fallback Chain

1. **First Choice:** Display `logo_light` (light mode) or `logo_dark` (dark mode)
2. **Second Choice:** Display `logo_light` for both modes if `logo_dark` is empty
3. **Fallback:** Display gradient circle with first letter of `app_name`

### Copyright Fallback Chain

1. **First Choice:** Use `copyright_text` and `copyright_year` from settings
2. **Fallback:** Use "Finora Bank" and current year from `Date()`

---

## File Structure

```
finora-bank/
├── app/
│   ├── helpers.php                         # Global helper functions
│   ├── Http/Middleware/
│   │   └── HandleInertiaRequests.php      # Shares settings globally
│   └── Models/
│       └── Setting.php                     # Settings model with helper methods
├── database/
│   ├── migrations/
│   │   └── 2026_01_21_000038_create_settings_table.php
│   └── seeders/
│       └── SettingSeeder.php               # Seeds default settings
├── resources/
│   ├── js/
│   │   ├── Components/Common/
│   │   │   ├── AppLogo.vue                     # Reusable logo component
│   │   │   └── CopyrightText.vue               # Reusable copyright component
│   │   └── Pages/Auth/
│   │       ├── Login.vue                       # Uses AppLogo + CopyrightText
│   │       ├── Register.vue                    # Uses AppLogo + CopyrightText
│   │       ├── ForgotPassword.vue              # Uses AppLogo + CopyrightText
│   │       ├── ResetPassword.vue               # Uses AppLogo + CopyrightText
│   │       └── VerifyEmail.vue                 # Uses AppLogo + CopyrightText
│   └── views/
│       ├── emails/                         # Email templates using app_name()
│       │   ├── transfer-otp.blade.php
│       │   ├── wire-transfer-otp.blade.php
│       │   ├── bank-account-created.blade.php
│       │   ├── ticket-created.blade.php
│       │   ├── newsletter.blade.php
│       │   └── ... (all use dynamic settings)
│       └── pdf/
│           └── account-statement.blade.php # PDF using app_name()
└── .github/
    └── SYSTEM_SETTINGS_GUIDE.md            # This file
```

---

## Helper Functions

### Available Global Helpers

The application includes several global helper functions for easy access to settings:

```php
// Get any setting value
setting('general', 'app_name', 'Default Name');

// Get app name
app_name(); // Returns: "Finora Bank" or custom value

// Get copyright text with year
copyright_text(); // Returns: "© 2026 Finora Bank"
copyright_text(false); // Returns: "Finora Bank" (without year)

// Get support contact info
support_email(); // Returns: "support@finorabank.com" or custom
support_phone(); // Returns: "+1-800-FINORA" or custom
```

### Usage in Blade Templates

All email templates now use these helpers:

```blade
<x-mail::message>
# Welcome to {{ app_name() }}

Thanks,<br>
{{ app_name() }} Team

<x-slot:subcopy>
{{ copyright_text() }}. All rights reserved.
</x-slot:subcopy>
</x-mail::message>
```

### Usage in Controllers

```php
$emailData = [
    'app_name' => app_name(),
    'support_email' => support_email(),
    'copyright' => copyright_text(),
];
```

---

## Email Templates

### Updated Templates

All email templates now use dynamic settings instead of hardcoded values:

**Before:**
```blade
Thanks,<br>
{{ config('app.name') }} Team

<p>&copy; {{ date('Y') }} Finora Bank. All rights reserved.</p>
```

**After:**
```blade
Thanks,<br>
{{ app_name() }} Team

<p>{{ copyright_text() }}. All rights reserved.</p>
```

### Email Templates Using Settings

- ✅ `transfer-otp.blade.php` - Transfer verification codes
- ✅ `wire-transfer-otp.blade.php` - Wire transfer verification
- ✅ `bank-account-created.blade.php` - Account creation notifications
- ✅ `deposit-notification.blade.php` - Deposit confirmations
- ✅ `account-statement.blade.php` - Account statements
- ✅ `ticket-created.blade.php` - Support ticket notifications
- ✅ `ticket-replied.blade.php` - Ticket response notifications
- ✅ `ticket-status-changed.blade.php` - Ticket status updates
- ✅ `newsletter.blade.php` - Marketing emails
- ✅ `kyc/approved.blade.php` - KYC approval emails
- ✅ `kyc/rejected.blade.php` - KYC rejection emails
- ✅ `kyc/submitted.blade.php` - KYC submission confirmations
- ✅ `support/new-ticket-notification.blade.php` - Admin notifications
- ✅ `funds-adjusted.blade.php` - Balance adjustment notifications
- ✅ `push-notification.blade.php` - Push notification emails
- ✅ `admin-notification.blade.php` - System admin alerts

### PDF Templates

- ✅ `pdf/account-statement.blade.php` - Account statement PDFs

---

## File Structure

```
finora-bank/
├── app/
│   ├── Http/Middleware/
│   │   └── HandleInertiaRequests.php      # Shares settings globally
│   └── Models/
│       └── Setting.php                     # Settings model with helper methods
├── database/
│   ├── migrations/
│   │   └── 2026_01_21_000038_create_settings_table.php
│   └── seeders/
│       └── SettingSeeder.php               # Seeds default settings
├── resources/js/
│   ├── Components/Common/
│   │   ├── AppLogo.vue                     # Reusable logo component
│   │   └── CopyrightText.vue               # Reusable copyright component
│   └── Pages/Auth/
│       ├── Login.vue                       # Uses AppLogo + CopyrightText
│       ├── Register.vue                    # Uses AppLogo + CopyrightText
│       ├── ForgotPassword.vue              # Uses AppLogo + CopyrightText
│       ├── ResetPassword.vue               # Uses AppLogo + CopyrightText
│       └── VerifyEmail.vue                 # Uses AppLogo + CopyrightText
└── .github/
    └── SYSTEM_SETTINGS_GUIDE.md            # This file
```

---

## Usage in Custom Pages

To use system settings in your own pages:

### Access Settings in Vue Component

```vue
<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const appName = computed(() => page.props.settings?.general?.app_name || 'Finora Bank');
const logoLight = computed(() => page.props.settings?.branding?.logo_light || '');
const copyrightText = computed(() => page.props.settings?.branding?.copyright_text || 'Finora Bank');
</script>

<template>
    <h1>{{ appName }}</h1>
    <img v-if="logoLight" :src="logoLight" :alt="appName">
    <p>© {{ copyrightText }}</p>
</template>
```

### Or Use Components (Recommended)

```vue
<script setup>
import AppLogo from '@/Components/Common/AppLogo.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';
</script>

<template>
    <AppLogo :show-text="true" size="md" />
    <CopyrightText :show-extra-text="false" />
</template>
```

---

## Testing

### Test Logo Upload

1. Upload a logo via Filament admin
2. Clear cache: `php artisan cache:clear`
3. Visit `/login` page
4. Verify logo displays correctly
5. Toggle dark mode (if dark logo uploaded)
6. Verify dark logo displays

### Test Copyright Text

1. Update copyright settings via Filament admin
2. Clear cache
3. Visit any auth page
4. Verify footer shows updated copyright

### Test Fallback

1. Leave logo settings empty
2. Visit `/login`
3. Verify fallback logo (gradient circle with "F") displays
4. Verify app name displays correctly

---

## Performance Considerations

- Settings are **cached for 1 hour** (3600 seconds) for performance
- Cache is automatically cleared when settings are updated via the `setValue()` method
- For immediate changes during development, run: `php artisan cache:clear`

---

## Best Practices

1. **Always upload both light and dark logos** for best user experience
2. **Use transparent PNGs or SVGs** for logos
3. **Keep copyright year updated** at the start of each new year
4. **Use descriptive file names** for uploaded logos (e.g., `finora-logo-light.png`)
5. **Test in both light and dark modes** after uploading logos
6. **Store logos in `public/storage/branding/`** for organization

---

## Troubleshooting

### Logo Not Displaying

**Symptom:** Logo uploaded but not showing

**Solutions:**
1. Clear cache: `php artisan cache:clear`
2. Verify file path is correct in settings table
3. Check file permissions: `chmod 644 public/storage/branding/*`
4. Ensure file exists in `public/storage/branding/`
5. Check browser console for 404 errors

### Wrong Copyright Year

**Symptom:** Copyright shows old year

**Solutions:**
1. Update `copyright_year` in branding settings via Filament admin
2. Clear cache
3. Verify change in database: `SELECT * FROM settings WHERE \`key\`='copyright_year';`

### Settings Not Loading

**Symptom:** Fallback values always showing

**Solutions:**
1. Verify settings exist: `php artisan db:seed --class=SettingSeeder`
2. Check database connection in `.env`
3. Clear all caches: `php artisan optimize:clear`
4. Check `HandleInertiaRequests.php` is properly configured

---

## API Reference

### Setting Model Methods

```php
// Get single value with fallback
Setting::getValue(string $group, string $key, mixed $default = null): mixed

// Set single value
Setting::setValue(string $group, string $key, mixed $value, string $type = 'string'): void

// Get all settings in a group
Setting::getGroup(string $group): array
```

### Type Support

- `string` - Plain text values
- `integer` - Numeric values
- `boolean` - True/false (stored as '1'/'0')
- `json` - JSON-encoded arrays/objects

---

## Future Enhancements

Potential improvements for the settings system:

1. **Image Upload in Filament** - Direct file upload in admin panel
2. **Logo Preview** - Preview logos before saving
3. **Favicon Auto-Generation** - Generate favicon from logo automatically
4. **Theme Customization** - Allow color scheme customization via settings
5. **Multi-Tenant Support** - Different branding per tenant
6. **Settings Import/Export** - Backup and restore settings

---

## Recent Updates

### Email Template Refactoring (January 26, 2026)

All email templates have been refactored to use dynamic system settings instead of hardcoded values.

**Changes Made:**
- Created `app/helpers.php` with global helper functions
- Registered helpers in `composer.json` autoload
- Updated 16 email templates to use `app_name()` helper
- Updated PDF templates to use `app_name()` helper
- Replaced hardcoded "Finora Bank" with dynamic settings
- Replaced `config('app.name')` with `app_name()` for consistency
- Replaced hardcoded copyright with `copyright_text()` helper

**Benefits:**
- All emails now respect system settings
- Single source of truth for branding
- Easy rebranding via admin panel
- No code changes needed for brand updates
- Consistent branding across all touchpoints

---

*Last Updated: January 26, 2026*
