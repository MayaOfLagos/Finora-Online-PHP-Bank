# Frontend - Deposit Module Todo List

## Overview
Vue 3 + Inertia.js implementation for all deposit types.

---

## Shared Components

### UI Components
- [ ] Create `DepositCard.vue` - Deposit type selection card
- [ ] Create `DepositAmountInput.vue` - Amount input with validation
- [ ] Create `DepositStatus.vue` - Deposit status indicator
- [ ] Create `DepositSummary.vue` - Deposit details summary

### Layout Components
- [ ] Create `DepositLayout.vue` - Deposit pages layout

---

## Check Deposit

### Pages
- [ ] Create `pages/Deposit/Check/Index.vue` - Check deposit home
- [ ] Create `pages/Deposit/Check/Upload.vue` - Upload check images
- [ ] Create `pages/Deposit/Check/Review.vue` - Review before submit
- [ ] Create `pages/Deposit/Check/VerifyPin.vue` - PIN verification
- [ ] Create `pages/Deposit/Check/Success.vue` - Success page
- [ ] Create `pages/Deposit/Check/Status.vue` - Check status

### Components
- [ ] Create `CheckUploader.vue` - Check image uploader
- [ ] Create `CheckPreview.vue` - Preview uploaded check
- [ ] Create `CheckAmountInput.vue` - Enter check amount
- [ ] Create `CameraCapture.vue` - Camera for mobile capture
- [ ] Create `ImageCropper.vue` - Crop check image

### Features
- [ ] Implement drag & drop upload
- [ ] Implement camera capture (mobile)
- [ ] Implement image validation
- [ ] Implement image compression

---

## Mobile Deposit (Payment Gateways)

### Pages
- [ ] Create `pages/Deposit/Mobile/Index.vue` - Gateway selection
- [ ] Create `pages/Deposit/Mobile/Amount.vue` - Enter amount
- [ ] Create `pages/Deposit/Mobile/VerifyPin.vue` - PIN verification
- [ ] Create `pages/Deposit/Mobile/Process.vue` - Processing page
- [ ] Create `pages/Deposit/Mobile/Success.vue` - Success page
- [ ] Create `pages/Deposit/Mobile/Failed.vue` - Failure page

### Stripe Components
- [ ] Create `StripePayment.vue` - Stripe payment form
- [ ] Create `StripeCardInput.vue` - Stripe Elements card
- [ ] Integrate Stripe.js

### PayPal Components
- [ ] Create `PayPalPayment.vue` - PayPal payment
- [ ] Create `PayPalButton.vue` - PayPal button
- [ ] Integrate PayPal SDK

### Paystack Components
- [ ] Create `PaystackPayment.vue` - Paystack payment
- [ ] Create `PaystackButton.vue` - Paystack button
- [ ] Integrate Paystack SDK

### Gateway Components
- [ ] Create `GatewaySelector.vue` - Select payment gateway
- [ ] Create `GatewayCard.vue` - Gateway display card
- [ ] Create `GatewayStatus.vue` - Gateway availability

### Store (Pinia)
- [ ] Create `stores/mobileDeposit.js`
- [ ] Manage payment state
- [ ] Handle gateway callbacks

---

## Crypto Deposit

### Pages
- [ ] Create `pages/Deposit/Crypto/Index.vue` - Crypto deposit home
- [ ] Create `pages/Deposit/Crypto/Select.vue` - Select cryptocurrency
- [ ] Create `pages/Deposit/Crypto/Wallet.vue` - Display wallet address
- [ ] Create `pages/Deposit/Crypto/Confirm.vue` - Confirm deposit
- [ ] Create `pages/Deposit/Crypto/VerifyPin.vue` - PIN verification
- [ ] Create `pages/Deposit/Crypto/Pending.vue` - Pending verification
- [ ] Create `pages/Deposit/Crypto/Success.vue` - Success page

### Components
- [ ] Create `CryptoSelector.vue` - Cryptocurrency dropdown
- [ ] Create `CryptoCard.vue` - Crypto type card
- [ ] Create `WalletAddress.vue` - Display wallet address
- [ ] Create `QRCode.vue` - QR code generator
- [ ] Create `CopyButton.vue` - Copy to clipboard button
- [ ] Create `CryptoAmountInput.vue` - Crypto amount input
- [ ] Create `NetworkSelector.vue` - Network selection (ERC20/TRC20)

### Features
- [ ] Generate QR codes for wallet addresses
- [ ] Copy address functionality
- [ ] Network warning messages
- [ ] Deposit instructions display

### Store (Pinia)
- [ ] Create `stores/cryptoDeposit.js`

---

## Deposit History

### Pages
- [ ] Create `pages/Deposit/History/Index.vue` - All deposits
- [ ] Create `pages/Deposit/History/Show.vue` - Deposit details

### Components
- [ ] Create `DepositHistoryTable.vue` - History data table
- [ ] Create `DepositHistoryFilters.vue` - Filter by type/status
- [ ] Create `DepositHistoryItem.vue` - Single deposit row
- [ ] Create `DepositReceipt.vue` - Printable receipt

---

## Styling

### Deposit Theme
- [ ] Gateway logos and branding
- [ ] Crypto icons
- [ ] Status color coding
- [ ] Responsive design

---

## Form Validation

- [ ] Amount validation
- [ ] File size/type validation
- [ ] Required fields validation

---

## User Experience

### Loading States
- [ ] Payment processing overlay
- [ ] Upload progress indicator
- [ ] Skeleton loaders

### Error Handling
- [ ] Payment failure handling
- [ ] Upload failure handling
- [ ] Network error handling

---

## External SDK Integration

### Stripe
```javascript
// stripe.js integration
- [ ] Load Stripe.js
- [ ] Initialize Stripe Elements
- [ ] Handle card tokenization
- [ ] Handle payment confirmation
```

### PayPal
```javascript
// PayPal SDK integration
- [ ] Load PayPal SDK
- [ ] Render PayPal buttons
- [ ] Handle order approval
- [ ] Handle capture
```

### Paystack
```javascript
// Paystack integration
- [ ] Load Paystack inline
- [ ] Initialize payment
- [ ] Handle callback
```

---

## Testing

- [ ] Unit tests for components
- [ ] Mock payment gateway tests
- [ ] Upload functionality tests
- [ ] QR code generation tests

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Shared Components | 🔴 Not Started | 0% |
| Check Deposit | 🔴 Not Started | 0% |
| Stripe Integration | 🔴 Not Started | 0% |
| PayPal Integration | 🔴 Not Started | 0% |
| Paystack Integration | 🔴 Not Started | 0% |
| Crypto Deposit | 🔴 Not Started | 0% |
| Deposit History | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
