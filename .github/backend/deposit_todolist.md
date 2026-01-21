# Backend - Deposit Module Todo List

## Overview
Implementation tasks for all deposit types in Finora Bank.

---

## Check Deposit

### Models & Migrations
- [ ] Create `check_deposits` migration
- [ ] Create `CheckDeposit` model
- [ ] Add image storage fields
- [ ] Add approval status fields
- [ ] Add hold period configuration

### Controllers
- [ ] Create `CheckDepositController`
- [ ] Implement `initiate()` - Start check deposit
- [ ] Implement `upload()` - Handle check image upload
- [ ] Implement `verifyPin()` - Verify transaction PIN
- [ ] Implement `submit()` - Submit for approval
- [ ] Implement `status()` - Check deposit status

### Services
- [ ] Create `CheckDepositService`
- [ ] Implement image validation
- [ ] Implement OCR integration (optional)
- [ ] Implement approval workflow

### Storage
- [ ] Configure check images storage disk
- [ ] Implement secure file access

---

## Mobile Deposit (Payment Gateways)

### Stripe Integration
- [ ] Install Stripe PHP SDK
- [ ] Create `StripeService`
- [ ] Implement payment intent creation
- [ ] Implement webhook handling
- [ ] Implement refund handling
- [ ] Create `stripe_deposits` migration

### PayPal Integration
- [ ] Install PayPal SDK
- [ ] Create `PayPalService`
- [ ] Implement order creation
- [ ] Implement capture payment
- [ ] Implement webhook handling
- [ ] Create `paypal_deposits` migration

### Paystack Integration
- [ ] Install Paystack package
- [ ] Create `PaystackService`
- [ ] Implement transaction initialization
- [ ] Implement verification
- [ ] Implement webhook handling
- [ ] Create `paystack_deposits` migration

### Flutterwave Integration (Optional)
- [ ] Install Flutterwave SDK
- [ ] Create `FlutterwaveService`
- [ ] Implement payment flow
- [ ] Create migration

### Razorpay Integration (Optional)
- [ ] Install Razorpay SDK
- [ ] Create `RazorpayService`
- [ ] Implement payment flow
- [ ] Create migration

### Gateway Management
- [ ] Create `payment_gateways` configuration table
- [ ] Create `PaymentGateway` model
- [ ] Implement gateway enable/disable
- [ ] Implement gateway credentials management

### Controllers
- [ ] Create `MobileDepositController`
- [ ] Implement `initiate()` - Choose gateway
- [ ] Implement `verifyPin()` - Verify transaction PIN
- [ ] Implement `processStripe()` - Stripe payment flow
- [ ] Implement `processPayPal()` - PayPal payment flow
- [ ] Implement `processPaystack()` - Paystack payment flow
- [ ] Implement `webhook()` - Handle gateway webhooks
- [ ] Implement `success()` - Handle successful deposits
- [ ] Implement `cancel()` - Handle cancelled deposits

---

## Crypto Deposit

### Models & Migrations
- [ ] Create `crypto_wallets` migration (admin-managed)
- [ ] Create `CryptoWallet` model
- [ ] Create `crypto_deposits` migration
- [ ] Create `CryptoDeposit` model
- [ ] Create `cryptocurrencies` migration
- [ ] Create `Cryptocurrency` model

### Controllers
- [ ] Create `CryptoDepositController`
- [ ] Implement `initiate()` - Start crypto deposit
- [ ] Implement `getWalletAddress()` - Get deposit address
- [ ] Implement `verifyPin()` - Verify transaction PIN
- [ ] Implement `submit()` - Submit deposit claim
- [ ] Implement `status()` - Check verification status

### Services
- [ ] Create `CryptoDepositService`
- [ ] Implement QR code generation
- [ ] Implement manual verification workflow
- [ ] Implement crypto type management

### Supported Cryptocurrencies
- [ ] Bitcoin (BTC)
- [ ] Ethereum (ETH)
- [ ] USDT (TRC20)
- [ ] USDT (ERC20)
- [ ] USDC
- [ ] Custom cryptocurrencies (admin-defined)

---

## Shared Components

### Enums
- [ ] Create `DepositStatus` enum (pending, approved, rejected, completed)
- [ ] Create `DepositType` enum (check, mobile, crypto)
- [ ] Create `PaymentGatewayType` enum

### Models
- [ ] Create base `Deposit` model
- [ ] Create `deposits` polymorphic table

### Services
- [ ] Create `DepositService` base class
- [ ] Implement balance credit after approval

### Events
- [ ] Create `DepositSubmitted` event
- [ ] Create `DepositApproved` event
- [ ] Create `DepositRejected` event
- [ ] Create `DepositCompleted` event

### Notifications
- [ ] Create deposit confirmation email
- [ ] Create deposit approval email
- [ ] Create deposit rejection email

---

## Configuration

### Environment Variables
```
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=sandbox

PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
```

---

## Routes
- [ ] Define deposit routes in `routes/web.php`
- [ ] Define webhook routes (exclude CSRF)
- [ ] Add proper middleware

---

## Testing
- [ ] Write unit tests for deposit services
- [ ] Write feature tests for deposit flows
- [ ] Mock payment gateway responses
- [ ] Test webhook handling

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Check Deposit | 🔴 Not Started | 0% |
| Stripe Integration | 🔴 Not Started | 0% |
| PayPal Integration | 🔴 Not Started | 0% |
| Paystack Integration | 🔴 Not Started | 0% |
| Crypto Deposit | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
