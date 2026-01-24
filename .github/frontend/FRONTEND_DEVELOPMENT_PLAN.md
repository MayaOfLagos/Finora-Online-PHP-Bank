npm install primevue @primevue/themes primeicons# Finora Bank - Frontend Development Plan

> **Last Updated:** January 23, 2026  
> **Stack:** Vue 3 + Inertia.js + Tailwind CSS 4 + Pinia  
> **Priority:** Mobile-First, Clean UI/UX, Fast & Elegant Experience

---

## 🎯 Development Approach

### Recommended UI Framework: **PrimeVue**

After evaluating multiple options for a banking application, **PrimeVue** is the recommended UI framework for Finora Bank:

| Framework | Pros | Cons | Verdict |
|-----------|------|------|---------|
| **PrimeVue** | 90+ components, excellent accessibility, built-in themes, DataTable/Charts, Mobile-responsive | Larger bundle (tree-shakeable) | ✅ **Recommended** |
| Vuetify 3 | Material Design, comprehensive | Heavy, opinionated design | Good alternative |
| Naive UI | Modern, lightweight, TypeScript | Less mature ecosystem | Consider for simpler apps |
| Headless UI | Fully customizable, lightweight | Requires more custom styling | For custom designs |
| Element Plus | Feature-rich, stable | Chinese documentation primary | Enterprise alternative |

### Why PrimeVue for Banking?

1. **Financial Components** - Excellent DataTables, Charts, InputNumber with currency formatting
2. **Accessibility** - WCAG 2.1 compliant (critical for banking)
3. **Mobile-First** - All components are responsive out-of-the-box
4. **Theme System** - Professional themes (Lara, Aura) suitable for banking
5. **Form Handling** - Advanced validation, input masks for account numbers, currencies
6. **Security UX** - Built-in password meters, OTP inputs, confirmation dialogs

---

## 🏗️ Architecture

```
resources/js/
├── app.js                    # Vue/Inertia bootstrap
├── bootstrap.js              # Axios, global configs
├── Components/
│   ├── Common/               # Shared components
│   │   ├── AppLogo.vue
│   │   ├── LoadingSpinner.vue
│   │   ├── EmptyState.vue
│   │   ├── ConfirmDialog.vue
│   │   ├── StatusBadge.vue
│   │   └── CurrencyDisplay.vue
│   ├── Forms/                # Form components
│   │   ├── OtpInput.vue
│   │   ├── PinInput.vue
│   │   ├── AmountInput.vue
│   │   ├── AccountSelector.vue
│   │   └── BeneficiarySelector.vue
│   ├── Cards/                # Card UI components
│   │   ├── AccountCard.vue
│   │   ├── TransactionCard.vue
│   │   ├── BankCard.vue
│   │   └── StatCard.vue
│   ├── Navigation/           # Nav components
│   │   ├── Sidebar.vue
│   │   ├── BottomNav.vue     # Mobile bottom navigation
│   │   ├── Breadcrumb.vue
│   │   └── UserMenu.vue
│   └── Modals/               # Modal components
│       ├── TransferConfirm.vue
│       ├── OtpVerify.vue
│       └── SuccessModal.vue
├── Layouts/
│   ├── DashboardLayout.vue   # Main authenticated layout
│   ├── AuthLayout.vue        # Login/Register layout
│   └── GuestLayout.vue       # Public pages
├── Pages/                    # Inertia pages (routes)
│   ├── Auth/
│   ├── Dashboard/
│   ├── Accounts/
│   ├── Transfers/
│   ├── Deposits/
│   ├── Loans/
│   ├── Cards/
│   ├── Grants/
│   ├── Support/
│   ├── Settings/
│   └── Profile/
├── Stores/                   # Pinia stores
│   ├── auth.js
│   ├── accounts.js
│   ├── notifications.js
│   └── settings.js
├── Composables/              # Vue composables
│   ├── useAuth.js
│   ├── useCurrency.js
│   ├── useOtp.js
│   └── useTransfer.js
└── Utils/                    # Utility functions
    ├── formatters.js
    ├── validators.js
    └── constants.js
```

---

## 📱 Mobile-First Design Principles

### 1. Bottom Navigation for Mobile
```
┌────────────────────────────────┐
│                                │
│         Content Area           │
│                                │
├────────────────────────────────┤
│  🏠    💳    ↔️    👤    ≡    │
│ Home  Cards  Send  Profile More│
└────────────────────────────────┘
```

