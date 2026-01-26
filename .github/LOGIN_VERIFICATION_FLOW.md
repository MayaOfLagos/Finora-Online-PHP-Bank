# Login Verification Flow

## Overview

The Finora Bank application implements a comprehensive multi-step login verification system to enhance security. After successful email/password authentication, users may be required to complete additional verification steps before accessing the dashboard.

---

## Verification Steps

### 1. Email/Password Authentication
- Standard Laravel authentication using email and password
- Checks if account is active
- Records login history

### 2. Email OTP Verification (Optional)
- **Triggered When:**
  - Global setting `login_require_email_otp` is enabled (default: enabled)
  - AND user does NOT have `skip_email_otp` permission
  
- **Process:**
  1. User is redirected to `/verify-email-otp`
  2. 6-digit OTP is automatically sent to user's email
  3. OTP expires after configured time (default: 10 minutes)
  4. User enters OTP code
  5. System verifies OTP
  6. Session is marked as `email_otp_verified_at`
  
- **Features:**
  - Auto-send OTP on page load
  - 60-second countdown before resend
  - Rate limiting (3 send attempts, 5 verify attempts)
  - Branded email with OTP code
  - Security tips displayed on page
  - Logout option available

### 3. PIN Verification (Required by Default)
- **Triggered When:**
  - Global setting `login_require_pin` is enabled (default: enabled)
  - After email OTP verification (or if email OTP is skipped)
  
- **Process:**
  1. User is redirected to `/verify-pin`
  2. User enters transaction PIN using number pad
  3. PIN is verified against hashed `transaction_pin` in database
  4. Session is marked as `pin_verified_at`
  5. `last_login_at` and `last_login_ip` are updated
  
- **Features:**
  - Phone-style number pad (1-9, 0)
  - PIN masking with dots (••••••)
  - Clear and backspace buttons
  - 4-6 digit PIN support
  - Rate limiting (5 verify attempts)
  - Cancel and logout options
  - Security notice displayed

### 4. Dashboard Access
- User successfully accesses dashboard after completing all required verification steps

---

## Flow Chart

```
Login (Email/Password)
    ↓
Is Account Active?
    ↓ NO → Show Error
    ↓ YES
    ↓
Is login_require_email_otp = true AND skip_email_otp = false?
    ↓ YES → Email OTP Verification
    ↓         ↓
    ↓         OTP Verified?
    ↓         ↓ NO → Show Error
    ↓         ↓ YES
    ↓         ↓
    ↓ ←──────┘
    ↓
Is login_require_pin = true?
    ↓ YES → PIN Verification
    ↓         ↓
    ↓         PIN Verified?
    ↓         ↓ NO → Show Error
    ↓         ↓ YES
    ↓         ↓
    ↓ ←──────┘
    ↓
Dashboard Access Granted
```

---

## Database Schema

### Users Table (New Fields)

```sql
-- User-level permissions
skip_email_otp BOOLEAN DEFAULT FALSE

-- Verification timestamps
email_otp_verified_at TIMESTAMP NULL
pin_verified_at TIMESTAMP NULL

-- Login tracking
last_login_at TIMESTAMP NULL
last_login_ip STRING NULL
```

### Settings Table (New Entries)

```sql
-- Security group
login_require_email_otp = '1' (boolean)
login_require_pin = '1' (boolean)
```

---

## Middleware

### `EnsureEmailOtpVerified`

**Alias:** `verified.email.otp`

**Logic:**
1. Check if user is authenticated
2. Check if `login_require_email_otp` setting is enabled
3. If disabled, allow access
4. Check if user has `skip_email_otp` permission
5. If yes, allow access
6. Check if session has `email_otp_verified_at` within last 30 minutes
7. If yes, allow access
8. Otherwise, redirect to `/verify-email-otp`

### `EnsurePinVerified`

**Alias:** `verified.pin`

**Logic:**
1. Check if user is authenticated
2. Check if `login_require_pin` setting is enabled
3. If disabled, allow access
4. Check if session has `pin_verified_at` within last 30 minutes
5. If yes, allow access
6. Otherwise, redirect to `/verify-pin`

