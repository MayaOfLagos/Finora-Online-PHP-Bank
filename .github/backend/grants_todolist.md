# Backend - Grants Module Todo List

## Overview
Implementation tasks for the grants system in Finora Bank.

---

## Models & Migrations

### Grant Programs
- [ ] Create `grant_programs` migration
- [ ] Create `GrantProgram` model
- [ ] Add eligibility criteria fields
- [ ] Add funding amount fields
- [ ] Add application deadline fields

### Grant Applications
- [ ] Create `grant_applications` migration
- [ ] Create `GrantApplication` model
- [ ] Add application status fields
- [ ] Add review notes fields

### Grant Documents
- [ ] Create `grant_documents` migration
- [ ] Create `GrantDocument` model
- [ ] Configure document storage

### Grant Disbursements
- [ ] Create `grant_disbursements` migration
- [ ] Create `GrantDisbursement` model

---

## Controllers

### GrantProgramController
- [ ] Implement `index()` - List available grants
- [ ] Implement `show()` - View grant details
- [ ] Implement `eligibility()` - Check eligibility

### GrantApplicationController
- [ ] Implement `index()` - List user's applications
- [ ] Implement `create()` - Show application form
- [ ] Implement `store()` - Submit application
- [ ] Implement `show()` - View application status
- [ ] Implement `uploadDocument()` - Upload documents
- [ ] Implement `withdraw()` - Withdraw application

### GrantDisbursementController
- [ ] Implement `index()` - List disbursements
- [ ] Implement `show()` - View disbursement details

---

## Services

### GrantProgramService
- [ ] Implement program creation
- [ ] Implement eligibility criteria management
- [ ] Implement deadline management

### GrantApplicationService
- [ ] Implement eligibility validation
- [ ] Implement application submission
- [ ] Implement document management
- [ ] Implement status tracking

### GrantDisbursementService
- [ ] Implement disbursement processing
- [ ] Implement fund transfer to account

### GrantEligibilityService
- [ ] Implement criteria checking
- [ ] Implement score calculation
- [ ] Implement automatic eligibility

---

## Eligibility Criteria

### Configurable Criteria
- Account age (minimum)
- Account balance (minimum/maximum)
- Transaction history
- KYC verification level
- Geographic location
- Account type
- Custom criteria (admin-defined)

---

## Enums

- [ ] Create `GrantStatus` enum (open, closed, upcoming)
- [ ] Create `ApplicationStatus` enum (draft, submitted, under_review, approved, rejected, disbursed)
- [ ] Create `DisbursementStatus` enum (pending, processing, completed, failed)

---

## Events & Notifications

### Events
- [ ] Create `GrantApplicationSubmitted` event
- [ ] Create `GrantApplicationApproved` event
- [ ] Create `GrantApplicationRejected` event
- [ ] Create `GrantDisbursed` event

### Notifications
- [ ] Create application received email
- [ ] Create application approved email
- [ ] Create application rejected email
- [ ] Create disbursement notification email

---

## Requests

- [ ] Create `StoreGrantApplicationRequest`
- [ ] Create `UploadGrantDocumentRequest`

---

## Routes

- [ ] Define grant program routes
- [ ] Define application routes
- [ ] Define disbursement routes
- [ ] Add authentication middleware

---

## Testing

- [ ] Test eligibility validation
- [ ] Test application workflow
- [ ] Test document upload
- [ ] Test disbursement process

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Models & Migrations | 🔴 Not Started | 0% |
| Grant Programs | 🔴 Not Started | 0% |
| Grant Applications | 🔴 Not Started | 0% |
| Disbursements | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
