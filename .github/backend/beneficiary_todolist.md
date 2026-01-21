# Backend - Beneficiary Module Todo List

## Overview
Implementation tasks for the beneficiary management system (Account-to-Account transfers only).

---

## Models & Migrations

### Beneficiaries
- [ ] Create `beneficiaries` migration
- [ ] Create `Beneficiary` model
- [ ] Add fields:
  - user_id (owner)
  - beneficiary_user_id (recipient within bank)
  - nickname
  - account_number
  - is_verified
  - is_favorite
  - transfer_limit
  - last_used_at
  - created_at
  - updated_at

### Beneficiary Verification
- [ ] Create `beneficiary_verifications` migration
- [ ] Create `BeneficiaryVerification` model
- [ ] Track verification attempts

---

## Controllers

### BeneficiaryController
- [ ] Implement `index()` - List all beneficiaries
- [ ] Implement `store()` - Add new beneficiary
- [ ] Implement `show()` - View beneficiary details
- [ ] Implement `update()` - Update beneficiary (nickname, limit)
- [ ] Implement `destroy()` - Remove beneficiary
- [ ] Implement `verify()` - Verify beneficiary account
- [ ] Implement `favorite()` - Toggle favorite status
- [ ] Implement `search()` - Search beneficiaries
- [ ] Implement `recent()` - Get recently used

---

## Services

### BeneficiaryService
- [ ] Implement beneficiary creation
- [ ] Implement account validation
- [ ] Implement nickname uniqueness check
- [ ] Implement limit management
- [ ] Implement favorite management

### BeneficiaryVerificationService
- [ ] Implement account existence check
- [ ] Implement account holder name verification
- [ ] Implement OTP verification for adding beneficiary

---

## Validation Rules

### Adding Beneficiary
- [ ] Account number must exist in system
- [ ] Cannot add self as beneficiary
- [ ] Cannot add duplicate beneficiary
- [ ] Nickname must be unique per user
- [ ] Maximum beneficiaries limit (configurable)

### Transfer Limits
- [ ] Per-transaction limit
- [ ] Daily limit per beneficiary
- [ ] Monthly limit per beneficiary

---

## Features

### Quick Actions
- [ ] Implement "Quick Transfer" from beneficiary
- [ ] Implement "Set as Favorite"
- [ ] Implement "Last Transfer Amount" quick fill

### Search & Filter
- [ ] Search by nickname
- [ ] Search by account number
- [ ] Filter by favorite
- [ ] Sort by last used
- [ ] Sort by name

---

## Enums

- [ ] Create `BeneficiaryStatus` enum (active, inactive, blocked)

---

## Events & Notifications

### Events
- [ ] Create `BeneficiaryAdded` event
- [ ] Create `BeneficiaryVerified` event
- [ ] Create `BeneficiaryRemoved` event

### Notifications
- [ ] Create beneficiary added email
- [ ] Create beneficiary removed email

---

## Security

### Adding Beneficiary Flow
```
Enter Account Number → 
Validate Account → 
Verify PIN → 
Verify Email OTP → 
Beneficiary Added
```

### Beneficiary Limits
- [ ] Configurable max beneficiaries per user
- [ ] Cooling period after adding (optional)
- [ ] Verification requirement for new beneficiaries

---

## Requests

- [ ] Create `StoreBeneficiaryRequest`
- [ ] Create `UpdateBeneficiaryRequest`
- [ ] Create `VerifyBeneficiaryRequest`

---

## API Responses

### Beneficiary List Response
```json
{
  "data": [
    {
      "id": "uuid",
      "nickname": "John Savings",
      "account_number": "****1234",
      "account_holder": "John Doe",
      "is_verified": true,
      "is_favorite": true,
      "transfer_limit": 10000,
      "last_used_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

---

## Routes

- [ ] Define beneficiary routes
- [ ] Add authentication middleware
- [ ] Add rate limiting

---

## Testing

- [ ] Test beneficiary creation
- [ ] Test duplicate prevention
- [ ] Test account validation
- [ ] Test limit enforcement
- [ ] Test search functionality

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Models & Migrations | 🔴 Not Started | 0% |
| CRUD Operations | 🔴 Not Started | 0% |
| Verification | 🔴 Not Started | 0% |
| Quick Actions | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