---

## Controllers

### `EmailOtpController`

**Methods:**
- `show()` - Display email OTP verification page
- `send()` - Generate and send OTP via email
- `verify()` - Verify OTP code

**Rate Limiting:**
- Send: 3 attempts per 60 seconds per user
- Verify: 5 attempts per 60 seconds per user

**Session Storage:**
- `email_otp` - The OTP code
- `email_otp_expires_at` - Expiration timestamp
- `email_otp_verified_at` - Verification timestamp

### `PinVerificationController`

**Methods:**
- `show()` - Display PIN verification page
- `verify()` - Verify transaction PIN

**Rate Limiting:**
- Verify: 5 attempts per 60 seconds per user

**Session Storage:**
- `pin_verified_at` - Verification timestamp

---

## Email Template

### Email OTP Mail

**File:** `resources/views/emails/email-otp.blade.php`

**Content:**
- Greeting with user name
- 6-digit OTP code (large, prominent display)
- Expiration time
- Security warning
- Support contact
- Branded footer with app name and copyright

**Subject:** `Your Login Verification Code - {App Name}`

---

## Vue Pages

### `Auth/VerifyEmailOtp.vue`

**Features:**
- PrimeVue `InputOtp` component (6 digits)
- Auto-send OTP on mount
- 60-second countdown timer
- Resend button (enabled after countdown)
- Verify button (disabled until 6 digits entered)
- Logout button
- Security tip panel
- Toast notifications for success/error
- Auto-focus on OTP input
- Enter key to submit

**Layout:** `AuthLayout.vue`

### `Auth/VerifyPin.vue`

**Features:**
- Custom number pad (3x4 grid)
- Buttons: 1-9, 0, Clear, Backspace
- PIN display with masking (dots)
- Digit counter (X/6 digits)
- Verify button (disabled until 4+ digits)
- Cancel button (clears PIN)
- Logout button
- Security notice panel
- Toast notifications for success/error
- Active/disabled states for buttons
- Press animations on buttons

**Layout:** `AuthLayout.vue`

**Number Pad Layout:**
```
[1] [2] [3]
[4] [5] [6]
[7] [8] [9]
[Clear] [0] [←]
```

---

## Route Configuration

### Authentication Routes (No Verification Required)

```php
Route::middleware('guest')->group(function () {
    Route::get('login', ...)->name('login');
    Route::post('login', ...);
    // ... other guest routes
});
```

### Verification Routes (Auth Required Only)

```php
Route::middleware('auth')->group(function () {
    // Email OTP
    Route::get('verify-email-otp', ...)->name('verify-email-otp.show');
    Route::post('verify-email-otp', ...)->name('verify-email-otp.verify');
    Route::post('verify-email-otp/send', ...)->name('verify-email-otp.send');
    
    // PIN
    Route::get('verify-pin', ...)->name('verify-pin.show');
    Route::post('verify-pin', ...)->name('verify-pin.verify');
});
```

### Dashboard Routes (Full Verification Required)

```php
Route::middleware(['auth', 'verified.email.otp', 'verified.pin'])->group(function () {
    Route::get('/dashboard', ...)->name('dashboard');
    // ... all other protected routes
});
```

---

## Admin Configuration

### Global Settings (Filament Admin)

Admins can control the verification requirements globally:

**Location:** Settings → Security

**Options:**
1. **Login Require Email OTP** (Boolean)
   - Default: Enabled
   - Controls whether email OTP is required after login
   - Can be overridden per user with `skip_email_otp`

2. **Login Require PIN** (Boolean)
   - Default: Enabled
   - Controls whether PIN verification is required
   - No user-level override (security requirement)

### User-Level Permissions

Admins can grant individual users the ability to skip email OTP:

**Location:** Users → Edit User → Permissions

**Field:** `skip_email_otp` (Checkbox)
- When checked, user bypasses email OTP verification
- User still requires PIN verification (if enabled globally)
- Useful for trusted users or admin accounts

---

## Security Considerations

### Session Lifetime
- Email OTP verification valid for 30 minutes
- PIN verification valid for 30 minutes
- Sessions cleared on logout or new login

