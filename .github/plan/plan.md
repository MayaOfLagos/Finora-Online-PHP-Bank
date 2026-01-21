┌─────────────────────────────────────────────────────────────────────────────┐
│                        DEVELOPMENT PHASES                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  PHASE 1: Foundation (Database & Models)                                    │
│  ════════════════════════════════════════                                   │
│  ├─ Database migrations (users, accounts, transactions, etc.)               │
│  ├─ Eloquent models with relationships                                      │
│  ├─ Factories & seeders for testing                                         │
│  └─ Enums for statuses (TransferStatus, AccountType, etc.)                  │
│                                                                             │
│                              ▼                                              │
│                                                                             │
│  PHASE 2: Admin Panel (Filament v5)                                         │
│  ═══════════════════════════════════                                        │
│  ├─ User management resource                                                │
│  ├─ Account management resource                                             │
│  ├─ Transaction viewing/approval                                            │
│  ├─ Settings & configuration panels                                         │
│  └─ Dashboard widgets (stats, charts)                                       │
│                                                                             │
│  WHY ADMIN FIRST?                                                           │
│  • Validates data structure before user frontend                            │
│  • Provides UI to manage test data                                          │
│  • Admin can approve pending transactions during dev                        │
│                                                                             │
│                              ▼                                              │
│                                                                             │
│  PHASE 3: Backend Services (Business Logic)                                 │
│  ═══════════════════════════════════════════                                │
│  ├─ TransferService (wire, internal, domestic, A2A)                         │
│  ├─ SecurityService (PIN, OTP, IMF/Tax/COT codes)                           │
│  ├─ DepositService (check, gateway, crypto)                                 │
│  ├─ LoanService (application, approval, repayment)                          │
│  └─ NotificationService (email, in-app)                                     │
│                                                                             │
│  Services are used by BOTH admin and user frontend                          │
│                                                                             │
│                              ▼                                              │
│                                                                             │
│  PHASE 4: User Frontend (Vue + Inertia)                                     │
│  ══════════════════════════════════════                                     │
│  ├─ Auth pages (login, register, 2FA)                                       │
│  ├─ Dashboard with account overview                                         │
│  ├─ Transfer flows with security steps                                      │
│  ├─ Deposit pages with gateway integration                                  │
│  └─ Account settings & profile                                              │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