### 2. Touch-Friendly Interactions
- Minimum tap target: 44x44px
- Swipe gestures for transactions list
- Pull-to-refresh on lists
- Haptic feedback on actions (where supported)

### 3. Progressive Enhancement
- Core functionality works without JS animations
- Enhanced experience with animations
- Offline support for viewing balances (cached)

---

## ✅ Feature Implementation Checklist

### Phase 1: Core Foundation (Week 1-2)

#### Authentication & Security
- [ ] **Login Page** - Email/Password with remember me
- [ ] **Register Page** - Multi-step registration
- [ ] **Forgot Password** - Email reset flow
- [ ] **2FA Setup** - TOTP app authentication
- [ ] **2FA Verification** - Code input during login
- [ ] **Email OTP** - 6-digit email verification
- [ ] **Transaction PIN** - Setup and change PIN
- [ ] **Session Management** - View/revoke sessions

#### Dashboard
- [ ] **Account Overview** - Balance cards with charts
- [ ] **Quick Actions** - Transfer, Pay, Deposit shortcuts
- [ ] **Recent Transactions** - Last 10 transactions
- [ ] **Notifications Bell** - Unread notifications count
- [ ] **Account Switcher** - Switch between accounts

### Phase 2: Accounts & Transfers (Week 3-4)

#### Bank Accounts
- [ ] **Account List** - All user accounts with balances
- [ ] **Account Details** - Full account info, statements
- [ ] **Account Statements** - Downloadable PDF/CSV
- [ ] **Transaction History** - Filterable, searchable
- [ ] **Account Settings** - Nickname, notifications

#### Transfer Module

##### Wire Transfer (International)
Security Flow: `Transaction PIN → IMF Code → Tax Code → COT Code → Email OTP → Success`
- [ ] Beneficiary selection (new/existing)
- [ ] SWIFT/BIC code input
- [ ] Amount & currency selection
- [ ] Fee preview
- [ ] Multi-step verification wizard
- [ ] Transfer confirmation receipt

##### Internal Transfer (Within Bank)
Security Flow: `Transaction PIN → Email OTP → Success`
- [ ] User search by account/email
- [ ] Instant transfer
- [ ] Quick transfer to recent recipients

##### Domestic Transfer (Local Banks)
Security Flow: `Transaction PIN → Email OTP → Success`
- [ ] Bank selection from list
- [ ] Account number validation
- [ ] Routing number input
- [ ] Processing time display

##### Account-to-Account Transfer
Security Flow: `Transaction PIN → Email OTP → Success`
- [ ] Own accounts selector
- [ ] Quick transfer between accounts
- [ ] Scheduled transfers
- [ ] Recurring transfers setup

### Phase 3: Deposits (Week 5-6)

#### Check Deposit
- [ ] Camera capture / file upload
- [ ] Check preview & crop
- [ ] Amount confirmation
- [ ] Deposit status tracking

#### Mobile Deposit (Payment Gateways)
Security: `Transaction PIN → Success`
- [ ] **Stripe** - Card payment integration
- [ ] **PayPal** - PayPal checkout
- [ ] **Paystack** - African payment gateway
- [ ] Gateway selection UI
- [ ] Amount input with limits
- [ ] Payment confirmation

#### Crypto Deposit
- [ ] Cryptocurrency selection (BTC, ETH, USDT, USDC)
- [ ] Wallet address display with QR code
- [ ] Copy address functionality
- [ ] Deposit history & status
- [ ] Network selection (ERC20, TRC20)

### Phase 4: Loans (Week 7-8)

#### Loan Management
- [ ] **Loan Calculator** - Interactive calculator
- [ ] **Loan Application** - Multi-step form
- [ ] **Document Upload** - Required documents
- [ ] **Application Status** - Track progress
- [ ] **Active Loans** - Current loans list
- [ ] **Repayment Schedule** - Payment timeline
- [ ] **Make Payment** - Pay loan installment
- [ ] **Loan History** - Past loans

#### Loan Types Supported
- Personal Loans
- Business Loans
- Mortgage Loans
- Auto Loans

### Phase 5: Cards (Week 9-10)

