# 📋 Beneficiaries Page Implementation TODO

**Priority:** HIGH  
**Status:** 🔴 Not Started  
**Route:** `/beneficiaries`  
**Parent:** Accounts Section  
**Layout:** DashboardLayout  
**Updated:** 2026-01-23

---

## 📄 Page Overview

The Beneficiaries page allows users to manage saved beneficiaries for quick and easy transfers.

---

## 🎯 Main Features

### 1. **Beneficiaries Grid/List**
- [ ] Display all saved beneficiaries
- [ ] Each beneficiary card shows:
  - [ ] Beneficiary name/nickname
  - [ ] Bank name (with logo if available)
  - [ ] Account number (masked: ****1234)
  - [ ] Account type badge
  - [ ] Transfer type (Internal, Domestic, Wire)
  - [ ] Quick transfer button
  - [ ] Edit and Delete actions
- [ ] Grid view (desktop) and list view (mobile)
- [ ] Alphabetical sorting
- [ ] Recently used section (top 5)

### 2. **Add New Beneficiary**
- [ ] "Add Beneficiary" button (prominent)
- [ ] Multi-step modal form:
  
  **Step 1: Select Transfer Type**
  - [ ] Internal Transfer (within Finora Bank)
  - [ ] Domestic Transfer (local banks)
  - [ ] Wire Transfer (international)
  
  **Step 2: Beneficiary Details**
  - [ ] Full name (required)
  - [ ] Nickname (optional, for easy identification)
  - [ ] Bank name (dropdown for domestic, input for wire)
  - [ ] Account number (required)
  - [ ] Bank routing number (domestic/wire only)
  - [ ] SWIFT/BIC code (wire only)
  - [ ] Bank address (wire only)
  - [ ] Country (wire only)
  
  **Step 3: Verification**
  - [ ] Review entered details
  - [ ] Transaction PIN verification
  - [ ] Add beneficiary button
  
- [ ] Form validation (real-time)
- [ ] Success feedback with animation

### 3. **Edit Beneficiary**
- [ ] Click edit icon on beneficiary card
- [ ] Same modal as "Add" but pre-filled
- [ ] Allow editing:
  - Nickname
  - Bank details (if not verified)
- [ ] Transaction PIN required
- [ ] Update confirmation

### 4. **Delete Beneficiary**
- [ ] Click delete icon
- [ ] Confirmation dialog
- [ ] Transaction PIN verification
- [ ] Success feedback
- [ ] Undo option (5 seconds)

### 5. **Quick Transfer**
- [ ] Click "Transfer" button on beneficiary card
- [ ] Redirect to appropriate transfer page:
  - `/transfers/internal` (if internal)
  - `/transfers/domestic` (if domestic)
  - `/transfers/wire` (if wire)
- [ ] Pre-fill beneficiary details on transfer form
- [ ] Pre-fill account number, name, bank

### 6. **Search & Filter**
- [ ] Search bar (by name, nickname, account number)
- [ ] Filter by transfer type (All, Internal, Domestic, Wire)
- [ ] Filter by bank (for domestic beneficiaries)
- [ ] Sort options:
  - Alphabetical (A-Z, Z-A)
  - Recently used
  - Recently added
  - Most transfers

### 7. **Beneficiary Details View**
- [ ] Click on beneficiary card to view full details
- [ ] Modal or side panel showing:
  - [ ] Full name
  - [ ] Nickname
  - [ ] Transfer type
  - [ ] Bank name and logo
  - [ ] Account number (full, with copy button)
  - [ ] Routing number (if applicable)
  - [ ] SWIFT code (if wire)
  - [ ] Country (if wire)
  - [ ] Date added
  - [ ] Total transfers to this beneficiary
  - [ ] Last transfer date and amount
  - [ ] Recent transactions (last 5)
- [ ] Quick transfer button
- [ ] Edit and delete buttons

### 8. **Empty State**
- [ ] Display when no beneficiaries exist
- [ ] "Add Your First Beneficiary" CTA
- [ ] Benefits explanation (faster transfers, no re-entry)

---

## 🎨 UI/UX Components to Create

### Vue Components
- [ ] `BeneficiaryCard.vue` - Individual beneficiary card
  - Props: beneficiary, showActions, compact
  - Emits: transfer, edit, delete, view-details

- [ ] `BeneficiariesGrid.vue` - Grid layout for beneficiaries
  - Props: beneficiaries, loading
  - Emits: card actions

- [ ] `AddBeneficiaryModal.vue` - Multi-step add beneficiary modal
  - Stepper component (PrimeVue)
  - Form validation
  - PIN verification
  - Emits: beneficiary-added

- [ ] `EditBeneficiaryModal.vue` - Edit beneficiary modal
  - Props: beneficiary
  - Form validation
  - PIN verification
  - Emits: beneficiary-updated

- [ ] `BeneficiaryDetailsModal.vue` - Full details view
  - Props: beneficiary, transactions
  - Emits: transfer, edit, delete, close

- [ ] `BeneficiaryFilters.vue` - Filter and search component
  - Props: transferTypes, banks
  - Emits: filter-change, search

- [ ] `DeleteBeneficiaryDialog.vue` - Delete confirmation
  - Props: beneficiary, show
  - PIN verification
  - Emits: confirm, cancel

- [ ] `RecentBeneficiaries.vue` - Recently used section
  - Props: beneficiaries (limited to 5)
  - Horizontal scroll on mobile

