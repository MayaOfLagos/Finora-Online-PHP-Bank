# Referral System Implementation Plan

## Overview

A comprehensive multi-level referral system where users can invite others and both parties earn rewards. The system includes admin management, level-based earnings, and integration with the existing Rewards system.

---

## Features Summary

### 1. Admin Panel - Referral Management
- **Referrals Resource** - View/manage all referrals
- **Referral Levels Resource** - Define multi-level tree structure
- **Referral Settings** - Global configuration

### 2. Referral Levels (Tree System)
- Admin defines X levels (e.g., 5 or 10)
- Each level has:
  - Level number (1, 2, 3...)
  - Inviter earning percentage/amount
  - New user earning percentage/amount
  - Minimum requirements (optional)
- Quick setup: Admin enters number of levels → auto-generates with default percentages

### 3. Registration Flow Enhancement
- **Referral Link Detection** (`?ref=CODE`)
  - Show welcome modal with confetti
  - Display inviter name/avatar
  - Show potential earnings
  - Save referral to session
- **Referral Input** (Coupon-style toggle)
  - "I have a referral code" expandable section
  - Auto-filled if from referral link
  - Validation against existing codes

### 4. Reward Distribution
- On successful registration:
  - **Inviter** receives reward based on their level
  - **New User** receives welcome bonus (if configured)
- Rewards added to existing Rewards system
- Users can redeem rewards as account credit

---

## Database Schema

### Table: `referral_levels`
```
id                      - bigint, primary key
level                   - integer (1, 2, 3...), unique
name                    - string (e.g., "Bronze", "Silver", "Gold")
inviter_reward_type     - enum('fixed', 'percentage')
inviter_reward_amount   - decimal (amount or percentage)
invitee_reward_type     - enum('fixed', 'percentage')
invitee_reward_amount   - decimal (amount for new user)
min_referrals_required  - integer (referrals needed to reach this level)
color                   - string (for UI display)
icon                    - string (optional)
is_active               - boolean
created_at              - timestamp
updated_at              - timestamp
```

### Table: `referrals` (tracking all referrals)
```
id                      - bigint, primary key
uuid                    - uuid, unique
referrer_id             - foreignId (user who invited)
referred_id             - foreignId (new user who signed up)
referral_code_used      - string (the code used)
referrer_level_id       - foreignId (level at time of referral)
referrer_reward_id      - foreignId (nullable, reward given to inviter)
referred_reward_id      - foreignId (nullable, reward given to new user)
referrer_earned         - decimal (amount earned by inviter)
referred_earned         - decimal (amount earned by new user)
status                  - enum('pending', 'completed', 'cancelled')
completed_at            - timestamp (nullable)
created_at              - timestamp
updated_at              - timestamp
```

### Table: `referral_settings` (global config)
```
id                      - bigint, primary key
key                     - string, unique
value                   - text (JSON for complex values)
created_at              - timestamp
updated_at              - timestamp
```

**Settings Keys:**
- `referral_enabled` - boolean
- `welcome_bonus_enabled` - boolean
- `welcome_bonus_amount` - decimal
- `min_deposit_for_reward` - decimal (optional requirement)
- `reward_delay_hours` - integer (delay before reward is claimable)

---

## Implementation Phases

### Phase 1: Database & Models
- [ ] Create `referral_levels` migration
- [ ] Create `referrals` migration  
- [ ] Create `referral_settings` migration
- [ ] Create `ReferralLevel` model with relationships
- [ ] Create `Referral` model with relationships
- [ ] Create `ReferralSetting` model (key-value store)
- [ ] Update `User` model relationships
- [ ] Create `ReferralService` for business logic

### Phase 2: Admin Panel - Referral Levels
- [ ] Create `ReferralLevelResource` in Filament
  - List view with sortable levels
  - Create/Edit with level configuration
  - Quick setup action (auto-generate X levels)
  - Toggle active/inactive
- [ ] Add level icons and colors for visual hierarchy

### Phase 3: Admin Panel - Referrals Management
- [ ] Create `ReferralResource` in Filament
  - List all referrals with filters
  - View referral details
  - Stats widgets (total, this month, earnings)
  - Export functionality

### Phase 4: Admin Panel - Settings
- [ ] Create Referral Settings page
  - Enable/disable referral program
  - Configure welcome bonus
  - Set reward distribution rules
  - Configure minimum requirements

### Phase 5: Frontend - Registration Enhancement
- [ ] Create referral welcome modal component
  - Confetti animation (use canvas-confetti)
  - Inviter info display
  - Earning preview
  - "Continue" button
- [ ] Add referral code input to registration
  - Coupon-style toggle ("I have a referral code")
  - Expandable input section
  - Real-time validation
- [ ] Session management for referral codes
- [ ] Auto-detect referral from URL parameter

### Phase 6: Referral Processing Logic
- [ ] Handle referral on user registration
  - Validate referral code
  - Determine inviter's level
  - Calculate rewards for both parties
  - Create reward records
  - Update referral status
- [ ] Create scheduled job for delayed rewards (if configured)
- [ ] Level progression logic (auto-upgrade based on referrals)