#### Card Management
- [ ] **My Cards** - Virtual/Physical cards list
- [ ] **Card Details** - Full card view with CVV reveal
- [ ] **Request Card** - New card application
- [ ] **Card Settings**
  - [ ] Set spending limits
  - [ ] Enable/disable online transactions
  - [ ] Enable/disable international transactions
  - [ ] Freeze/Unfreeze card
- [ ] **PIN Management** - View/Change PIN
- [ ] **Card Transactions** - Card-specific history
- [ ] **Card Statements** - Monthly statements

### Phase 6: Grants (Week 11)

#### Grant Module
- [ ] **Available Grants** - Browse grant programs
- [ ] **Grant Details** - Eligibility, requirements
- [ ] **Apply for Grant** - Application form
- [ ] **Upload Documents** - Supporting documents
- [ ] **Application Status** - Track progress
- [ ] **My Grants** - Approved grants, disbursements

### Phase 7: Support & Profile (Week 12)

#### Support System
- [ ] **Create Ticket** - New support request
- [ ] **My Tickets** - Ticket list with status
- [ ] **Ticket Chat** - Real-time messaging
- [ ] **FAQ Section** - Searchable FAQs
- [ ] **Knowledge Base** - Help articles

#### Beneficiary Management
- [ ] **Beneficiary List** - Saved beneficiaries
- [ ] **Add Beneficiary** - New beneficiary form
- [ ] **Edit/Delete** - Manage beneficiaries
- [ ] **Quick Select** - In transfer flows

#### Profile & Settings
- [ ] **Profile Information** - View/Edit profile
- [ ] **Change Password** - Password update
- [ ] **Security Settings** - 2FA, PIN management
- [ ] **Notification Preferences** - Email, SMS, Push
- [ ] **KYC Verification** - Upload documents
- [ ] **Login Activity** - Recent logins
- [ ] **Theme Toggle** - Light/Dark mode

### Phase 8: Additional Features (Week 13-14)

#### Money Requests
- [ ] **Request Money** - Send request to others
- [ ] **Pending Requests** - Incoming requests
- [ ] **Request History** - Past requests

#### Currency Exchange
- [ ] **Exchange Calculator** - Live rates
- [ ] **Exchange Money** - Convert currencies
- [ ] **Exchange History** - Past exchanges

#### Rewards & Vouchers
- [ ] **My Rewards** - Available rewards
- [ ] **Redeem Voucher** - Enter voucher code
- [ ] **Voucher History** - Used vouchers

#### Tax Refunds
- [ ] **Tax Refund Status** - Track refunds
- [ ] **ID.me Verification** - Identity verification

#### Withdrawals
- [ ] **Withdrawal Request** - Request withdrawal
- [ ] **Withdrawal History** - Past withdrawals

---

## 🎨 Design System

### Color Palette (Banking-Appropriate)

```css
/* Primary - Trust & Stability */
--primary-50: #EEF2FF;
--primary-100: #E0E7FF;
--primary-500: #6366F1;  /* Indigo */
--primary-600: #4F46E5;
--primary-700: #4338CA;

/* Success - Positive Actions */
--success-500: #22C55E;
--success-600: #16A34A;

/* Warning - Caution */
--warning-500: #F59E0B;
--warning-600: #D97706;

/* Danger - Alerts */
--danger-500: #EF4444;
--danger-600: #DC2626;

/* Neutral - Text & Backgrounds */
--neutral-50: #F9FAFB;
--neutral-100: #F3F4F6;
--neutral-500: #6B7280;
--neutral-900: #111827;
```

### Typography
- **Headings:** Inter (or system font stack)
- **Body:** Inter
- **Monospace:** JetBrains Mono (for account numbers, amounts)

### Spacing Scale
```
4px, 8px, 12px, 16px, 24px, 32px, 48px, 64px
```

---

## 🔐 Security UX Patterns

### Transaction PIN Entry
```vue
<!-- 4-6 digit masked input -->
<PinInput 
  :length="6" 
  masked 
  @complete="verifyPin"
/>
```

### OTP Verification
```vue
<!-- Auto-advance, auto-submit -->
<OtpInput 
  :length="6" 
  auto-focus 
  auto-submit
  :countdown="300"
  @complete="verifyOtp"
  @resend="resendOtp"
/>
```

