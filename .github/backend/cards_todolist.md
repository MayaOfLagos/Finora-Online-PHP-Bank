# Backend - Cards Module Todo List

## Overview
Implementation tasks for the virtual and physical cards system in Finora Bank.

---

## Models & Migrations

### Card Types
- [ ] Create `card_types` migration
- [ ] Create `CardType` model
- [ ] Seed card types (Debit, Credit, Virtual)

### Cards
- [ ] Create `cards` migration
- [ ] Create `Card` model
- [ ] Add encrypted card number field
- [ ] Add CVV encryption
- [ ] Add expiry date fields
- [ ] Add card status fields
- [ ] Add spending limits fields

### Card Transactions
- [ ] Create `card_transactions` migration
- [ ] Create `CardTransaction` model
- [ ] Add merchant information fields

### Card Requests
- [ ] Create `card_requests` migration (for physical cards)
- [ ] Create `CardRequest` model
- [ ] Add shipping address fields

---

## Controllers

### CardController
- [ ] Implement `index()` - List user's cards
- [ ] Implement `show()` - View card details
- [ ] Implement `create()` - Request new card
- [ ] Implement `freeze()` - Freeze card
- [ ] Implement `unfreeze()` - Unfreeze card
- [ ] Implement `setLimit()` - Set spending limit
- [ ] Implement `changePin()` - Change card PIN
- [ ] Implement `block()` - Block card permanently

### VirtualCardController
- [ ] Implement `generate()` - Generate virtual card
- [ ] Implement `regenerate()` - Regenerate card details
- [ ] Implement `delete()` - Delete virtual card

### CardTransactionController
- [ ] Implement `index()` - List card transactions
- [ ] Implement `show()` - View transaction details
- [ ] Implement `statement()` - Generate card statement

### CardRequestController
- [ ] Implement `store()` - Submit physical card request
- [ ] Implement `status()` - Check request status

---

## Services

### CardService
- [ ] Implement card number generation (Luhn algorithm)
- [ ] Implement CVV generation
- [ ] Implement expiry date calculation
- [ ] Implement card encryption/decryption
- [ ] Implement card masking (show last 4 digits)

### VirtualCardService
- [ ] Implement instant card generation
- [ ] Implement card regeneration
- [ ] Implement virtual card limits

### CardTransactionService
- [ ] Implement transaction logging
- [ ] Implement spending limit validation
- [ ] Implement merchant categorization

### CardStatementService
- [ ] Implement statement generation
- [ ] Implement PDF export
- [ ] Implement date range filtering

---

## Security

### Card Data Encryption
- [ ] Implement AES-256 encryption for card numbers
- [ ] Implement secure CVV storage
- [ ] Implement PIN hashing
- [ ] Use Laravel's encryption facade

### Card PIN Management
- [ ] Implement PIN creation flow
- [ ] Implement PIN verification
- [ ] Implement PIN change with OTP
- [ ] Implement PIN reset

---

## Enums

- [ ] Create `CardStatus` enum (active, frozen, blocked, expired)
- [ ] Create `CardType` enum (debit, credit, virtual)
- [ ] Create `CardRequestStatus` enum (pending, processing, shipped, delivered)

---

## Card Specifications

### Virtual Card
- Instant generation
- Online transactions only
- Regeneratable
- Configurable limits

### Debit Card
- Physical card
- Linked to account balance
- ATM withdrawals (simulation)
- POS transactions (simulation)

### Credit Card
- Physical card
- Credit limit based
- Monthly billing cycle
- Interest calculation

---

## Events & Notifications

### Events
- [ ] Create `CardCreated` event
- [ ] Create `CardFrozen` event
- [ ] Create `CardBlocked` event
- [ ] Create `CardTransaction` event
- [ ] Create `CardLimitReached` event

### Notifications
- [ ] Create card created email
- [ ] Create card frozen email
- [ ] Create transaction alert email
- [ ] Create spending limit alert email
- [ ] Create card shipped email

---

## Requests

- [ ] Create `CreateCardRequest`
- [ ] Create `SetCardLimitRequest`
- [ ] Create `ChangeCardPinRequest`
- [ ] Create `CardRequestRequest` (physical card)

---

## Routes

- [ ] Define card management routes
- [ ] Define virtual card routes
- [ ] Define transaction routes
- [ ] Add authentication middleware
- [ ] Add PIN verification middleware

---

## Testing

- [ ] Test card number generation (Luhn)
- [ ] Test encryption/decryption
- [ ] Test spending limits
- [ ] Test card freeze/unfreeze
- [ ] Test statement generation

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Models & Migrations | 🔴 Not Started | 0% |
| Virtual Cards | 🔴 Not Started | 0% |
| Physical Cards | 🔴 Not Started | 0% |
| Card Transactions | 🔴 Not Started | 0% |
| Card Statements | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