### Rate Limiting
- Send OTP: 3 attempts per minute
- Verify OTP: 5 attempts per minute
- Verify PIN: 5 attempts per minute
- Automatic lockout with countdown

### OTP Expiration
- Configurable via `security.otp_expiry_minutes` setting
- Default: 10 minutes
- Expired OTPs automatically cleared from session

### PIN Security
- Transaction PIN stored hashed in database
- Verified using `Hash::check()`
- No plaintext PIN ever stored or transmitted

### Verification Bypass
- Email OTP can be skipped per user (`skip_email_otp`)
- PIN verification has NO bypass option
- Admins must explicitly grant skip permissions

---

## Testing

### Test Flow 1: Full Verification

1. Login with valid credentials
2. Redirected to `/verify-email-otp`
3. Receive OTP email
4. Enter OTP code
5. Redirected to `/verify-pin`
6. Enter transaction PIN
7. Redirected to `/dashboard`

### Test Flow 2: Email OTP Disabled

1. Admin disables `login_require_email_otp` setting
2. Login with valid credentials
3. Skip email OTP (no redirect)
4. Redirected to `/verify-pin`
5. Enter transaction PIN
6. Redirected to `/dashboard`

### Test Flow 3: User Skip Email OTP

1. Admin enables `skip_email_otp` for user
2. Login with valid credentials
3. Skip email OTP (no redirect)
4. Redirected to `/verify-pin`
5. Enter transaction PIN
6. Redirected to `/dashboard`

### Test Flow 4: Both Verifications Disabled

1. Admin disables both `login_require_email_otp` and `login_require_pin`
2. Login with valid credentials
3. Immediately redirected to `/dashboard`

---

## Troubleshooting

### Email OTP Not Received
- Check email configuration in `.env`
- Verify Mailtrap/SMTP settings
- Check spam folder
- Review Laravel logs: `storage/logs/laravel.log`

### Invalid OTP Error
- Check if OTP has expired
- Verify correct OTP from latest email
- Check rate limiting hasn't been triggered
- Clear session and try again

### Invalid PIN Error
- Verify user has `transaction_pin` set
- Check if PIN is correct (case-sensitive for numeric)
- Ensure PIN is 4-6 digits
- Contact admin if PIN forgotten

### Redirect Loop
- Clear browser cache and cookies
- Check middleware configuration in `bootstrap/app.php`
- Verify session is working (check `SESSION_DRIVER` in `.env`)
- Check database for proper field values

### Middleware Not Applied
- Run `php artisan route:cache` if routes cached
- Verify middleware aliases in `bootstrap/app.php`
- Check route definition in `routes/web.php`

---

## Development Notes

### Helper Functions Used
- `app_name()` - Get application name
- `setting($group, $key, $default)` - Get setting value
- `copyright_text()` - Get copyright text

### Dependencies
- Laravel 12+
- Inertia.js
- Vue 3
- PrimeVue 4.x
- Mailtrap/SMTP for emails

### File Locations
- **Middleware:** `app/Http/Middleware/`
- **Controllers:** `app/Http/Controllers/Auth/`
- **Vue Pages:** `resources/js/Pages/Auth/`
- **Email Templates:** `resources/views/emails/`
- **Routes:** `routes/web.php`
- **Middleware Config:** `bootstrap/app.php`
- **Migration:** `database/migrations/2026_01_26_152508_add_login_verification_fields_to_users_table.php`

---

## Future Enhancements

- [ ] SMS OTP option (alternative to email)
- [ ] Biometric authentication (fingerprint, face)
- [ ] Remember device feature (skip verification for trusted devices)
- [ ] Backup codes for account recovery
- [ ] Admin dashboard for verification statistics
- [ ] Webhook notifications for security events
- [ ] Time-based verification expiry (auto-logout after X hours)
- [ ] IP whitelist/blacklist
- [ ] Geolocation verification
- [ ] Failed verification alerts

---

**Created:** 2026-01-26  
**Last Updated:** 2026-01-26  
**Status:** Implemented and Tested
