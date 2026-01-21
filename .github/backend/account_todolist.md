# Backend - Account Management Todo List

## Overview
Implementation tasks for user account management in Finora Bank.

---

## Models & Migrations

### Users (Extend Laravel Default)
- [ ] Modify `users` migration
- [ ] Add fields:
  - phone_number
  - date_of_birth
  - address fields
  - profile_photo
  - transaction_pin (hashed)
  - is_active
  - is_verified
  - last_login_at
  - last_login_ip

### Account Types
- [ ] Create `account_types` migration
- [ ] Create `AccountType` model
- [ ] Seed types (Savings, Checking, Business)

### Bank Accounts
- [ ] Create `bank_accounts` migration
- [ ] Create `BankAccount` model
- [ ] Add fields:
  - user_id
  - account_type_id
  - account_number (unique)
  - balance (stored in cents)
  - currency
  - is_primary
  - is_active
  - opened_at

### KYC Verification
- [ ] Create `kyc_verifications` migration
- [ ] Create `KycVerification` model
- [ ] Add document fields
- [ ] Add verification status

### Login History
- [ ] Create `login_histories` migration
- [ ] Create `LoginHistory` model
- [ ] Track IP, device, location

### User Sessions
- [ ] Create `user_sessions` migration
- [ ] Create `UserSession` model
- [ ] Track active devices

---

## Controllers

### ProfileController
- [ ] Implement `show()` - View profile
- [ ] Implement `update()` - Update profile
- [ ] Implement `updatePhoto()` - Update profile photo
- [ ] Implement `changePassword()` - Change password
- [ ] Implement `deleteAccount()` - Soft delete account

### BankAccountController
- [ ] Implement `index()` - List user's accounts
- [ ] Implement `show()` - View account details
- [ ] Implement `create()` - Open new account
- [ ] Implement `setPrimary()` - Set primary account
- [ ] Implement `statement()` - Get account statement
- [ ] Implement `close()` - Close account

### KycController
- [ ] Implement `show()` - View KYC status
- [ ] Implement `submit()` - Submit KYC documents
- [ ] Implement `uploadDocument()` - Upload document

### TransactionPinController
- [ ] Implement `create()` - Set initial PIN
- [ ] Implement `change()` - Change PIN
- [ ] Implement `reset()` - Reset PIN (via email)
- [ ] Implement `verify()` - Verify PIN

### SessionController
- [ ] Implement `index()` - List active sessions
- [ ] Implement `destroy()` - Logout specific session
- [ ] Implement `destroyAll()` - Logout all sessions

### LoginHistoryController
- [ ] Implement `index()` - View login history

---

## Services

### AccountService
- [ ] Implement account number generation
- [ ] Implement balance management
- [ ] Implement account closure workflow

### KycService
- [ ] Implement document upload
- [ ] Implement verification workflow
- [ ] Implement KYC levels (Basic, Verified, Premium)

### TransactionPinService
- [ ] Implement PIN hashing
- [ ] Implement PIN verification
- [ ] Implement failed attempts tracking
- [ ] Implement PIN lockout

### StatementService
- [ ] Implement statement generation
- [ ] Implement PDF export
- [ ] Implement date range filtering
- [ ] Implement transaction categorization

### SessionService
- [ ] Implement session tracking
- [ ] Implement device fingerprinting
- [ ] Implement suspicious activity detection

---

## Account Number Generation

### Format
- [ ] Bank identifier prefix (e.g., "FIN")
- [ ] Account type code
- [ ] Sequential number
- [ ] Check digit (Luhn algorithm)

### Example: `FIN-SAV-00001234-7`

---

## KYC Levels

### Level 1 - Basic
- Email verified
- Phone verified
- Basic limits

### Level 2 - Verified
- ID document uploaded
- Selfie verification
- Increased limits

### Level 3 - Premium
- Address proof uploaded
- Income proof uploaded
- Maximum limits

---

## Enums

- [ ] Create `AccountType` enum (savings, checking, business)
- [ ] Create `AccountStatus` enum (active, frozen, closed)
- [ ] Create `KycLevel` enum (basic, verified, premium)
- [ ] Create `KycStatus` enum (pending, approved, rejected)

---

## Events & Notifications

### Events
- [ ] Create `AccountCreated` event
- [ ] Create `AccountClosed` event
- [ ] Create `KycSubmitted` event
- [ ] Create `KycApproved` event
- [ ] Create `KycRejected` event
- [ ] Create `NewDeviceLogin` event

### Notifications
- [ ] Create welcome email
- [ ] Create account opened email
- [ ] Create KYC approved email
- [ ] Create KYC rejected email
- [ ] Create new device login alert
- [ ] Create profile update confirmation

---

## Account Statement Features

### Statement Contents
- [ ] Opening balance
- [ ] Closing balance
- [ ] All transactions
- [ ] Transaction categories
- [ ] Running balance

### Export Formats
- [ ] PDF statement
- [ ] Excel/CSV export

---

## Requests

- [ ] Create `UpdateProfileRequest`
- [ ] Create `ChangePasswordRequest`
- [ ] Create `CreateAccountRequest`
- [ ] Create `SubmitKycRequest`
- [ ] Create `CreatePinRequest`
- [ ] Create `ChangePinRequest`

---

## Routes

- [ ] Define profile routes
- [ ] Define account routes
- [ ] Define KYC routes
- [ ] Define PIN routes
- [ ] Define session routes
- [ ] Add authentication middleware

---

## Testing

- [ ] Test profile updates
- [ ] Test account creation
- [ ] Test PIN management
- [ ] Test KYC workflow
- [ ] Test session management
- [ ] Test statement generation

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| User Model | 🔴 Not Started | 0% |
| Bank Accounts | 🔴 Not Started | 0% |
| KYC System | 🔴 Not Started | 0% |
| Transaction PIN | 🔴 Not Started | 0% |
| Session Management | 🔴 Not Started | 0% |
| Statements | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
