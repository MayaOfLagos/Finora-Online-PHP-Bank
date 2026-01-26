# Finora Bank - Project Instructions

## Project Overview

**Finora Bank** is a professional-grade online banking application built for educational purposes. This project demonstrates enterprise-level banking features with modern web technologies.

---

## Technology Stack

### Backend
- **Framework:** Laravel 11+ (latest stable)
- **Admin Panel:** Filament v5 (with MCP server integration)
- **Database:** MySQL 8.0+ / MariaDB 10.6+
- **PHP Version:** 8.2+
- **Hosting:** Shared hosting compatible (cPanel/DirectAdmin)

### Frontend
- **Framework:** Vue 3 (latest - Composition API with `<script setup>`)
- **UI Library:** PrimeVue 4.x (banking-grade components)
- **SSR Bridge:** Inertia.js (latest)
- **Styling:** Tailwind CSS 4.x
- **State Management:** Pinia
- **Build Tool:** Vite
- **Icons:** PrimeIcons + Heroicons

### MCP Integration
- **Laravel Boost MCP:** For Laravel contextual understanding
- **Filament v5 MCP Server:** For admin panel development context
- Refer to Filament v5 documentation for MCP setup instructions

---

## Core Banking Features

### 1. Transfer Module

#### Wire Transfer (International)
**Security Flow:** `Transaction PIN → IMF Code → Tax Code → COT Code → Email OTP → Success`

- International wire transfers
- SWIFT/BIC code support
- Multi-currency support
- Three-step verification (IMF, Tax, COT)
- Beneficiary management
- Transfer limits and fees

#### Internal Transfer (Within Bank)
**Security Flow:** `Transaction PIN → Email OTP → Success`

- User-to-user transfers within Finora Bank
- Instant transfer
- No fees (configurable)
- Transaction history

#### Domestic Transfer (Local Banks)
**Security Flow:** `Transaction PIN → Email OTP → Success`

- Transfer to other local banks
- Bank routing validation
- Processing time configuration
- Fee structure

#### Account-to-Account Transfer
**Security Flow:** `Transaction PIN → Email OTP → Success`

- Transfer between own accounts
- Quick transfer shortcuts
- Scheduled transfers
- Recurring transfers

### 2. Deposit Module

#### Check Deposit
- Image capture/upload
- OCR verification (optional)
- Pending approval workflow
- Hold period configuration

#### Mobile Deposit (Payment Gateways)
**Security:** `Transaction PIN → Success`

- **Stripe** integration
- **PayPal** integration
- **Paystack** integration
- **Flutterwave** integration (optional)
- **Razorpay** integration (optional)
- Admin can enable/disable gateways

#### Crypto Deposit
- Admin-configurable crypto addresses
- Multiple cryptocurrency support:
  - Bitcoin (BTC)
  - Ethereum (ETH)
  - USDT (TRC20/ERC20)
  - USDC
  - Custom cryptocurrencies
- QR code generation
- Wallet address display
- Manual verification by admin

### 3. Loan Module

- Personal loans
- Business loans
- Mortgage loans
- Auto loans
- Loan calculator
- Application workflow
- Document upload
- Approval process
- Repayment schedule
- Interest calculation
- Late payment penalties

### 4. Cards Module

- Virtual card generation
- Physical card requests
- Card types (Debit/Credit)
- Card limits management
- Card freeze/unfreeze
- PIN management
- Transaction history
- Card statements

### 5. Grants Module

- Grant application
- Eligibility criteria
- Document submission
- Review process
- Approval/Rejection workflow
- Grant disbursement

### 6. Support Module

- Ticket system
- Live chat (optional)
- FAQ section
- Knowledge base
- Email support
- Priority levels
- Status tracking

### 7. Beneficiary Module

- Saved beneficiaries for Account-to-Account
- Beneficiary verification
- Nickname assignment
- Quick transfer shortcuts
- Beneficiary limits

### 8. Account Management

- Account types (Savings, Checking, Business)
- Account statements
- Transaction history
- Account settings
- Profile management
- KYC verification

### 9. Notifications

- Email notifications
- SMS notifications (optional)
- Push notifications (optional)
- In-app notifications
- Notification preferences

---

## Security Implementation

### Authentication
- Email/Password login
- Two-Factor Authentication (2FA)
- Session management
- Device tracking
- Login history

### Transaction Security

#### Transaction PIN
- 4-6 digit PIN
- PIN change functionality
- PIN recovery via email
- Failed attempts lockout

#### Email OTP
- Time-based OTP
- 6-digit code
- Expiry configuration (default: 10 minutes)
- Resend functionality

