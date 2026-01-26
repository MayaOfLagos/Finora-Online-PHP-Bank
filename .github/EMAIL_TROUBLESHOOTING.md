# Email Sending Troubleshooting Guide

## Issue: Password Reset Email Not Sending

### Error Message
```
Error: Failed to send reset link. Please try again.
```

### Root Cause
The Mailtrap sandbox has reached its email sending limit. The specific error:

```
Failed to authenticate on SMTP server with username "9bc7085993707a" using the following authenticators: "CRAM-MD5", "LOGIN", "PLAIN". 
Authenticator "CRAM-MD5" returned "Expected response code "235" but got code "535", with message "535 5.7.0 The email limit is reached. 
Please upgrade your plan https://mailtrap.io/billing/plans/testing".
```

---

## Solutions

### Solution 1: Reset Mailtrap Inbox (Quick Fix)

1. Login to [Mailtrap.io](https://mailtrap.io)
2. Go to your sandbox inbox
3. Click **"Empty Inbox"** to delete all emails
4. This will free up your email quota
5. Try sending password reset email again

**Note:** This only works if you haven't exceeded your monthly limit. Check your usage in Mailtrap dashboard.

---

### Solution 2: Use a Different Mailtrap Inbox

1. Login to Mailtrap
2. Create a new inbox
3. Copy the new SMTP credentials
4. Update your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<new_username>
MAIL_PASSWORD=<new_password>
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@finorabank.com
MAIL_FROM_NAME="${APP_NAME}"
```

5. Clear config cache:
```bash
php artisan config:clear
php artisan cache:clear
```

---

### Solution 3: Upgrade Mailtrap Plan

**Free Tier Limits:**
- 500 emails per month
- 10,000 emails total inbox capacity
- 1 project
- 1 inbox

**Paid Plans:** Starting at $10/month
- 1,000+ emails per month
- Unlimited inbox capacity
- Multiple projects and inboxes
- Email templates
- HTML check
- Spam analysis

Visit: https://mailtrap.io/billing/plans/testing

---

### Solution 4: Switch to Production Email Service (Recommended for Live Apps)

For production deployment, use a real email service:

#### Option A: Gmail SMTP (Small Scale)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password  # Generate from Google Account settings
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note:** Enable 2FA and generate App Password in Google Account settings.

#### Option B: SendGrid (Recommended - Free 100 emails/day)

1. Sign up at [SendGrid](https://sendgrid.com)
2. Get your API key
3. Install SendGrid driver:
```bash
composer require symfony/sendgrid-mailer
```

4. Update `.env`:
```env
MAIL_MAILER=sendgrid
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@finorabank.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Option C: Mailgun (Flexible Pricing)

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-secret
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Option D: Amazon SES (Cost-Effective for High Volume)

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=noreply@your-verified-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Testing Password Reset Email

After configuring email, test it:

### Method 1: Via Tinker
```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
Password::sendResetLink(['email' => $user->email]);
```

### Method 2: Via Browser
1. Navigate to `/forgot-password`
2. Enter a valid user email
3. Click "Send Password Reset Link"
4. Check your email inbox (or Mailtrap)

### Method 3: Via Artisan Command
```bash
php artisan tinker --execute "
\$user = App\Models\User::first();
\Illuminate\Support\Facades\Password::sendResetLink(['email' => \$user->email]);
echo 'Password reset email sent to: ' . \$user->email;
"
```

---

## Custom Password Reset Notification

The application now uses a custom password reset notification with branded emails:

**File:** `app/Notifications/CustomResetPasswordNotification.php`

**Features:**
- Uses `app_name()` helper for dynamic branding
- Branded email signature
- Localized content
- Custom expiration message

**User Model Override:**
```php
// In app/Models/User.php
public function sendPasswordResetNotification($token): void
{
    $this->notify(new \App\Notifications\CustomResetPasswordNotification($token));
}
```

---

## Email Configuration Best Practices

### Development
- ✅ Use Mailtrap for email testing
- ✅ Never use production emails in development
- ✅ Test all email types (reset, verification, notifications)

### Staging
- ✅ Use a staging email service (separate SendGrid project)
- ✅ Test with real email addresses
- ✅ Verify deliverability and spam scores

### Production
- ✅ Use a reliable email service (SendGrid, Mailgun, SES)
- ✅ Set up proper SPF, DKIM, DMARC records
- ✅ Monitor email delivery rates
- ✅ Implement email queues for performance
- ✅ Set up email webhooks for bounce/complaint handling

---

## Monitoring Email Health

### Check Email Logs
```bash
tail -f storage/logs/laravel.log | grep -i "mail\|email"
```

### Check Queue Status (if using queues)
```bash
php artisan queue:work --once --verbose
```

### Test SMTP Connection
```bash
php artisan tinker --execute "
try {
    Mail::raw('Test email', function(\$message) {
        \$message->to('test@example.com')->subject('Test');
    });
    echo 'Email sent successfully';
} catch (\Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
"
```

---

## Common Email Errors

### Error 1: Connection Timeout
```
Connection could not be established with host smtp.example.com
```
**Solution:** Check MAIL_HOST and MAIL_PORT in `.env`

### Error 2: Authentication Failed
```
Expected response code "235" but got code "535"
```
**Solution:** Verify MAIL_USERNAME and MAIL_PASSWORD

### Error 3: Email Limit Reached (Current Issue)
```
The email limit is reached. Please upgrade your plan
```
**Solution:** See solutions above

### Error 4: SSL/TLS Error
```
stream_socket_enable_crypto(): SSL operation failed
```
**Solution:** Check MAIL_ENCRYPTION (should be `tls` or `ssl` or `null`)

---

## Quick Fix Checklist

- [ ] Check Mailtrap inbox usage/limits
- [ ] Clear old emails from Mailtrap inbox
- [ ] Verify `.env` email credentials are correct
- [ ] Run `php artisan config:clear`
- [ ] Run `php artisan cache:clear`
- [ ] Test with tinker command
- [ ] Check `storage/logs/laravel.log` for errors
- [ ] Verify email is not in spam folder
- [ ] Check Mailtrap inbox for received emails

---

## For Immediate Fix

Run these commands:

```bash
# Clear caches
php artisan config:clear
php artisan cache:clear

# Test email sending
php artisan tinker --execute "
\$user = App\Models\User::first();
if (\$user) {
    try {
        \Illuminate\Support\Facades\Password::sendResetLink(['email' => \$user->email]);
        echo 'Success! Check your Mailtrap inbox.';
    } catch (\Exception \$e) {
        echo 'Error: ' . \$e->getMessage();
    }
}
"
```

If you see "The email limit is reached" error:
1. Login to Mailtrap
2. Empty your inbox
3. Or create a new inbox and update credentials

---

*Last Updated: January 26, 2026*
