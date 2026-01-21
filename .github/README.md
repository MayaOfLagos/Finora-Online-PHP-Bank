# Finora Bank - Project Overview

## 🏦 About Finora Bank

Finora Bank is a professional-grade online banking application built for educational purposes. This project demonstrates enterprise-level banking features with modern web technologies.

---

## 📂 Project Structure

```
.github/
├── copilot-instructions.md     # Main project instructions
├── backend/                     # Backend todo lists
│   ├── transfer_todolist.md    # Transfer module tasks
│   ├── deposit_todolist.md     # Deposit module tasks
│   ├── loan_todolist.md        # Loan module tasks
│   ├── cards_todolist.md       # Cards module tasks
│   ├── grants_todolist.md      # Grants module tasks
│   ├── support_todolist.md     # Support module tasks
│   ├── beneficiary_todolist.md # Beneficiary module tasks
│   └── account_todolist.md     # Account management tasks
├── frontend/                    # Frontend todo lists
│   ├── transfer_todolist.md    # Transfer UI tasks
│   ├── deposit_todolist.md     # Deposit UI tasks
│   ├── dashboard_account_todolist.md # Dashboard & Account UI
│   ├── features_todolist.md    # Loan, Cards, etc. UI
│   └── auth_todolist.md        # Authentication UI
├── security/                    # Security implementation
│   └── security_todolist.md    # Security tasks
├── admin/                       # Filament admin panel
│   └── filament_todolist.md    # Admin panel tasks
└── database/                    # Database design
    └── database_todolist.md    # Migration tasks
```

---

## 🛠 Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 11+ |
| Admin Panel | Filament v5 |
| Frontend | Vue 3 + Inertia.js |
| Styling | Tailwind CSS 3.x |
| State | Pinia |
| Database | MySQL 8.0+ |
| Build | Vite |

---

## 🔐 Security Flows

### Wire Transfer (International)
```
PIN → IMF Code → Tax Code → COT Code → Email OTP → Success
```

### Internal/Domestic/Account Transfer
```
PIN → Email OTP → Success
```

### Mobile Deposit
```
PIN → Payment Gateway → Success
```

---

## 📊 Module Overview

| Module | Description | Security |
|--------|-------------|----------|
| Wire Transfer | International transfers with SWIFT | 5-step verification |
| Internal Transfer | Within Finora Bank | PIN + OTP |
| Domestic Transfer | Local bank transfers | PIN + OTP |
| Account Transfer | Between own accounts | PIN + OTP |
| Check Deposit | Image upload deposit | PIN only |
| Mobile Deposit | Stripe/PayPal/Paystack | PIN only |
| Crypto Deposit | Bitcoin, ETH, USDT | PIN only |
| Loans | Personal, Business, etc. | Standard auth |
| Cards | Virtual & Physical | PIN + Card PIN |
| Grants | Grant applications | Standard auth |
| Support | Ticket system | Standard auth |

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Installation
```bash
# Clone and setup
composer install
npm install
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build assets
npm run build

# Serve
php artisan serve
```

---

## 📝 Development Workflow

1. Check relevant todo list in `.github/`
2. Mark task as in-progress
3. Implement feature
4. Write tests
5. Mark task as complete
6. Update progress tracking

---

## 📈 Progress Dashboard

### Backend Modules
- [ ] Transfer Module
- [ ] Deposit Module
- [ ] Loan Module
- [ ] Cards Module
- [ ] Grants Module
- [ ] Support Module
- [ ] Beneficiary Module
- [ ] Account Management

### Frontend Modules
- [ ] Authentication
- [ ] Dashboard
- [ ] Transfer UI
- [ ] Deposit UI
- [ ] Other Features

### Admin Panel
- [ ] Filament Setup
- [ ] Resources
- [ ] Widgets
- [ ] Reports

### Database
- [ ] Core Tables
- [ ] Feature Tables
- [ ] Seeders

---

## 🔗 Important Links

- [Laravel Documentation](https://laravel.com/docs)
- [Filament v5 Documentation](https://filamentphp.com/docs)
- [Vue 3 Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

---

## 📄 License

This is an educational project for learning purposes.

---

*Last Updated: Project Initialization*