#### Wire Transfer Verification Codes
1. **IMF Code:** International Monetary Fund verification
2. **Tax Code:** Tax clearance verification
3. **COT Code:** Cost of Transfer verification

*Note: These codes are admin-managed for educational demonstration*

---

## Admin Panel (Filament v5)

### Dashboard
- Total users overview
- Transaction statistics
- Revenue metrics
- Recent activities
- Charts and graphs

### User Management
- User CRUD
- KYC verification
- Account status management
- User impersonation
- Activity logs

### Transaction Management
- All transactions view
- Pending approvals
- Transaction status updates
- Refund processing

### Deposit Management
- Pending deposits review
- Crypto deposit verification
- Check deposit approval
- Gateway management

### Loan Management
- Loan applications
- Approval workflow
- Disbursement processing
- Repayment tracking

### Card Management
- Card requests
- Card issuance
- Limit management
- Card blocking

### Settings
- General settings
- Fee configuration
- Limit configuration
- Gateway credentials
- Email templates
- SMS templates

### Reports
- Transaction reports
- User reports
- Revenue reports
- Export functionality (PDF, Excel)

---

## Database Design Principles

- Use UUIDs for sensitive tables
- Soft deletes for audit trails
- Timestamps on all tables
- Proper indexing
- Foreign key constraints
- Money stored in cents (integer)

---

## API Standards

- RESTful conventions
- JSON responses
- Proper HTTP status codes
- Rate limiting
- API versioning
- Request validation

---

## Folder Structure Convention

```
finora-bank/
├── app/
│   ├── Filament/           # Filament admin resources
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/           # Business logic
│   ├── Enums/              # Status enums
│   └── Notifications/
├── resources/
│   ├── js/
│   │   ├── Components/     # Vue components
│   │   ├── Layouts/
│   │   ├── Pages/          # Inertia pages
│   │   └── Stores/         # Pinia stores
│   └── views/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
└── routes/
    ├── web.php
    └── api.php
```

---

## Filament v5 Header Actions Best Practices

### Width Enum (IMPORTANT)

In Filament v5, use `Filament\Support\Enums\Width` for modal widths:

```php
use Filament\Support\Enums\Width;

// Correct usage:
->modalWidth(Width::Medium)
->modalWidth(Width::Large)
->modalWidth(Width::ExtraLarge)

// DEPRECATED - Do NOT use:
// use Filament\Support\Enums\MaxWidth; // This is deprecated in v5
```

**Available Width values:** `ExtraSmall`, `Small`, `Medium`, `Large`, `ExtraLarge`, `TwoExtraLarge`, `ThreeExtraLarge`, `FourExtraLarge`, `FiveExtraLarge`, `SixExtraLarge`, `SevenExtraLarge`, `Screen`

### Modal Dialogs for CRUD Operations

**Always prefer modal dialogs over page-based forms for header actions.** This applies to Create, Edit, View, and Delete operations.

#### When to Use Modal Dialogs:
- Creating new records (Use `Action::make('create')` with `.form()` and `.action()`)
- Editing records (Use `Action::make('edit')` with `.form()` and `.action()`)
- Viewing records (Use `Action::make('view')` with form fields set to disabled)
- Deleting records (Use `.requiresConfirmation()`)
- Bulk actions

#### Modal Dialog Pattern:
```php
Action::make('create_account_type')
    ->label('Create Account Type')
    ->icon('heroicon-o-plus-circle')
    ->color('primary')
    ->modalWidth(Width::Large)
    ->form([
        TextInput::make('name')->required(),
        Textarea::make('description'),
    ])
    ->action(function (array $data) {
        Model::create($data);
        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();
    }),
```

#### Action Group Pattern for Multiple Actions:
```php
ActionGroup::make([
    Action::make('view')->icon('heroicon-o-eye')->color('info'),
    Action::make('edit')->icon('heroicon-o-pencil')->color('primary'),
    Action::make('delete')->icon('heroicon-o-trash')->color('danger'),
])
```

#### Avoid:
- Don't use `CreateAction::make()` that navigates to `/create` page
- Don't use `EditAction::make()` that navigates to `/{record}/edit` page
- Don't mix modal dialogs with page-based navigation in same resource

### Benefits:
- Better UX - no page reloads
- Cleaner navigation - admin stays on list
- Faster operations - modal context
- Consistent with modern patterns

---

## 🎯 Frontend Development (CURRENT PRIORITY)

> **Reference Document:** `.github/frontend/FRONTEND_DEVELOPMENT_PLAN.md`