### Wire Transfer Verification Flow
```
Step 1: Enter Transfer Details
Step 2: Enter Transaction PIN
Step 3: Enter IMF Code (Admin provided)
Step 4: Enter Tax Code (Admin provided)
Step 5: Enter COT Code (Admin provided)
Step 6: Enter Email OTP
Step 7: Success + Receipt
```

---

## 📦 Package Dependencies to Add

```json
{
  "dependencies": {
    "primevue": "^4.x",
    "@primevue/themes": "^4.x",
    "primeicons": "^7.x",
    "chart.js": "^4.x",
    "@vueuse/core": "^11.x",
    "date-fns": "^3.x",
    "qrcode.vue": "^3.x",
    "vue-the-mask": "^0.11.x"
  }
}
```

---

## 🚀 Getting Started

### 1. Install PrimeVue
```bash
npm install primevue @primevue/themes primeicons
```

### 2. Configure in app.js
```javascript
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '.dark',
                        cssLayer: false
                    }
                }
            })
            .mount(el);
    },
});
```

---

## 📋 Backend API Endpoints Needed

Based on admin features, these API endpoints need to be created:

### Authentication
- `POST /api/auth/login`
- `POST /api/auth/register`
- `POST /api/auth/logout`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`
- `POST /api/auth/verify-2fa`
- `POST /api/auth/verify-email-otp`

### Accounts
- `GET /api/accounts`
- `GET /api/accounts/{id}`
- `GET /api/accounts/{id}/transactions`
- `GET /api/accounts/{id}/statements`

### Transfers
- `POST /api/transfers/wire`
- `POST /api/transfers/internal`
- `POST /api/transfers/domestic`
- `POST /api/transfers/account-to-account`
- `POST /api/transfers/verify-pin`
- `POST /api/transfers/verify-otp`
- `POST /api/transfers/verify-code` (IMF, Tax, COT)

### Deposits
- `POST /api/deposits/check`
- `POST /api/deposits/mobile`
- `GET /api/deposits/crypto/wallets`
- `GET /api/deposits/history`

### Loans
- `GET /api/loans`
- `GET /api/loans/types`
- `POST /api/loans/apply`
- `POST /api/loans/{id}/documents`
- `GET /api/loans/{id}/repayments`
- `POST /api/loans/{id}/pay`

### Cards
- `GET /api/cards`
- `POST /api/cards/request`
- `PATCH /api/cards/{id}/settings`
- `POST /api/cards/{id}/freeze`
- `GET /api/cards/{id}/transactions`

### Support
- `GET /api/support/tickets`
- `POST /api/support/tickets`
- `GET /api/support/tickets/{id}/messages`
- `POST /api/support/tickets/{id}/messages`
- `GET /api/support/faqs`

### Profile
- `GET /api/profile`
- `PATCH /api/profile`
- `POST /api/profile/change-password`
- `POST /api/profile/change-pin`
- `GET /api/profile/sessions`
- `DELETE /api/profile/sessions/{id}`

---

## 📊 Performance Targets

| Metric | Target | Tool |
|--------|--------|------|
| First Contentful Paint | < 1.5s | Lighthouse |
| Largest Contentful Paint | < 2.5s | Lighthouse |
| Time to Interactive | < 3.5s | Lighthouse |
| Cumulative Layout Shift | < 0.1 | Lighthouse |
| Bundle Size (gzipped) | < 200KB | Vite |

---

## 🧪 Testing Strategy

- **Unit Tests:** Vitest for components and composables
- **E2E Tests:** Playwright for critical user flows
- **Visual Regression:** Percy or Chromatic

### Critical Flows to Test
1. Login → Dashboard
2. Transfer Money (all types)
3. Deposit funds
4. Apply for loan
5. Card management
6. Support ticket creation

---

## 📅 Timeline Summary

| Phase | Duration | Features |
|-------|----------|----------|
| Phase 1 | Week 1-2 | Auth, Dashboard |
| Phase 2 | Week 3-4 | Accounts, Transfers |
| Phase 3 | Week 5-6 | Deposits |
| Phase 4 | Week 7-8 | Loans |
| Phase 5 | Week 9-10 | Cards |
| Phase 6 | Week 11 | Grants |
| Phase 7 | Week 12 | Support, Profile |
| Phase 8 | Week 13-14 | Additional Features |

**Total Estimated Time: 14 weeks**

---

*This document should be updated as development progresses. Check off completed features in the checklist above.*
