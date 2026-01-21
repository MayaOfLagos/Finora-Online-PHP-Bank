# Backend - Transfer Module Todo List

## Overview
Implementation tasks for all transfer types in Finora Bank.

---

## Wire Transfer (International)

### Models & Migrations
- [ ] Create `wire_transfers` migration
- [ ] Create `WireTransfer` model with relationships
- [ ] Create `wire_transfer_verifications` table (IMF, Tax, COT tracking)
- [ ] Create `WireTransferVerification` model
- [ ] Add SWIFT/BIC code fields

### Controllers
- [ ] Create `WireTransferController`
- [ ] Implement `initiate()` - Start wire transfer
- [ ] Implement `verifyPin()` - Verify transaction PIN
- [ ] Implement `verifyImfCode()` - IMF code verification
- [ ] Implement `verifyTaxCode()` - Tax code verification
- [ ] Implement `verifyCotCode()` - COT code verification
- [ ] Implement `verifyOtp()` - Email OTP verification
- [ ] Implement `complete()` - Finalize transfer
- [ ] Implement `cancel()` - Cancel pending transfer

### Services
- [ ] Create `WireTransferService`
- [ ] Implement fee calculation logic
- [ ] Implement currency conversion
- [ ] Implement transfer limits validation
- [ ] Implement verification code validation

### Requests
- [ ] Create `InitiateWireTransferRequest`
- [ ] Create `VerifyWireTransferRequest`

### Events & Notifications
- [ ] Create `WireTransferInitiated` event
- [ ] Create `WireTransferCompleted` event
- [ ] Create `WireTransferFailed` event
- [ ] Create email notification templates

---

## Internal Transfer (User to User)

### Models & Migrations
- [ ] Create `internal_transfers` migration
- [ ] Create `InternalTransfer` model
- [ ] Add sender/receiver relationships

### Controllers
- [ ] Create `InternalTransferController`
- [ ] Implement `initiate()` - Start internal transfer
- [ ] Implement `verifyPin()` - Verify transaction PIN
- [ ] Implement `verifyOtp()` - Email OTP verification
- [ ] Implement `complete()` - Finalize transfer

### Services
- [ ] Create `InternalTransferService`
- [ ] Implement balance deduction
- [ ] Implement balance credit
- [ ] Implement transaction logging

### Requests
- [ ] Create `InitiateInternalTransferRequest`

---

## Domestic Transfer (Local Banks)

### Models & Migrations
- [ ] Create `domestic_transfers` migration
- [ ] Create `DomesticTransfer` model
- [ ] Create `banks` table for local banks
- [ ] Create `Bank` model

### Controllers
- [ ] Create `DomesticTransferController`
- [ ] Implement `initiate()` - Start domestic transfer
- [ ] Implement `verifyPin()` - Verify transaction PIN
- [ ] Implement `verifyOtp()` - Email OTP verification
- [ ] Implement `complete()` - Finalize transfer

### Services
- [ ] Create `DomesticTransferService`
- [ ] Implement bank routing validation
- [ ] Implement fee calculation
- [ ] Implement processing time logic

---

## Account-to-Account Transfer

### Models & Migrations
- [ ] Create `account_transfers` migration
- [ ] Create `AccountTransfer` model

### Controllers
- [ ] Create `AccountTransferController`
- [ ] Implement `initiate()` - Start transfer
- [ ] Implement `verifyPin()` - Verify transaction PIN
- [ ] Implement `verifyOtp()` - Email OTP verification
- [ ] Implement `complete()` - Finalize transfer

### Services
- [ ] Create `AccountTransferService`
- [ ] Implement multi-account balance management

---

## Shared Components

### Enums
- [ ] Create `TransferStatus` enum (pending, processing, completed, failed, cancelled)
- [ ] Create `TransferType` enum (wire, internal, domestic, account)
- [ ] Create `VerificationStep` enum

### Traits
- [ ] Create `HasTransactionPin` trait
- [ ] Create `HasOtpVerification` trait
- [ ] Create `Transferable` trait

### Middleware
- [ ] Create `EnsureTransactionPinSet` middleware
- [ ] Create `ValidateTransferSession` middleware

---

## Routes
- [ ] Define transfer routes in `routes/web.php`
- [ ] Group routes with proper middleware
- [ ] Add rate limiting for security

---

## Testing
- [ ] Write unit tests for transfer services
- [ ] Write feature tests for transfer flows
- [ ] Test edge cases (insufficient balance, invalid codes)

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Wire Transfer | 🔴 Not Started | 0% |
| Internal Transfer | 🔴 Not Started | 0% |
| Domestic Transfer | 🔴 Not Started | 0% |
| Account Transfer | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