### Technology Stack
- **Framework:** Vue 3 (Composition API with `<script setup>`)
- **UI Library:** PrimeVue 4.x (recommended for banking)
- **SSR Bridge:** Inertia.js
- **Styling:** Tailwind CSS 4.x
- **State Management:** Pinia
- **Build Tool:** Vite

### Development Approach
- **Mobile-First Design** - All components must be responsive
- **Component-Based Architecture** - Reusable Vue components
- **Clean UI/UX** - Professional banking aesthetic
- **Fast & Elegant** - Optimized performance, smooth animations

### Folder Structure
```
resources/js/
├── Components/
│   ├── Common/         # AppLogo, LoadingSpinner, StatusBadge
│   ├── Forms/          # OtpInput, PinInput, AmountInput
│   ├── Cards/          # AccountCard, TransactionCard, BankCard
│   ├── Navigation/     # Sidebar, BottomNav, UserMenu
│   └── Modals/         # TransferConfirm, OtpVerify, SuccessModal
├── Layouts/
│   ├── DashboardLayout.vue
│   ├── AuthLayout.vue
│   └── GuestLayout.vue
├── Pages/              # Inertia pages (auto-routed)
├── Stores/             # Pinia stores
├── Composables/        # Vue composables (useAuth, useCurrency)
└── Utils/              # Helpers (formatters, validators)
```

### Vue Component Standards

#### File Naming
- Components: `PascalCase.vue` (e.g., `AccountCard.vue`)
- Pages: `PascalCase.vue` in nested folders (e.g., `Pages/Transfers/Wire.vue`)
- Composables: `camelCase.js` with `use` prefix (e.g., `useAuth.js`)

#### Component Template
```vue
<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Props
const props = defineProps({
    account: Object,
    showBalance: { type: Boolean, default: true }
});

// Emits
const emit = defineEmits(['select', 'transfer']);

// Composables
const page = usePage();
const user = computed(() => page.props.auth.user);

// Reactive state
const isLoading = ref(false);

// Methods
const handleTransfer = () => {
    emit('transfer', props.account);
};
</script>

<template>
    <div class="account-card">
        <!-- Template content -->
    </div>
</template>

<style scoped>
/* Component-specific styles if needed */
</style>
```

### PrimeVue Integration

#### Setup (app.js)
```javascript
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.dark',
        }
    }
});
app.use(ToastService);
app.use(ConfirmationService);
```

#### Common Components to Use
- `DataTable` - Transaction lists, account lists
- `Card` - Account cards, stat cards
- `InputNumber` - Currency amounts
- `InputMask` - Account numbers, phone numbers
- `Password` - Password fields with strength meter
- `Toast` - Notifications
- `ConfirmDialog` - Delete confirmations
- `Stepper` - Multi-step transfer flows
- `Chart` - Account balance charts

### Security UX Patterns

#### PIN Input Component
```vue
<PinInput 
    :length="6" 
    masked 
    @complete="verifyPin"
/>
```

#### OTP Input Component
```vue
<OtpInput 
    :length="6" 
    auto-focus 
    auto-submit
    :countdown="300"
    @complete="verifyOtp"
    @resend="resendOtp"
/>
```

#### Transfer Verification Steps
1. Wire Transfer: PIN → IMF → Tax → COT → OTP → Success
2. Other Transfers: PIN → OTP → Success

### Mobile-First Guidelines

#### Bottom Navigation (Mobile)
```vue
<BottomNav v-if="isMobile">
    <BottomNavItem icon="home" to="/dashboard" />
    <BottomNavItem icon="credit-card" to="/cards" />
    <BottomNavItem icon="send" to="/transfers" />
    <BottomNavItem icon="user" to="/profile" />
    <BottomNavItem icon="menu" @click="openMore" />
</BottomNav>
```

#### Responsive Breakpoints
```css
/* Tailwind breakpoints */
sm: 640px   /* Mobile landscape */
md: 768px   /* Tablet */
lg: 1024px  /* Desktop */
xl: 1280px  /* Large desktop */
```

#### Touch-Friendly
- Minimum tap target: 44x44px
- Use `@click` not `@dblclick`
- Swipe gestures for lists (optional)

### API Integration with Inertia

#### Page Props
```javascript
// In Laravel Controller
return Inertia::render('Dashboard', [
    'accounts' => $user->bankAccounts,
    'recentTransactions' => $user->transactions()->latest()->take(10)->get(),
    'stats' => [
        'totalBalance' => $totalBalance,
        'monthlyIncome' => $monthlyIncome,
    ],
]);
```

