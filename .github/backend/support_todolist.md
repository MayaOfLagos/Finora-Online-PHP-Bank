# Backend - Support Module Todo List

## Overview
Implementation tasks for the customer support system in Finora Bank.

---

## Models & Migrations

### Support Categories
- [ ] Create `support_categories` migration
- [ ] Create `SupportCategory` model
- [ ] Seed default categories

### Support Tickets
- [ ] Create `support_tickets` migration
- [ ] Create `SupportTicket` model
- [ ] Add priority fields
- [ ] Add status fields
- [ ] Add assigned agent fields

### Ticket Messages
- [ ] Create `ticket_messages` migration
- [ ] Create `TicketMessage` model
- [ ] Add attachments support

### Ticket Attachments
- [ ] Create `ticket_attachments` migration
- [ ] Create `TicketAttachment` model
- [ ] Configure secure storage

### FAQ
- [ ] Create `faqs` migration
- [ ] Create `Faq` model
- [ ] Add category relationship

### Knowledge Base
- [ ] Create `knowledge_base_articles` migration
- [ ] Create `KnowledgeBaseArticle` model
- [ ] Add search indexing

---

## Controllers

### SupportTicketController
- [ ] Implement `index()` - List user's tickets
- [ ] Implement `create()` - Show ticket form
- [ ] Implement `store()` - Create new ticket
- [ ] Implement `show()` - View ticket details
- [ ] Implement `reply()` - Reply to ticket
- [ ] Implement `close()` - Close ticket
- [ ] Implement `reopen()` - Reopen ticket
- [ ] Implement `uploadAttachment()` - Add attachment

### FaqController
- [ ] Implement `index()` - List all FAQs
- [ ] Implement `show()` - View FAQ
- [ ] Implement `search()` - Search FAQs
- [ ] Implement `byCategory()` - FAQs by category

### KnowledgeBaseController
- [ ] Implement `index()` - List articles
- [ ] Implement `show()` - View article
- [ ] Implement `search()` - Search articles
- [ ] Implement `byCategory()` - Articles by category

---

## Services

### TicketService
- [ ] Implement ticket creation
- [ ] Implement ticket assignment
- [ ] Implement priority management
- [ ] Implement SLA tracking

### TicketNotificationService
- [ ] Implement new ticket notification
- [ ] Implement reply notification
- [ ] Implement status change notification

### FaqService
- [ ] Implement FAQ search
- [ ] Implement FAQ ordering
- [ ] Implement view counting

### KnowledgeBaseService
- [ ] Implement article search
- [ ] Implement related articles
- [ ] Implement view tracking

---

## Support Categories

### Default Categories
- Account Issues
- Transfer Problems
- Deposit Inquiries
- Loan Questions
- Card Issues
- Security Concerns
- Technical Support
- General Inquiry
- Feedback/Suggestions

---

## Priority Levels

- [ ] Low - Response within 48 hours
- [ ] Medium - Response within 24 hours
- [ ] High - Response within 12 hours
- [ ] Urgent - Response within 4 hours

---

## Enums

- [ ] Create `TicketStatus` enum (open, in_progress, waiting_customer, resolved, closed)
- [ ] Create `TicketPriority` enum (low, medium, high, urgent)
- [ ] Create `MessageType` enum (customer, agent, system)

---

## Events & Notifications

### Events
- [ ] Create `TicketCreated` event
- [ ] Create `TicketReplied` event
- [ ] Create `TicketResolved` event
- [ ] Create `TicketClosed` event

### Notifications
- [ ] Create ticket created email
- [ ] Create agent reply email
- [ ] Create ticket resolved email
- [ ] Create ticket closed email
- [ ] Create satisfaction survey email

---

## Live Chat (Optional)

### Implementation Options
- [ ] Research WebSocket options
- [ ] Consider Laravel Reverb
- [ ] Consider Pusher integration
- [ ] Fallback to polling for shared hosting

---

## Requests

- [ ] Create `StoreTicketRequest`
- [ ] Create `ReplyTicketRequest`
- [ ] Create `UploadAttachmentRequest`

---

## Routes

- [ ] Define ticket routes
- [ ] Define FAQ routes
- [ ] Define knowledge base routes
- [ ] Add authentication middleware

---

## Testing

- [ ] Test ticket creation
- [ ] Test reply workflow
- [ ] Test status transitions
- [ ] Test search functionality
- [ ] Test file uploads

---

## Progress Tracking

| Feature | Status | Completion |
|---------|--------|------------|
| Models & Migrations | 🔴 Not Started | 0% |
| Ticket System | 🔴 Not Started | 0% |
| FAQ | 🔴 Not Started | 0% |
| Knowledge Base | 🔴 Not Started | 0% |
| Live Chat | 🔴 Not Started | 0% |

---

*Last Updated: Project Initialization*
