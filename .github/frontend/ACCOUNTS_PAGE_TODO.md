# 📋 Accounts Page Implementation TODO

**Priority:** HIGH  
**Status:** 🔴 Not Started  
**Route:** `/accounts`  
**Layout:** DashboardLayout  
**Updated:** 2026-01-23

---

## 📄 Page Overview

The Accounts page displays all user bank accounts with detailed information, quick actions, and account management features.

---

## 🎯 Main Page Features (`/accounts`)

### 1. **Accounts Overview Section**
- [ ] Total balance card (all accounts combined)
- [ ] Currency selector (multi-currency support)
- [ ] Quick stats: Active accounts, Total deposits, Total withdrawals

### 2. **Account Cards Grid**
- [ ] Display all user accounts in responsive grid
- [ ] Each card shows:
  - [ ] Account type (Savings, Checking, Business, Fixed Deposit)
  - [ ] Account number (masked: ****1234)
  - [ ] Account name/label
  - [ ] Current balance with currency
  - [ ] Account status badge (Active, Frozen, Pending)
  - [ ] Quick action buttons
- [ ] Hover effects and animations
- [ ] Click to view account details

### 3. **Quick Actions per Account**
- [ ] View Details button
- [ ] Transfer Money (redirect to transfers with account pre-selected)
- [ ] Download Statement
- [ ] Freeze/Unfreeze Account
- [ ] Set as Primary Account

### 4. **Create New Account**
- [ ] "Open New Account" button (prominent)
- [ ] Modal form with:
  - [ ] Account type selection (dropdown)
  - [ ] Account name/label (optional)
  - [ ] Initial deposit amount (optional)
  - [ ] Currency selection
  - [ ] Terms and conditions checkbox
  - [ ] Transaction PIN verification
  - [ ] Submit and success feedback

### 5. **Filters & Search**
- [ ] Filter by account type
- [ ] Filter by account status
- [ ] Search by account number or name
- [ ] Sort by balance, date created, account type

### 6. **Empty State**
- [ ] Display when no accounts exist
- [ ] "Open Your First Account" CTA
- [ ] Onboarding message

---

## 📊 Sub-Pages

### **Transaction History Page** (`/transactions`)
*See: TRANSACTION_HISTORY_TODO.md*

### **Beneficiaries Page** (`/beneficiaries`)
*See: BENEFICIARIES_TODO.md*

---

## 🎨 UI/UX Components to Create

### Vue Components
- [ ] `AccountCard.vue` - Individual account card component
  - Props: account object, showActions, compact mode
  - Emits: view-details, transfer, freeze, download-statement
  
- [ ] `AccountStats.vue` - Overview statistics component
  - Props: accounts array, currency
  - Displays: total balance, account count, stats

- [ ] `CreateAccountModal.vue` - New account creation modal
  - Form validation
  - PIN verification step
  - Success/Error handling

- [ ] `AccountFilters.vue` - Filter and search component
  - Props: accountTypes, statuses
  - Emits: filter-change, search

- [ ] `AccountBalance.vue` - Balance display with currency
  - Props: amount, currency, showSymbol, size
  - Handles formatting and animations

### PrimeVue Components Used
- [ ] Card (for account cards)
- [ ] Button (quick actions)
- [ ] Dialog (create account modal)
- [ ] InputText (search, account name)
- [ ] Dropdown (account type, currency)
- [ ] Badge (account status)
- [ ] Checkbox (terms and conditions)
- [ ] Skeleton (loading states)

---

## 🔌 Backend Integration

### API Endpoints Needed
- [ ] `GET /api/accounts` - Fetch user accounts
- [ ] `POST /api/accounts` - Create new account
- [ ] `GET /api/accounts/{id}` - Get account details
- [ ] `PATCH /api/accounts/{id}/freeze` - Freeze account
- [ ] `PATCH /api/accounts/{id}/unfreeze` - Unfreeze account
- [ ] `PATCH /api/accounts/{id}/set-primary` - Set as primary
- [ ] `GET /api/accounts/{id}/statement` - Download statement (PDF)
- [ ] `POST /api/accounts/verify-pin` - Verify transaction PIN

### Inertia Props Required
```javascript
{
    accounts: Array,           // User's bank accounts
    accountTypes: Array,       // Available account types
    currencies: Array,         // Supported currencies
    stats: {
        totalBalance: Number,
        activeAccounts: Number,
        primaryAccount: Object
    }
}
```

---

## 🔐 Security Features

- [ ] Transaction PIN required for:
  - Creating new account
  - Freezing/Unfreezing account
  - Setting primary account
- [ ] Mask account numbers (show last 4 digits only)
- [ ] Rate limiting on account creation
- [ ] Email notification on account status changes

---

## 📱 Responsive Design

- [ ] Desktop: 3-column grid for account cards
- [ ] Tablet: 2-column grid
- [ ] Mobile: Single column, stacked cards
- [ ] Touch-friendly buttons and cards
- [ ] Swipe gestures for quick actions (mobile)

---

## ✅ Testing Checklist

- [ ] View all accounts
- [ ] Create new account (all types)
- [ ] Filter accounts by type and status
- [ ] Search accounts by number/name
- [ ] Freeze/unfreeze account
- [ ] Set primary account
- [ ] Download statement
- [ ] Navigate to transfer with pre-selected account
- [ ] Empty state display
- [ ] Loading states
- [ ] Error handling (network, validation)
- [ ] PIN verification flow
- [ ] Mobile responsiveness

---

## 🎬 Implementation Order

1. **Phase 1: Basic Display** ✅
   - Create page route and layout
   - Fetch and display accounts
   - AccountCard component
   - AccountStats component
   - Loading and empty states

2. **Phase 2: Account Creation** ✅
   - CreateAccountModal component
   - Form validation
   - PIN verification integration
   - Success/Error handling

3. **Phase 3: Account Actions** ✅
   - Freeze/Unfreeze functionality
   - Set primary account
   - Download statement
   - Quick transfer integration

4. **Phase 4: Filters & Search** ✅
   - AccountFilters component
   - Filter by type and status
   - Search functionality
   - Sort options

5. **Phase 5: Polish** ✅
   - Animations and transitions
   - Mobile optimization
   - Error boundaries
   - Accessibility improvements

---

## 📝 Notes

- Account numbers should be auto-generated on backend
- Default account type: Savings
- Primary account is used for default transactions
- Account creation may require KYC verification (check user status)
- Fixed deposit accounts may have restrictions (no withdrawals before maturity)
- Business accounts may require additional documentation

---

## 🔗 Related Files

- **Route:** `routes/web.php` - `/accounts` route
- **Controller:** `app/Http/Controllers/AccountController.php`
- **Model:** `app/Models/BankAccount.php`
- **Page:** `resources/js/Pages/Accounts/Index.vue`
- **Components:** `resources/js/Components/Accounts/`
- **Store:** `resources/js/Stores/accounts.js` (Pinia)

---

**Next Page After Completion:** [Transaction History](/transactions) → See `TRANSACTION_HISTORY_TODO.md`
