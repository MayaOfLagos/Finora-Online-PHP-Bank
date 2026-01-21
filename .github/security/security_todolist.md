# Security Implementation Todo List

## Overview
Security features and flows for Finora Bank.

---

## Authentication Security

### Laravel Sanctum/Fortify
- [ ] Install and configure Laravel Fortify
- [ ] Configure authentication views
- [ ] Set up session-based authentication
- [ ] Configure password validation rules

### Two-Factor Authentication
- [ ] Enable 2FA in Fortify
- [ ] Create 2FA setup flow
- [ ] Create 2FA verification flow
- [ ] Implement recovery codes
- [ ] Add 2FA to sensitive operations

### Session Management
- [ ] Configure session lifetime
- [ ] Implement session invalidation
- [ ] Track active sessions
- [ ] Device fingerprinting
- [ ] Concurrent session limits

### Login Security
- [ ] Implement login throttling
- [ ] Track login attempts
- [ ] Block suspicious IPs
- [ ] Notify on new device login
- [ ] Record login history

---

## Transaction Security

### Transaction PIN

#### Implementation
- [ ] Create PIN storage (hashed)
- [ ] Create PIN verification service
- [ ] Implement PIN lockout after failed attempts
- [ ] Create PIN reset flow
- [ ] Create PIN change flow

#### PIN Flow
```
1. User enters PIN
2. System validates against hashed PIN
3. Track failed attempts
4. Lock after 3-5 failures
5. Require email OTP to unlock
```

### Email OTP

#### Implementation
- [ ] Create OTP generation service
- [ ] Create OTP storage (cache/database)
- [ ] Implement OTP expiration (10 minutes)
- [ ] Create OTP verification
- [ ] Implement resend functionality
- [ ] Rate limit OTP requests

#### OTP Configuration
```php
// config/otp.php
'length' => 6,
'expiry' => 10, // minutes
'max_attempts' => 3,
'resend_cooldown' => 60, // seconds
```

### Wire Transfer Verification Codes

#### IMF Code
- [ ] Admin sets IMF code per user
- [ ] User enters IMF code during wire transfer
- [ ] Validate against stored code
- [ ] Track verification attempts

#### Tax Code
- [ ] Admin sets Tax code per user
- [ ] User enters Tax code after IMF
- [ ] Validate against stored code
- [ ] Track verification attempts

#### COT Code
- [ ] Admin sets COT code per user
- [ ] User enters COT code after Tax
- [ ] Validate against stored code
- [ ] Track verification attempts

#### Verification Code Model
```
verification_codes table:
- id
- user_id
- type (imf, tax, cot)
- code (encrypted)
- is_active
- created_at
- updated_at
```

---

## Transaction Flow Security

### Wire Transfer Flow
```
Step 1: Enter Transfer Details
    ↓
Step 2: Enter Transaction PIN
    ↓ (PIN Verified)
Step 3: Enter IMF Code
    ↓ (IMF Verified)
Step 4: Enter Tax Code
    ↓ (Tax Verified)
Step 5: Enter COT Code
    ↓ (COT Verified)
Step 6: Enter Email OTP
    ↓ (OTP Verified)
Step 7: Transfer Success
```

### Internal/Domestic/A2A Flow
```
Step 1: Enter Transfer Details
    ↓
Step 2: Enter Transaction PIN
    ↓ (PIN Verified)
Step 3: Enter Email OTP
    ↓ (OTP Verified)
Step 4: Transfer Success
```

### Deposit Flow (Mobile)
```
Step 1: Enter Amount
    ↓
Step 2: Enter Transaction PIN
    ↓ (PIN Verified)
Step 3: Process Payment
    ↓
Step 4: Deposit Success
```

---

## Session-Based Security Tokens

### Transfer Session Token
- [ ] Generate unique token for each transfer
- [ ] Store in session with expiry
- [ ] Validate token at each step
- [ ] Invalidate on completion/failure
- [ ] Prevent replay attacks

### Implementation
```php
// Generate transfer session
$transferSession = [
    'token' => Str::uuid(),
    'type' => 'wire_transfer',
    'step' => 1,
    'data' => $transferData,
    'expires_at' => now()->addMinutes(30),
];
Session::put('transfer_session', $transferSession);
```

---

## Data Encryption

### Sensitive Data Fields
- [ ] Card numbers (AES-256)
- [ ] CVV (encrypted)
- [ ] Transaction PIN (bcrypt hash)
- [ ] Verification codes (encrypted)
- [ ] Personal documents (encrypted storage)

### Laravel Encryption
- [ ] Use `encrypt()` for reversible encryption
- [ ] Use `Hash::make()` for passwords/PINs
- [ ] Configure `APP_KEY` securely
- [ ] Rotate keys procedure

---

## Rate Limiting

### API Rate Limits
- [ ] Authentication: 5 attempts/minute
- [ ] OTP requests: 3/minute
- [ ] PIN verification: 5 attempts, then lockout
- [ ] Transfer initiation: 10/minute
- [ ] General API: 60/minute

### Implementation
```php
// routes/web.php
Route::middleware(['auth', 'throttle:transfers'])
    ->group(function () {
        Route::post('/transfer/wire', [WireTransferController::class, 'initiate']);
    });

// RouteServiceProvider
RateLimiter::for('transfers', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});
```

---

## Audit Logging

### Events to Log
- [ ] All authentication events
- [ ] All transfer attempts
- [ ] PIN changes
- [ ] Profile updates
- [ ] KYC submissions
- [ ] Card operations
- [ ] Admin actions

### Log Structure
```php
// activity_logs table
[
    'user_id',
    'action',
    'description',
    'ip_address',
    'user_agent',
    'metadata', // JSON
    'created_at',
]
```

---

## Security Headers

### HTTP Headers
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: DENY
- [ ] X-XSS-Protection: 1; mode=block
- [ ] Strict-Transport-Security
- [ ] Content-Security-Policy

### Implementation
- [ ] Create security middleware
- [ ] Apply to all routes

---

## Input Validation

### Server-Side Validation
- [ ] Validate all inputs
- [ ] Sanitize user data
- [ ] Prevent SQL injection (Eloquent)
- [ ] Prevent XSS (Blade escaping)
- [ ] Validate file uploads

### Amount Validation
- [ ] Minimum transfer amount
- [ ] Maximum transfer amount
- [ ] Daily limits
- [ ] Monthly limits
- [ ] Sufficient balance check

---

## Security Middleware

### Custom Middleware
- [ ] `EnsureTransactionPinSet` - Check PIN exists
- [ ] `ValidateTransferSession` - Validate transfer session
- [ ] `CheckAccountStatus` - Ensure account is active
- [ ] `VerifyKycLevel` - Check KYC for operation
- [ ] `LogActivity` - Log user activity

---

## Testing

### Security Tests
- [ ] Test authentication flows
- [ ] Test rate limiting
- [ ] Test PIN lockout
- [ ] Test OTP expiration
- [ ] Test session timeout
- [ ] Test CSRF protection
- [ ] Test unauthorized access

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Authentication | 🔴 Not Started | 0% |
| Transaction PIN | 🔴 Not Started | 0% |
| Email OTP | 🔴 Not Started | 0% |
| Wire Verification | 🔴 Not Started | 0% |
| Rate Limiting | 🔴 Not Started | 0% |
| Audit Logging | 🔴 Not Started | 0% |
| Encryption | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
