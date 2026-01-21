# Database Design Todo List

## Overview
Database migrations and schema design for Finora Bank.

---

## Core Tables

### Users (Extend Default)
- [ ] Modify `users` migration

```sql
users
├── id (bigint, PK)
├── uuid (uuid, unique)
├── first_name (varchar)
├── last_name (varchar)
├── email (varchar, unique)
├── email_verified_at (timestamp, nullable)
├── phone_number (varchar, nullable)
├── date_of_birth (date, nullable)
├── address_line_1 (varchar, nullable)
├── address_line_2 (varchar, nullable)
├── city (varchar, nullable)
├── state (varchar, nullable)
├── postal_code (varchar, nullable)
├── country (varchar, nullable)
├── profile_photo_path (varchar, nullable)
├── transaction_pin (varchar, nullable) -- hashed
├── is_active (boolean, default: true)
├── is_verified (boolean, default: false)
├── kyc_level (tinyint, default: 1)
├── last_login_at (timestamp, nullable)
├── last_login_ip (varchar, nullable)
├── password (varchar)
├── remember_token (varchar, nullable)
├── two_factor_secret (text, nullable)
├── two_factor_recovery_codes (text, nullable)
├── created_at (timestamp)
├── updated_at (timestamp)
└── deleted_at (timestamp, nullable)
```

### Account Types
- [ ] Create `account_types` migration

