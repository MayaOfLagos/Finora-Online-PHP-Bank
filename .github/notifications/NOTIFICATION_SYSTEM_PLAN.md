# Finora Bank - Notification System Implementation Plan

## ✅ IMPLEMENTATION COMPLETED

### Phase 1: Database Notifications Table ✅
- Created `notifications` table via `php artisan notifications:table`
- Migration applied successfully

### Phase 2: Notification Infrastructure ✅

#### Files Created:
- `app/Notifications/GeneralNotification.php` - Base notification class with factory methods
- `app/Http/Controllers/NotificationController.php` - Web controller for notifications
- `resources/js/Pages/Notifications/Index.vue` - Full page notifications view

#### Files Modified:
- `routes/web.php` - Added notification routes
- `app/Http/Controllers/DashboardController.php` - Added notifications to page props
- `app/Http/Controllers/Api/V1/NotificationController.php` - Fixed to use proper model
- `app/Filament/Resources/Users/Tables/UsersTable.php` - Added database notifications for admin actions
- `resources/js/Components/Navigation/NotificationPanel.vue` - Complete redesign

### Phase 3: NotificationPanel.vue Redesign ✅

#### Desktop View (PrimeVue):
- Enhanced OverlayPanel with gradient header
- "All" and "Unread" tabs
- Smooth animations and hover effects
- Icon-based notification icons with type mapping
- Relative time formatting
- Mark all as read functionality

#### Mobile View (Glassmorphism):
- Detached floating panel from right side
- Liquid glass effect matching bottom nav style
- `z-[90]` z-index for proper layering
- Slide-in animation from right
- Touch-friendly card interactions with scale effect
- Segmented tab switcher
- Body scroll lock when open

### Phase 4: Routes Created ✅
```
GET    /notifications                 - Index page
GET    /notifications/dropdown        - Get dropdown data (AJAX)
GET    /notifications/unread-count    - Get unread count (AJAX)
POST   /notifications/{id}/mark-read  - Mark single as read
POST   /notifications/mark-all-read   - Mark all as read
DELETE /notifications/{id}            - Delete single
DELETE /notifications                 - Delete all
```

### Phase 5: GeneralNotification Factory Methods ✅
Static factory methods for easy notification creation:
- `transferCompleted($amount, $recipient, $href)`
- `transferReceived($amount, $sender, $href)`
- `depositReceived($amount, $method, $href)`
- `depositApproved($amount, $href)`
- `securityAlert($title, $message, $href)`
- `newLogin($device, $location, $href)`
- `loanStatusUpdate($status, $amount, $message, $href)`
- `cardUpdate($status, $cardType, $message, $href)`
- `supportTicketUpdate($ticketId, $status, $message, $href)`
- `adminMessage($title, $message, $href)`
- `kycUpdate($status, $message, $href)`

---

## Current State Analysis

### Database Tables (61 total)
The following tables can trigger user notifications:

| Table | Event Types | Currently Notified |
|-------|-------------|-------------------|
| `wire_transfers` | created, status_change, completed, failed | ❌ No |
| `domestic_transfers` | created, status_change, completed, failed | ❌ No |
| `internal_transfers` | created, completed, failed | ❌ No |
| `account_transfers` | created, completed | ❌ No |
| `check_deposits` | submitted, approved, rejected, on_hold | ❌ No |
| `mobile_deposits` | submitted, completed, failed | ✅ Partial (email) |
| `crypto_deposits` | submitted, confirmed, pending | ✅ Partial (email) |
| `loan_applications` | submitted, approved, rejected, documents_needed | ❌ No |
| `loans` | disbursed, payment_due, payment_received, late | ❌ No |
| `loan_payments` | received, missed, reminder | ❌ No |
| `grant_applications` | submitted, approved, rejected | ✅ Yes (via Notification class) |
| `grant_disbursements` | processed | ✅ Yes |
| `cards` | issued, shipped, activated, blocked | ❌ No |
| `card_requests` | submitted, approved, shipped | ❌ No |
| `support_tickets` | created, replied, status_change, closed | ✅ Yes (email) |
| `kyc_verifications` | submitted, approved, rejected | ✅ Yes (email) |
| `login_histories` | new_device_login, suspicious_login | ❌ No |
| `bank_accounts` | created, updated | ✅ Yes (email) |
| `vouchers` | redeemed | ✅ Yes (email) |
| `rewards` | earned, redeemed | ❌ No |
| `tax_refunds` | submitted, status_change | ❌ No |
| `money_requests` | received, approved, declined | ❌ No |
| `exchange_money` | completed | ❌ No |
| `withdrawals` | initiated, completed, failed | ❌ No |