#### Form Submission
```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    amount: null,
    beneficiary_id: null,
    pin: '',
});

const submit = () => {
    form.post('/transfers/internal', {
        onSuccess: () => {
            // Show success toast
        },
    });
};
</script>
```

### Pinia Store Pattern
```javascript
// stores/accounts.js
import { defineStore } from 'pinia';

export const useAccountsStore = defineStore('accounts', {
    state: () => ({
        accounts: [],
        selectedAccount: null,
        isLoading: false,
    }),
    
    getters: {
        totalBalance: (state) => state.accounts.reduce(
            (sum, acc) => sum + acc.balance, 0
        ),
    },
    
    actions: {
        async fetchAccounts() {
            this.isLoading = true;
            // Accounts come from Inertia props, not API calls
        },
    },
});
```

### Performance Guidelines
- Lazy load pages with `defineAsyncComponent`
- Use `v-memo` for expensive list renders
- Optimize images with proper formats (WebP)
- Keep bundle size under 200KB (gzipped)

---

## Coding Standards

- Follow PSR-12 for PHP
- Use Vue 3 Composition API with `<script setup>`
- TypeScript encouraged for Vue components
- Consistent naming conventions
- Comprehensive comments
- Unit and feature tests

---

## Environment Requirements

### Shared Hosting Compatibility
- No queue workers required (sync driver)
- File-based caching option
- No Supervisor needed
- Compatible with PHP-FPM
- .htaccess for Apache

---

## Getting Started

1. Clone repository
2. Copy `.env.example` to `.env`
3. Configure database credentials
4. Run `composer install`
5. Run `npm install`
6. Run `php artisan key:generate`
7. Run `php artisan migrate --seed`
8. Run `npm run build`

---

## Todo Lists Location

All development todo lists are organized in:
- `.github/backend/` - Backend implementation tasks
- `.github/frontend/` - Frontend implementation tasks
- `.github/security/` - Security implementation tasks
- `.github/admin/` - Filament admin panel tasks
- `.github/database/` - Database design and migrations

---

## Version History

- **v1.0.0** - Initial project setup

---

*This is an educational project for learning purposes.*

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5.1
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version-specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== livewire/core rules ===

## Livewire

- Use the `search-docs` tool to find exact version-specific documentation for how to write Livewire and Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` Artisan command to create new components.
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend; they're like regular HTTP requests. Always validate form data and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle Hook Examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>

## Testing Livewire

<code-snippet name="Example Livewire Component Test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>

<code-snippet name="Testing Livewire Component Exists on Page" lang="php">
    $this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== phpunit/core rules ===

## PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== production deployment ===

## Production Server (StackCP)

### SSH Configuration
- **SSH Alias:** `finora-server` (configured in ~/.ssh/config)
- **Host:** ssh.gb.stackcp.com
- **User:** finorafundtrust.com
- **Document Root:** ~/public_html

### PHP Path (CRITICAL)
The default `php` command on the server points to PHP 8.0.30, which is incompatible with Laravel 12.
**Always use the full path to PHP 8.5:**

```bash
/usr/php85/usr/bin/php
```

### Available PHP Versions on Server
- `/usr/php80/usr/bin/php` - PHP 8.0
- `/usr/php81/usr/bin/php` - PHP 8.1
- `/usr/php82/usr/bin/php` - PHP 8.2
- `/usr/php83/usr/bin/php` - PHP 8.3
- `/usr/php84/usr/bin/php` - PHP 8.4
- `/usr/php85/usr/bin/php` - PHP 8.5 ✅ (USE THIS)

### Deployment Commands
```bash
# Pull latest changes
ssh finora-server "cd ~/public_html && git pull origin main"

# Run migrations
ssh finora-server "cd ~/public_html && /usr/php85/usr/bin/php artisan migrate --force"

# Clear all caches
ssh finora-server "cd ~/public_html && /usr/php85/usr/bin/php artisan config:clear && /usr/php85/usr/bin/php artisan cache:clear && /usr/php85/usr/bin/php artisan view:clear && /usr/php85/usr/bin/php artisan route:clear"

# Full deployment (one command)
ssh finora-server "cd ~/public_html && git pull origin main && /usr/php85/usr/bin/php artisan migrate --force && /usr/php85/usr/bin/php artisan config:clear && /usr/php85/usr/bin/php artisan cache:clear && /usr/php85/usr/bin/php artisan view:clear && /usr/php85/usr/bin/php artisan route:clear"
```

### Composer Commands on Production
```bash
ssh finora-server "cd ~/public_html && /usr/php85/usr/bin/php /usr/bin/composer install --no-dev --optimize-autoloader"
```
</laravel-boost-guidelines>