```sql
account_types
├── id (bigint, PK)
├── name (varchar) -- Savings, Checking, Business
├── code (varchar, unique) -- SAV, CHK, BUS
├── description (text, nullable)
├── minimum_balance (bigint, default: 0) -- in cents
├── is_active (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Bank Accounts
- [ ] Create `bank_accounts` migration

```sql
bank_accounts
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── account_type_id (bigint, FK)
├── account_number (varchar, unique)
├── balance (bigint, default: 0) -- in cents
├── currency (varchar, default: 'USD')
├── is_primary (boolean, default: false)
├── is_active (boolean, default: true)
├── opened_at (timestamp)
├── closed_at (timestamp, nullable)
├── created_at (timestamp)
├── updated_at (timestamp)
└── deleted_at (timestamp, nullable)
```

---

## KYC Tables

### KYC Verifications
- [ ] Create `kyc_verifications` migration

```sql
kyc_verifications
├── id (bigint, PK)
├── user_id (bigint, FK)
├── document_type (varchar) -- passport, id_card, driver_license
├── document_number (varchar, nullable)
├── document_front_path (varchar, nullable)
├── document_back_path (varchar, nullable)
├── selfie_path (varchar, nullable)
├── address_proof_path (varchar, nullable)
├── status (varchar) -- pending, approved, rejected
├── rejection_reason (text, nullable)
├── verified_at (timestamp, nullable)
├── verified_by (bigint, FK, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Transfer Tables

### Wire Transfers
- [ ] Create `wire_transfers` migration

```sql
wire_transfers
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── bank_account_id (bigint, FK)
├── reference_number (varchar, unique)
├── beneficiary_name (varchar)
├── beneficiary_account (varchar)
├── beneficiary_bank_name (varchar)
├── beneficiary_bank_address (text, nullable)
├── swift_code (varchar)
├── routing_number (varchar, nullable)
├── amount (bigint) -- in cents
├── currency (varchar)
├── exchange_rate (decimal, nullable)
├── fee (bigint, default: 0)
├── total_amount (bigint)
├── purpose (text, nullable)
├── status (varchar) -- pending, processing, completed, failed, cancelled
├── current_step (varchar, nullable)
├── imf_verified_at (timestamp, nullable)
├── tax_verified_at (timestamp, nullable)
├── cot_verified_at (timestamp, nullable)
├── otp_verified_at (timestamp, nullable)
├── completed_at (timestamp, nullable)
├── failed_reason (text, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Internal Transfers
- [ ] Create `internal_transfers` migration

```sql
internal_transfers
├── id (bigint, PK)
├── uuid (uuid, unique)
├── sender_id (bigint, FK -> users)
├── sender_account_id (bigint, FK)
├── receiver_id (bigint, FK -> users)
├── receiver_account_id (bigint, FK)
├── reference_number (varchar, unique)
├── amount (bigint)
├── currency (varchar)
├── description (text, nullable)
├── status (varchar)
├── otp_verified_at (timestamp, nullable)
├── completed_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Domestic Transfers
- [ ] Create `domestic_transfers` migration

```sql
domestic_transfers
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── bank_account_id (bigint, FK)
├── reference_number (varchar, unique)
├── bank_id (bigint, FK)
├── beneficiary_name (varchar)
├── beneficiary_account (varchar)
├── amount (bigint)
├── currency (varchar)
├── fee (bigint, default: 0)
├── description (text, nullable)
├── status (varchar)
├── otp_verified_at (timestamp, nullable)
├── completed_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Account Transfers
- [ ] Create `account_transfers` migration

```sql
account_transfers
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── from_account_id (bigint, FK)
├── to_account_id (bigint, FK)
├── reference_number (varchar, unique)
├── amount (bigint)
├── currency (varchar)
├── description (text, nullable)
├── status (varchar)
├── otp_verified_at (timestamp, nullable)
├── completed_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Banks (Local)
- [ ] Create `banks` migration

```sql
banks
├── id (bigint, PK)
├── name (varchar)
├── code (varchar, unique)
├── routing_number (varchar, nullable)
├── swift_code (varchar, nullable)
├── is_active (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Verification Codes

### Verification Codes
- [ ] Create `verification_codes` migration

```sql
verification_codes
├── id (bigint, PK)
├── user_id (bigint, FK)
├── type (varchar) -- imf, tax, cot
├── code (varchar) -- encrypted
├── is_active (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Deposit Tables

### Check Deposits
- [ ] Create `check_deposits` migration

```sql
check_deposits
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── bank_account_id (bigint, FK)
├── reference_number (varchar, unique)
├── check_number (varchar, nullable)
├── check_front_image (varchar)
├── check_back_image (varchar, nullable)
├── amount (bigint)
├── currency (varchar)
├── status (varchar) -- pending, approved, rejected, completed
├── rejection_reason (text, nullable)
├── hold_until (timestamp, nullable)
├── approved_at (timestamp, nullable)
├── approved_by (bigint, FK, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Mobile Deposits (Gateway)
- [ ] Create `mobile_deposits` migration

```sql
mobile_deposits
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── bank_account_id (bigint, FK)
├── reference_number (varchar, unique)
├── gateway (varchar) -- stripe, paypal, paystack
├── gateway_transaction_id (varchar, nullable)
├── amount (bigint)
├── currency (varchar)
├── fee (bigint, default: 0)
├── status (varchar)
├── gateway_response (json, nullable)
├── completed_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Cryptocurrencies
- [ ] Create `cryptocurrencies` migration

```sql
cryptocurrencies
├── id (bigint, PK)
├── name (varchar) -- Bitcoin, Ethereum
├── symbol (varchar) -- BTC, ETH
├── network (varchar, nullable) -- ERC20, TRC20
├── icon (varchar, nullable)
├── is_active (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Crypto Wallets (Admin)
- [ ] Create `crypto_wallets` migration

```sql
crypto_wallets
├── id (bigint, PK)
├── cryptocurrency_id (bigint, FK)
├── wallet_address (varchar)
├── label (varchar, nullable)
├── is_active (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Crypto Deposits
- [ ] Create `crypto_deposits` migration

```sql
crypto_deposits
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── bank_account_id (bigint, FK)
├── cryptocurrency_id (bigint, FK)
├── crypto_wallet_id (bigint, FK)
├── reference_number (varchar, unique)
├── crypto_amount (decimal)
├── usd_amount (bigint) -- converted to cents
├── transaction_hash (varchar, nullable)
├── status (varchar)
├── verified_at (timestamp, nullable)
├── verified_by (bigint, FK, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Loan Tables

### Loan Types
- [ ] Create `loan_types` migration

```sql
loan_types
├── id (bigint, PK)
├── name (varchar)
├── code (varchar, unique)
├── description (text, nullable)
├── min_amount (bigint)
├── max_amount (bigint)
├── min_term_months (integer)
├── max_term_months (integer)
├── interest_rate (decimal) -- annual percentage
├── is_active (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Loan Applications
- [ ] Create `loan_applications` migration

```sql
loan_applications
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── loan_type_id (bigint, FK)
├── reference_number (varchar, unique)
├── amount (bigint)
├── term_months (integer)
├── interest_rate (decimal)
├── monthly_payment (bigint)
├── total_payable (bigint)
├── purpose (text, nullable)
├── status (varchar)
├── rejection_reason (text, nullable)
├── approved_at (timestamp, nullable)
├── approved_by (bigint, FK, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Loans (Active)
- [ ] Create `loans` migration

```sql
loans
├── id (bigint, PK)
├── uuid (uuid, unique)
├── loan_application_id (bigint, FK)
├── user_id (bigint, FK)
├── bank_account_id (bigint, FK)
├── principal_amount (bigint)
├── outstanding_balance (bigint)
├── interest_rate (decimal)
├── monthly_payment (bigint)
├── next_payment_date (date)
├── final_payment_date (date)
├── status (varchar) -- active, closed, defaulted
├── disbursed_at (timestamp)
├── closed_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Loan Documents
- [ ] Create `loan_documents` migration

```sql
loan_documents
├── id (bigint, PK)
├── loan_application_id (bigint, FK)
├── document_type (varchar)
├── file_path (varchar)
├── original_name (varchar)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Loan Repayments
- [ ] Create `loan_repayments` migration

```sql
loan_repayments
├── id (bigint, PK)
├── loan_id (bigint, FK)
├── reference_number (varchar, unique)
├── amount (bigint)
├── principal_portion (bigint)
├── interest_portion (bigint)
├── penalty_amount (bigint, default: 0)
├── due_date (date)
├── paid_at (timestamp, nullable)
├── status (varchar) -- pending, paid, overdue, partial
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Card Tables

### Card Types
- [ ] Create `card_types` migration

```sql
card_types
├── id (bigint, PK)
├── name (varchar)
├── code (varchar, unique)
├── is_virtual (boolean, default: false)
├── is_credit (boolean, default: false)
├── default_limit (bigint, nullable)
├── is_active (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Cards
- [ ] Create `cards` migration

```sql
cards
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── bank_account_id (bigint, FK)
├── card_type_id (bigint, FK)
├── card_number (varchar) -- encrypted
├── card_holder_name (varchar)
├── expiry_month (varchar) -- encrypted
├── expiry_year (varchar) -- encrypted
├── cvv (varchar) -- encrypted
├── pin (varchar, nullable) -- hashed
├── spending_limit (bigint, nullable)
├── daily_limit (bigint, nullable)
├── status (varchar) -- active, frozen, blocked, expired
├── is_virtual (boolean)
├── issued_at (timestamp)
├── expires_at (timestamp)
├── blocked_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Card Requests (Physical)
- [ ] Create `card_requests` migration

```sql
card_requests
├── id (bigint, PK)
├── user_id (bigint, FK)
├── card_type_id (bigint, FK)
├── reference_number (varchar, unique)
├── shipping_address (text)
├── status (varchar) -- pending, processing, shipped, delivered
├── tracking_number (varchar, nullable)
├── shipped_at (timestamp, nullable)
├── delivered_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Card Transactions
- [ ] Create `card_transactions` migration

```sql
card_transactions
├── id (bigint, PK)
├── card_id (bigint, FK)
├── reference_number (varchar, unique)
├── merchant_name (varchar, nullable)
├── merchant_category (varchar, nullable)
├── amount (bigint)
├── currency (varchar)
├── type (varchar) -- purchase, atm, refund
├── status (varchar)
├── transaction_at (timestamp)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Grant Tables

### Grant Programs
- [ ] Create `grant_programs` migration

```sql
grant_programs
├── id (bigint, PK)
├── name (varchar)
├── description (text)
├── amount (bigint)
├── currency (varchar)
├── eligibility_criteria (json, nullable)
├── required_documents (json, nullable)
├── start_date (date)
├── end_date (date)
├── max_recipients (integer, nullable)
├── status (varchar) -- open, closed, upcoming
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Grant Applications
- [ ] Create `grant_applications` migration

```sql
grant_applications
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── grant_program_id (bigint, FK)
├── reference_number (varchar, unique)
├── status (varchar)
├── rejection_reason (text, nullable)
├── approved_at (timestamp, nullable)
├── approved_by (bigint, FK, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Grant Documents
- [ ] Create `grant_documents` migration

```sql
grant_documents
├── id (bigint, PK)
├── grant_application_id (bigint, FK)
├── document_type (varchar)
├── file_path (varchar)
├── original_name (varchar)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Grant Disbursements
- [ ] Create `grant_disbursements` migration

```sql
grant_disbursements
├── id (bigint, PK)
├── grant_application_id (bigint, FK)
├── bank_account_id (bigint, FK)
├── reference_number (varchar, unique)
├── amount (bigint)
├── status (varchar)
├── disbursed_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Support Tables

### Support Categories
- [ ] Create `support_categories` migration

```sql
support_categories
├── id (bigint, PK)
├── name (varchar)
├── description (text, nullable)
├── is_active (boolean, default: true)
├── sort_order (integer, default: 0)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Support Tickets
- [ ] Create `support_tickets` migration

```sql
support_tickets
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── category_id (bigint, FK)
├── ticket_number (varchar, unique)
├── subject (varchar)
├── priority (varchar) -- low, medium, high, urgent
├── status (varchar) -- open, in_progress, waiting, resolved, closed
├── assigned_to (bigint, FK, nullable)
├── resolved_at (timestamp, nullable)
├── closed_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Ticket Messages
- [ ] Create `ticket_messages` migration

```sql
ticket_messages
├── id (bigint, PK)
├── support_ticket_id (bigint, FK)
├── user_id (bigint, FK, nullable) -- null for system
├── message (text)
├── type (varchar) -- customer, agent, system
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Ticket Attachments
- [ ] Create `ticket_attachments` migration

```sql
ticket_attachments
├── id (bigint, PK)
├── ticket_message_id (bigint, FK)
├── file_path (varchar)
├── original_name (varchar)
├── file_size (bigint)
├── mime_type (varchar)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### FAQs
- [ ] Create `faqs` migration

```sql
faqs
├── id (bigint, PK)
├── category_id (bigint, FK, nullable)
├── question (text)
├── answer (text)
├── is_published (boolean, default: false)
├── sort_order (integer, default: 0)
├── view_count (integer, default: 0)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Knowledge Base Articles
- [ ] Create `knowledge_base_articles` migration

```sql
knowledge_base_articles
├── id (bigint, PK)
├── category_id (bigint, FK, nullable)
├── title (varchar)
├── slug (varchar, unique)
├── content (longtext)
├── is_published (boolean, default: false)
├── view_count (integer, default: 0)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Beneficiary Tables

### Beneficiaries
- [ ] Create `beneficiaries` migration

```sql
beneficiaries
├── id (bigint, PK)
├── uuid (uuid, unique)
├── user_id (bigint, FK)
├── beneficiary_user_id (bigint, FK)
├── beneficiary_account_id (bigint, FK)
├── nickname (varchar)
├── is_verified (boolean, default: false)
├── is_favorite (boolean, default: false)
├── transfer_limit (bigint, nullable)
├── last_used_at (timestamp, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Activity & Logging

### Activity Logs
- [ ] Create `activity_logs` migration

```sql
activity_logs
├── id (bigint, PK)
├── user_id (bigint, FK, nullable)
├── action (varchar)
├── description (text)
├── subject_type (varchar, nullable)
├── subject_id (bigint, nullable)
├── ip_address (varchar, nullable)
├── user_agent (text, nullable)
├── metadata (json, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Login History
- [ ] Create `login_histories` migration

```sql
login_histories
├── id (bigint, PK)
├── user_id (bigint, FK)
├── ip_address (varchar)
├── user_agent (text)
├── device_type (varchar, nullable)
├── browser (varchar, nullable)
├── platform (varchar, nullable)
├── location (varchar, nullable)
├── status (varchar) -- success, failed
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Settings & Configuration

### Settings
- [ ] Create `settings` migration

```sql
settings
├── id (bigint, PK)
├── group (varchar)
├── key (varchar)
├── value (text, nullable)
├── type (varchar) -- string, integer, boolean, json
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Payment Gateways
- [ ] Create `payment_gateways` migration

```sql
payment_gateways
├── id (bigint, PK)
├── name (varchar)
├── code (varchar, unique)
├── logo (varchar, nullable)
├── credentials (json) -- encrypted
├── is_active (boolean, default: false)
├── is_test_mode (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Notifications

### Notifications (Laravel Default)
- [ ] Use Laravel's `notifications` table

### Notification Settings
- [ ] Create `notification_settings` migration

```sql
notification_settings
├── id (bigint, PK)
├── user_id (bigint, FK)
├── type (varchar)
├── email (boolean, default: true)
├── sms (boolean, default: false)
├── push (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Seeders

### Required Seeders
- [ ] Create `AccountTypeSeeder`
- [ ] Create `BankSeeder`
- [ ] Create `LoanTypeSeeder`
- [ ] Create `CardTypeSeeder`
- [ ] Create `CryptocurrencySeeder`
- [ ] Create `SupportCategorySeeder`
- [ ] Create `SettingsSeeder`
- [ ] Create `PaymentGatewaySeeder`
- [ ] Create `RoleAndPermissionSeeder`

---

## Progress Tracking

| Table Group | Status | Completion |
|-------------|--------|------------|
| Core Tables | 🔴 Not Started | 0% |
| Transfer Tables | 🔴 Not Started | 0% |
| Deposit Tables | 🔴 Not Started | 0% |
| Loan Tables | 🔴 Not Started | 0% |
| Card Tables | 🔴 Not Started | 0% |
| Grant Tables | 🔴 Not Started | 0% |
| Support Tables | 🔴 Not Started | 0% |
| Settings Tables | 🔴 Not Started | 0% |
| Seeders | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