### Existing Mail Classes (app/Mail/)
- `AccountStatementMail.php` - Account statements
- `AdminNotificationMail.php` - Admin to user notifications ✅
- `BankAccountCreatedMail.php` - New account created ✅
- `DepositNotificationMail.php` - Deposit notifications ✅
- `EmailOtpMail.php` - OTP codes ✅
- `FundsAdjustedMail.php` - Balance adjustments ✅
- `KycApprovedMail.php` - KYC approval ✅
- `KycRejectedMail.php` - KYC rejection ✅
- `KycSubmittedMail.php` - KYC submitted ✅
- `NewTicketNotificationMail.php` - New support ticket ✅
- `NewsletterMail.php` - Newsletter ✅
- `PushNotificationMail.php` - Push notification (via email) ✅
- `TicketCreatedMail.php` - Ticket created ✅
- `TicketRepliedMail.php` - Ticket reply ✅
- `TicketStatusChangedMail.php` - Ticket status change ✅
- `TransferOtpMail.php` - Transfer OTP ✅
- `VoucherRedeemedMail.php` - Voucher redeemed ✅
- `WireTransferOtpMail.php` - Wire transfer OTP ✅

### Existing Notification Classes (app/Notifications/)
- `CustomResetPasswordNotification.php` - Password reset
- `GrantApprovedNotification.php` - Grant approved
- `GrantRejectedNotification.php` - Grant rejected
- `GrantDisbursedNotification.php` - Grant disbursed

### Admin Settings (ManageSettings.php)
Located at: `app/Filament/Pages/ManageSettings.php`

**Global Notification Settings:**
- `email_notifications` - Enable email notifications (default: true)
- `sms_notifications` - Enable SMS notifications (default: false)
- `push_notifications` - Enable push notifications (default: false)
- `transaction_alerts` - Enable transaction alerts (default: true)
- `login_alerts` - Enable login alerts (default: true)
- `marketing_emails` - Enable marketing emails (default: false)

### User-Level Settings (NotificationSetting Model)
Located at: `app/Models/NotificationSetting.php`

**Per-user notification preferences:**
- `type` - Notification type (e.g., 'transfer', 'login', 'deposit')
- `email` - Email enabled (boolean)
- `sms` - SMS enabled (boolean)
- `push` - Push enabled (boolean)

### User Permission Flags (User Model)
Located at: `app/Models/User.php`

**Feature-specific permissions:**
- `can_transfer` - Can make transfers
- `can_withdraw` - Can withdraw funds
- `can_deposit` - Can make deposits
- `can_request_loan` - Can request loans
- `can_request_card` - Can request cards
- `can_apply_grant` - Can apply for grants
- `can_send_wire_transfer` - Wire transfer access
- `can_send_internal_transfer` - Internal transfer access
- `can_send_domestic_transfer` - Domestic transfer access
- `can_create_beneficiary` - Can create beneficiaries
- `skip_transfer_otp` - Skip OTP for transfers
- `skip_email_otp` - Skip email OTP

### Admin User Actions (UsersTable.php)
Located at: `app/Filament/Resources/Users/Tables/UsersTable.php`

**Admin can send:**
1. **Email** - Custom email to specific user
2. **Push Notification** - Push notification to user (via email)
3. **Newsletter** - Newsletter templates (weekly, monthly, promo)

---

## Implementation Plan

### Phase 1: Database Notifications Table
Create Laravel's default notifications table for database storage.

```bash
php artisan notifications:table
php artisan migrate
```

### Phase 2: Create Notification Classes

#### Transfer Notifications
- [ ] `TransferInitiatedNotification` - When transfer is created
- [ ] `TransferCompletedNotification` - When transfer completes
- [ ] `TransferFailedNotification` - When transfer fails
- [ ] `TransferReceivedNotification` - When user receives transfer

#### Deposit Notifications
- [ ] `DepositReceivedNotification` - Deposit received
- [ ] `DepositApprovedNotification` - Check deposit approved
- [ ] `DepositRejectedNotification` - Deposit rejected
- [ ] `DepositOnHoldNotification` - Deposit on hold

#### Loan Notifications
- [ ] `LoanApplicationSubmittedNotification` - Application submitted
- [ ] `LoanApprovedNotification` - Loan approved
- [ ] `LoanRejectedNotification` - Loan rejected
- [ ] `LoanDisbursedNotification` - Loan funds disbursed
- [ ] `LoanPaymentDueNotification` - Payment reminder
- [ ] `LoanPaymentReceivedNotification` - Payment received
- [ ] `LoanOverdueNotification` - Payment overdue