### PrimeVue Components Used
- [ ] Card (beneficiary cards)
- [ ] Button (actions)
- [ ] Dialog (modals)
- [ ] Stepper (multi-step form)
- [ ] InputText (form inputs)
- [ ] Dropdown (bank selection, country)
- [ ] InputMask (account number, routing number)
- [ ] Badge (transfer type)
- [ ] ConfirmDialog (delete confirmation)
- [ ] Skeleton (loading)
- [ ] Divider

---

## 🔌 Backend Integration

### API Endpoints Needed
- [ ] `GET /api/beneficiaries` - Fetch all user beneficiaries
- [ ] `POST /api/beneficiaries` - Add new beneficiary
- [ ] `GET /api/beneficiaries/{id}` - Get beneficiary details
- [ ] `PATCH /api/beneficiaries/{id}` - Update beneficiary
- [ ] `DELETE /api/beneficiaries/{id}` - Delete beneficiary
- [ ] `POST /api/beneficiaries/verify-pin` - Verify transaction PIN
- [ ] `GET /api/beneficiaries/{id}/transactions` - Get transactions to beneficiary
- [ ] `GET /api/banks` - Get list of local banks (for domestic)
- [ ] `GET /api/banks/search` - Search banks by name/code

### Inertia Props Required
```javascript
{
    beneficiaries: Array,      // All user beneficiaries
    recentBeneficiaries: Array, // Last 5 used
    banks: Array,              // Local banks list
    countries: Array,          // Countries for wire transfers
    stats: {
        total: Number,
        internal: Number,
        domestic: Number,
        wire: Number
    }
}
```

---

## 🔐 Security Features

- [ ] Transaction PIN required for:
  - Adding new beneficiary
  - Editing beneficiary (sensitive fields)
  - Deleting beneficiary
- [ ] Mask account numbers (show last 4 digits)
- [ ] Verify bank account exists (optional validation)
- [ ] Rate limiting on add/delete operations
- [ ] Email notification on beneficiary changes
- [ ] Two-factor authentication (optional, for high-risk changes)

---

## 📱 Responsive Design

- [ ] Desktop: 3-4 column grid for beneficiary cards
- [ ] Tablet: 2-3 column grid
- [ ] Mobile: Single column, full-width cards
- [ ] Touch-friendly action buttons
- [ ] Swipe actions on mobile (swipe left to delete)
- [ ] Bottom sheet for add/edit on mobile
- [ ] Horizontal scroll for "Recently Used" section

---

## ✅ Testing Checklist

- [ ] View all beneficiaries
- [ ] Add internal beneficiary
- [ ] Add domestic beneficiary
- [ ] Add wire transfer beneficiary
- [ ] Edit beneficiary details
- [ ] Delete beneficiary
- [ ] Confirm delete with PIN
- [ ] Undo delete (within 5 seconds)
- [ ] Search beneficiaries
- [ ] Filter by transfer type
- [ ] Filter by bank
- [ ] Sort beneficiaries
- [ ] Quick transfer (redirect with pre-fill)
- [ ] View beneficiary details
- [ ] View transaction history for beneficiary
- [ ] Copy account number
- [ ] Empty state display
- [ ] Loading states
- [ ] Form validation
- [ ] PIN verification
- [ ] Error handling
- [ ] Mobile responsiveness

---

## 🎬 Implementation Order

1. **Phase 1: Basic Display** ✅
   - Create page and route
   - BeneficiaryCard component
   - BeneficiariesGrid component
   - Fetch and display beneficiaries
   - Empty state

2. **Phase 2: Add Beneficiary** ✅
   - AddBeneficiaryModal component
   - Multi-step form (Stepper)
   - Form validation
   - PIN verification
   - Success feedback

3. **Phase 3: Edit & Delete** ✅
   - EditBeneficiaryModal component
   - DeleteBeneficiaryDialog component
   - PIN verification for both
   - Undo delete functionality

4. **Phase 4: Search & Filter** ✅
   - BeneficiaryFilters component
   - Search implementation
   - Filter by type and bank
   - Sort options

5. **Phase 5: Details View** ✅
   - BeneficiaryDetailsModal component
   - Show full details
   - Transaction history integration
   - Quick actions

6. **Phase 6: Quick Transfer Integration** ✅
   - Redirect logic
   - Pre-fill transfer form
   - Test all transfer types

7. **Phase 7: Recently Used Section** ✅
   - RecentBeneficiaries component
   - Horizontal scroll
   - Quick access

8. **Phase 8: Polish** ✅
   - Animations and transitions
   - Mobile optimization
   - Touch gestures
   - Accessibility

---

## 📝 Notes

- Beneficiary nicknames help users identify recipients easily
- Internal beneficiaries are verified automatically (bank lookup)
- Domestic beneficiaries may require routing number validation
- Wire transfers require more information (SWIFT, address)
- Consider implementing beneficiary groups (family, business, etc.)
- Favorite beneficiaries feature (pin to top)
- Transfer limits per beneficiary (optional security feature)
- Beneficiary verification status (verified, pending, unverified)
- Suggest beneficiaries based on frequent transfers

---

## 🔗 Related Files

- **Route:** `routes/web.php` - `/beneficiaries` route
- **Controller:** `app/Http/Controllers/BeneficiaryController.php`
- **Model:** `app/Models/Beneficiary.php`
- **Page:** `resources/js/Pages/Beneficiaries/Index.vue`
- **Components:** `resources/js/Components/Beneficiaries/`
- **Store:** `resources/js/Stores/beneficiaries.js` (Pinia)

---

**Previous Page:** [Transaction History](/transactions) ← `TRANSACTION_HISTORY_TODO.md`  
**Next Page:** [Wire Transfer](/transfers/wire) → `WIRE_TRANSFER_TODO.md`
