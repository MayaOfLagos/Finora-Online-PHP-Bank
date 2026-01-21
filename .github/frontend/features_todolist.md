# Frontend - Loan, Cards, Grants, Support Todo List

## Overview
Vue 3 + Inertia.js implementation for banking features.

---

# LOAN MODULE

## Loan Pages

### Application Flow
- [ ] Create `pages/Loan/Index.vue` - Loan products overview
- [ ] Create `pages/Loan/Calculator.vue` - Loan calculator
- [ ] Create `pages/Loan/Apply/Index.vue` - Start application
- [ ] Create `pages/Loan/Apply/Form.vue` - Application form
- [ ] Create `pages/Loan/Apply/Documents.vue` - Upload documents
- [ ] Create `pages/Loan/Apply/Review.vue` - Review application
- [ ] Create `pages/Loan/Apply/Success.vue` - Application submitted

### Active Loans
- [ ] Create `pages/Loan/Active/Index.vue` - My loans
- [ ] Create `pages/Loan/Active/Show.vue` - Loan details
- [ ] Create `pages/Loan/Active/Schedule.vue` - Repayment schedule
- [ ] Create `pages/Loan/Active/Payment.vue` - Make payment

### Applications
- [ ] Create `pages/Loan/Applications/Index.vue` - My applications
- [ ] Create `pages/Loan/Applications/Show.vue` - Application status

## Loan Components

- [ ] Create `LoanProductCard.vue` - Loan type card
- [ ] Create `LoanCalculator.vue` - Calculator widget
- [ ] Create `LoanApplicationForm.vue` - Application form
- [ ] Create `LoanDocumentUpload.vue` - Document uploader
- [ ] Create `LoanScheduleTable.vue` - Repayment schedule
- [ ] Create `LoanPaymentForm.vue` - Payment form
- [ ] Create `LoanStatusBadge.vue` - Status indicator
- [ ] Create `LoanProgressBar.vue` - Repayment progress
- [ ] Create `AmortizationChart.vue` - Visual schedule

---

# CARDS MODULE

## Card Pages

- [ ] Create `pages/Cards/Index.vue` - My cards
- [ ] Create `pages/Cards/Show.vue` - Card details
- [ ] Create `pages/Cards/Request.vue` - Request new card
- [ ] Create `pages/Cards/Virtual/Create.vue` - Create virtual card
- [ ] Create `pages/Cards/Settings.vue` - Card settings
- [ ] Create `pages/Cards/Transactions.vue` - Card transactions
- [ ] Create `pages/Cards/Statement.vue` - Card statement

## Card Components

- [ ] Create `CardDisplay.vue` - Visual card design
- [ ] Create `CardList.vue` - List of cards
- [ ] Create `VirtualCardGenerator.vue` - Generate virtual card
- [ ] Create `CardDetails.vue` - Card information
- [ ] Create `CardActions.vue` - Freeze/Unfreeze/Block
- [ ] Create `CardLimitSetting.vue` - Set spending limit
- [ ] Create `CardPinChange.vue` - Change card PIN
- [ ] Create `CardTransactionList.vue` - Transaction history
- [ ] Create `CardTypeSelector.vue` - Select card type
- [ ] Create `ShippingAddressForm.vue` - Physical card address

### Card Visual Design
- [ ] Create card front design (number, name, expiry)
- [ ] Create card back design (CVV)
- [ ] Implement card flip animation
- [ ] Add show/hide sensitive data toggle

---

# GRANTS MODULE

## Grant Pages

- [ ] Create `pages/Grants/Index.vue` - Available grants
- [ ] Create `pages/Grants/Show.vue` - Grant details
- [ ] Create `pages/Grants/Apply.vue` - Apply for grant
- [ ] Create `pages/Grants/Applications/Index.vue` - My applications
- [ ] Create `pages/Grants/Applications/Show.vue` - Application status

## Grant Components