### Phase 7: User Dashboard Integration
- [ ] Add referral section to user dashboard
- [ ] Show referral code & link
- [ ] Display referral stats
- [ ] List referred users
- [ ] Show pending/earned rewards

---

## Referral Level Auto-Generation Logic

When admin enters "5 levels", auto-generate:

| Level | Name     | Inviter Reward | New User Reward | Min Referrals |
|-------|----------|----------------|-----------------|---------------|
| 1     | Starter  | $5 (fixed)     | $2              | 0             |
| 2     | Bronze   | $7 (fixed)     | $3              | 5             |
| 3     | Silver   | $10 (fixed)    | $5              | 15            |
| 4     | Gold     | $15 (fixed)    | $7              | 30            |
| 5     | Platinum | $25 (fixed)    | $10             | 50            |

Admin can customize after generation.

---

## Registration Flow Diagram

```
User clicks referral link (?ref=CODE)
         │
         ▼
┌─────────────────────────────────┐
│  Validate referral code exists  │
└─────────────────────────────────┘
         │
         ▼ (valid)
┌─────────────────────────────────┐
│  Save to session                │
│  referral_code = CODE           │
│  referrer_id = inviter.id       │
└─────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│  Show Welcome Modal             │
│  - Confetti animation 🎉        │
│  - "You were invited by [Name]" │
│  - "Sign up & earn $X!"         │
│  - [Continue] button            │
└─────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│  Registration Form              │
│  - Referral input auto-filled   │
│  - (or toggle to enter code)    │
└─────────────────────────────────┘
         │
         ▼ (on successful registration)
┌─────────────────────────────────┐
│  Process Referral               │
│  1. Get inviter's level         │
│  2. Calculate inviter reward    │
│  3. Calculate new user reward   │
│  4. Create Reward records       │
│  5. Update referral status      │
│  6. Check level progression     │
└─────────────────────────────────┘
```

---

## Reward Integration

### Reward Types for Referrals:
- `referral_inviter` - Reward for inviting someone
- `referral_signup` - Reward for signing up via referral

### Reward Record Structure:
```php
Reward::create([
    'user_id' => $user->id,
    'type' => 'referral_inviter', // or 'referral_signup'
    'points' => 0,
    'amount' => $calculatedAmount, // in cents
    'status' => 'earned',
    'source' => 'Referral Program',
    'description' => 'Earned for inviting [username]',
    'metadata' => [
        'referral_id' => $referral->id,
        'referred_user_id' => $newUser->id,
        'level_at_time' => $level->id,
    ],
]);
```

---

## API Endpoints (if needed)

```
GET  /api/referral/validate/{code}  - Validate referral code
GET  /api/referral/info/{code}      - Get inviter info for modal
POST /api/referral/apply            - Apply referral to session
```

---

## File Structure

```
app/
├── Enums/
│   └── ReferralStatus.php
├── Models/
│   ├── Referral.php
│   ├── ReferralLevel.php
│   └── ReferralSetting.php
├── Services/
│   └── ReferralService.php
├── Filament/
│   └── Resources/
│       ├── Referrals/
│       │   ├── ReferralResource.php
│       │   ├── Pages/
│       │   ├── Schemas/
│       │   └── Tables/
│       └── ReferralLevels/
│           ├── ReferralLevelResource.php
│           ├── Pages/
│           ├── Schemas/
│           └── Tables/
database/
├── migrations/
│   ├── xxxx_create_referral_levels_table.php
│   ├── xxxx_create_referrals_table.php
│   └── xxxx_create_referral_settings_table.php
└── seeders/
    └── ReferralLevelSeeder.php
resources/
└── js/
    ├── Components/
    │   └── Modals/
    │       └── ReferralWelcomeModal.vue
    └── Pages/
        └── Auth/
            └── Register.vue (update)
```

---

## Todo List Summary

### Database & Backend
1. [ ] Create migrations (referral_levels, referrals, referral_settings)
2. [ ] Create Eloquent models with relationships
3. [ ] Create ReferralStatus enum
4. [ ] Create ReferralService class
5. [ ] Update User model with referral relationships

### Admin Panel
6. [ ] Create ReferralLevelResource (CRUD + auto-generate)
7. [ ] Create ReferralResource (list, view, stats)
8. [ ] Create Referral Settings page
9. [ ] Add referral stats to admin dashboard

### Frontend Registration
10. [ ] Create ReferralWelcomeModal component (Vue)
11. [ ] Add confetti animation library
12. [ ] Update Register page with referral input
13. [ ] Implement session storage for referral
14. [ ] Handle referral URL parameter detection

### Referral Processing
15. [ ] Implement referral validation on registration
16. [ ] Implement reward calculation and distribution
17. [ ] Implement level progression system
18. [ ] Add activity logging for referrals

### Testing
19. [ ] Write tests for ReferralService
20. [ ] Write tests for registration with referral
21. [ ] Test reward distribution

---

## Notes

- All monetary amounts stored in cents (integer)
- Rewards are NOT direct credits - must be redeemed
- Consider fraud prevention (same IP, email patterns)
- Level progression is automatic based on referral count
- Admin can manually adjust user levels if needed

---

*Created: January 31, 2026*