#### Card Notifications
- [ ] `CardIssuedNotification` - Card issued
- [ ] `CardShippedNotification` - Card shipped
- [ ] `CardActivatedNotification` - Card activated
- [ ] `CardBlockedNotification` - Card blocked/frozen

#### Security Notifications
- [ ] `NewLoginNotification` - New device/location login
- [ ] `SuspiciousActivityNotification` - Unusual activity
- [ ] `PasswordChangedNotification` - Password changed
- [ ] `PinChangedNotification` - Transaction PIN changed
- [ ] `TwoFactorEnabledNotification` - 2FA enabled
- [ ] `TwoFactorDisabledNotification` - 2FA disabled

#### Account Notifications
- [ ] `AccountCreatedNotification` - New account created
- [ ] `AccountUpdatedNotification` - Account details updated
- [ ] `LowBalanceNotification` - Balance below threshold
- [ ] `FundsAdjustedNotification` - Admin balance adjustment

#### Reward/Voucher Notifications
- [ ] `RewardEarnedNotification` - Reward earned
- [ ] `VoucherReceivedNotification` - Voucher received

#### Other Notifications
- [ ] `MoneyRequestReceivedNotification` - Money request received
- [ ] `MoneyRequestApprovedNotification` - Request approved
- [ ] `ExchangeCompletedNotification` - Currency exchange done
- [ ] `WithdrawalCompletedNotification` - Withdrawal processed

### Phase 3: Update NotificationPanel.vue

#### Desktop View (PrimeVue)
- Use existing `OverlayPanel` component
- Keep current structure but connect to real data

#### Mobile View (Glassmorphism)
- Detached floating panel
- Liquid glass effect like bottom nav
- Slide-in animation from right
- Touch-friendly interactions

### Phase 4: Backend API Endpoints

```php
// routes/web.php or routes/api.php
Route::middleware('auth')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/clear-all', [NotificationController::class, 'clearAll']);
});
```

### Phase 5: Real-time Updates (Optional)
- Laravel Echo + Pusher/Ably for real-time
- Or polling every 30 seconds as fallback

### Phase 6: User Notification Preferences Page
Create a Settings > Notifications page where users can:
- Toggle email notifications per category
- Toggle push notifications per category
- Set quiet hours
- Manage notification history

---

## Notification Categories

| Category | Types | Default Email | Default Push |
|----------|-------|---------------|--------------|
| **Transfers** | sent, received, failed | ✅ | ✅ |
| **Deposits** | received, approved, rejected | ✅ | ✅ |
| **Withdrawals** | initiated, completed, failed | ✅ | ✅ |
| **Security** | login, password_change, 2fa | ✅ | ✅ |
| **Cards** | issued, shipped, blocked | ✅ | ❌ |
| **Loans** | approved, rejected, payment_due | ✅ | ✅ |
| **Grants** | approved, rejected, disbursed | ✅ | ❌ |
| **Support** | ticket_reply, ticket_closed | ✅ | ❌ |
| **Marketing** | promotions, newsletters | ❌ | ❌ |
| **System** | maintenance, updates | ✅ | ❌ |

---

## Priority Implementation Order

1. **Create notifications table migration** ⬅️ START HERE
2. **Create base Notification classes for transfers**
3. **Update NotificationPanel.vue with glassmorphism mobile view**
4. **Connect to real database notifications**
5. **Add notification sending to existing transaction controllers**
6. **Create user notification preferences page**
7. **Add remaining notification types**

---

## Files to Modify/Create

### New Files
- `database/migrations/xxxx_create_notifications_table.php`
- `app/Notifications/Transfers/*.php` (4 classes)
- `app/Notifications/Deposits/*.php` (4 classes)
- `app/Notifications/Loans/*.php` (7 classes)
- `app/Notifications/Security/*.php` (6 classes)
- `app/Notifications/Cards/*.php` (4 classes)
- `app/Http/Controllers/NotificationController.php` (web version)
- `resources/js/Pages/Settings/Notifications.vue`

### Files to Modify
- `resources/js/Components/Navigation/NotificationPanel.vue` - Add mobile glassmorphism
- `app/Http/Controllers/Transfers/*Controller.php` - Add notification triggers
- `app/Http/Controllers/Deposits/*Controller.php` - Add notification triggers
- `routes/web.php` - Add notification routes
- `app/Http/Controllers/DashboardController.php` - Pass notifications to frontend

---

## Next Steps

1. Run `php artisan notifications:table && php artisan migrate`
2. Start with NotificationPanel.vue redesign (glassmorphism mobile)
3. Create transfer notification classes
4. Wire up the backend to frontend

Ready to proceed?