- [ ] Create `GrantCard.vue` - Grant program card
- [ ] Create `GrantDetails.vue` - Grant information
- [ ] Create `GrantEligibility.vue` - Eligibility checker
- [ ] Create `GrantApplicationForm.vue` - Application form
- [ ] Create `GrantDocumentUpload.vue` - Document uploader
- [ ] Create `GrantStatusTracker.vue` - Application status
- [ ] Create `GrantDeadline.vue` - Deadline countdown

---

# SUPPORT MODULE

## Support Pages

### Tickets
- [ ] Create `pages/Support/Index.vue` - Support home
- [ ] Create `pages/Support/Tickets/Index.vue` - My tickets
- [ ] Create `pages/Support/Tickets/Create.vue` - New ticket
- [ ] Create `pages/Support/Tickets/Show.vue` - Ticket details

### FAQ & Knowledge Base
- [ ] Create `pages/Support/Faq/Index.vue` - FAQ list
- [ ] Create `pages/Support/Faq/Show.vue` - FAQ answer
- [ ] Create `pages/Support/Articles/Index.vue` - Knowledge base
- [ ] Create `pages/Support/Articles/Show.vue` - Article view

## Support Components

### Ticket Components
- [ ] Create `TicketList.vue` - Ticket list
- [ ] Create `TicketCard.vue` - Ticket summary
- [ ] Create `TicketForm.vue` - New ticket form
- [ ] Create `TicketThread.vue` - Ticket conversation
- [ ] Create `TicketMessage.vue` - Single message
- [ ] Create `TicketReplyForm.vue` - Reply form
- [ ] Create `TicketAttachment.vue` - Attachment handler
- [ ] Create `TicketStatusBadge.vue` - Status indicator
- [ ] Create `PrioritySelector.vue` - Priority dropdown
- [ ] Create `CategorySelector.vue` - Category dropdown

### FAQ Components
- [ ] Create `FaqList.vue` - FAQ accordion
- [ ] Create `FaqItem.vue` - Single FAQ
- [ ] Create `FaqSearch.vue` - Search FAQs
- [ ] Create `FaqCategories.vue` - Category filter

### Knowledge Base Components
- [ ] Create `ArticleList.vue` - Article list
- [ ] Create `ArticleCard.vue` - Article preview
- [ ] Create `ArticleContent.vue` - Article body
- [ ] Create `ArticleSearch.vue` - Search articles
- [ ] Create `RelatedArticles.vue` - Related content

### Live Chat (Optional)
- [ ] Create `ChatWidget.vue` - Chat widget
- [ ] Create `ChatWindow.vue` - Chat window
- [ ] Create `ChatMessage.vue` - Chat message
- [ ] Create `ChatInput.vue` - Message input

---

# BENEFICIARY MODULE

## Beneficiary Pages

- [ ] Create `pages/Beneficiary/Index.vue` - All beneficiaries
- [ ] Create `pages/Beneficiary/Add.vue` - Add beneficiary
- [ ] Create `pages/Beneficiary/Edit.vue` - Edit beneficiary
- [ ] Create `pages/Beneficiary/Verify.vue` - Verify beneficiary

## Beneficiary Components

- [ ] Create `BeneficiaryList.vue` - Beneficiary list
- [ ] Create `BeneficiaryCard.vue` - Beneficiary card
- [ ] Create `BeneficiaryForm.vue` - Add/Edit form
- [ ] Create `BeneficiarySearch.vue` - Search beneficiaries
- [ ] Create `BeneficiaryQuickSelect.vue` - Quick selection
- [ ] Create `FavoriteBeneficiaries.vue` - Favorites list
- [ ] Create `RecentBeneficiaries.vue` - Recently used
- [ ] Create `VerificationFlow.vue` - Verification steps

---

## Store (Pinia)

- [ ] Create `stores/loan.js` - Loan state
- [ ] Create `stores/cards.js` - Cards state
- [ ] Create `stores/grants.js` - Grants state
- [ ] Create `stores/support.js` - Support state
- [ ] Create `stores/beneficiary.js` - Beneficiary state

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Loan Module | 🔴 Not Started | 0% |
| Cards Module | 🔴 Not Started | 0% |
| Grants Module | 🔴 Not Started | 0% |
| Support Module | 🔴 Not Started | 0% |
| Beneficiary Module | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
