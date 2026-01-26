<x-mail::message>
# Login Verification Code

Hello {{ $user->name }},

You are receiving this email because we received a login request for your account. Please use the following One-Time Password (OTP) to verify your identity:

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

This code will expire in {{ setting('security', 'otp_expiry_minutes', 10) }} minutes.

**If you did not attempt to log in, please ignore this email or contact our support team immediately.**

For your security, do not share this code with anyone.

Best regards,<br>
{{ app_name() }}

---

{{ copyright_text() }}
</x-mail::message>
