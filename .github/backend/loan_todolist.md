# Backend - Loan Module Todo List

## Overview
Implementation tasks for the loan system in Finora Bank.

---

## Models & Migrations

### Loan Types
- [ ] Create `loan_types` migration
- [ ] Create `LoanType` model
- [ ] Seed loan types (Personal, Business, Mortgage, Auto)

### Loan Applications
- [ ] Create `loan_applications` migration
- [ ] Create `LoanApplication` model
- [ ] Add fields: amount, term, purpose, status
- [ ] Add interest rate calculation fields

### Loan Documents
- [ ] Create `loan_documents` migration
- [ ] Create `LoanDocument` model
- [ ] Configure document storage

### Loan Repayments
- [ ] Create `loan_repayments` migration
- [ ] Create `LoanRepayment` model
- [ ] Add payment tracking fields

### Active Loans
- [ ] Create `loans` migration (approved loans)
- [ ] Create `Loan` model
- [ ] Add disbursement tracking

---

## Controllers

### LoanApplicationController
- [ ] Implement `index()` - List user's applications
- [ ] Implement `create()` - Show application form
- [ ] Implement `store()` - Submit application
- [ ] Implement `show()` - View application details
- [ ] Implement `uploadDocument()` - Upload required docs
- [ ] Implement `cancel()` - Cancel pending application

### LoanController
- [ ] Implement `index()` - List active loans
- [ ] Implement `show()` - View loan details
- [ ] Implement `schedule()` - View repayment schedule
- [ ] Implement `makePayment()` - Make loan payment
- [ ] Implement `paymentHistory()` - View payment history

### LoanCalculatorController
- [ ] Implement `calculate()` - Calculate loan terms

---

## Services

### LoanApplicationService
- [ ] Implement eligibility check
- [ ] Implement credit score simulation
- [ ] Implement document verification workflow
- [ ] Implement application status management

### LoanCalculatorService
- [ ] Implement interest calculation (simple/compound)
- [ ] Implement EMI calculation
- [ ] Implement total payable calculation
- [ ] Implement amortization schedule generation

### LoanDisbursementService
- [ ] Implement loan approval workflow
- [ ] Implement disbursement to account
- [ ] Implement loan activation

### LoanRepaymentService
- [ ] Implement payment processing
- [ ] Implement late payment detection
- [ ] Implement penalty calculation
- [ ] Implement early payoff calculation

---

## Loan Types Configuration

### Personal Loan
- Interest Rate: 8-15% APR
- Term: 12-60 months
- Max Amount: Configurable
- Requirements: Basic KYC

### Business Loan
- Interest Rate: 10-18% APR
- Term: 12-84 months
- Max Amount: Configurable
- Requirements: Business documents

### Mortgage Loan
- Interest Rate: 5-10% APR
- Term: 60-360 months
- Max Amount: Configurable
- Requirements: Property documents

### Auto Loan
- Interest Rate: 6-12% APR
- Term: 12-72 months
- Max Amount: Configurable
- Requirements: Vehicle details

---

## Enums

- [ ] Create `LoanStatus` enum (draft, submitted, under_review, approved, rejected, disbursed, active, closed, defaulted)
- [ ] Create `LoanTypeEnum` enum
- [ ] Create `RepaymentStatus` enum (pending, paid, overdue, partial)
- [ ] Create `DocumentType` enum

---

## Events & Notifications

### Events
- [ ] Create `LoanApplicationSubmitted` event
- [ ] Create `LoanApproved` event
- [ ] Create `LoanRejected` event
- [ ] Create `LoanDisbursed` event
- [ ] Create `LoanPaymentDue` event
- [ ] Create `LoanPaymentReceived` event
- [ ] Create `LoanPaymentOverdue` event

### Notifications
- [ ] Create application received email
- [ ] Create application approved email
- [ ] Create application rejected email
- [ ] Create disbursement confirmation email
- [ ] Create payment reminder email
- [ ] Create payment received email
- [ ] Create overdue notice email

---

## Scheduled Tasks

- [ ] Create payment reminder command (3 days before due)
- [ ] Create overdue detection command
- [ ] Create penalty application command
- [ ] Schedule in `Console/Kernel.php`

---

## Requests

- [ ] Create `StoreLoanApplicationRequest`
- [ ] Create `UploadLoanDocumentRequest`
- [ ] Create `MakeLoanPaymentRequest`
- [ ] Create `CalculateLoanRequest`

---

## Routes

- [ ] Define loan application routes
- [ ] Define active loan routes
- [ ] Define calculator routes
- [ ] Add authentication middleware

---

## Testing

- [ ] Test loan calculation accuracy
- [ ] Test application workflow
- [ ] Test payment processing
- [ ] Test penalty calculations
- [ ] Test scheduled tasks

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Models & Migrations | 🔴 Not Started | 0% |
| Loan Application | 🔴 Not Started | 0% |
| Loan Calculator | 🔴 Not Started | 0% |
| Loan Repayment | 🔴 Not Started | 0% |
| Notifications | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
