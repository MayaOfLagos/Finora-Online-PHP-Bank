# Finora Bank - Professional Homepage Redesign Plan

## Overview
Transform the Finora Bank homepage into a professional, modern banking website with mobile-first responsive design, featuring smooth animations, auto-sliding banners, and comprehensive footer sections.

---

## 🎯 Project Goals
- **Professional Banking Aesthetic** - Clean, trustworthy, institutional look
- **Mobile-First Design** - Prioritize mobile experience with sticky action buttons
- **Performance** - Fast loading with preloader, lazy loading images
- **Conversion Focused** - Clear CTAs for Login/Register
- **Accessibility** - WCAG compliant, readable, navigable

---

## 📱 Responsive Breakpoints
```
Mobile:     < 640px   (Primary focus)
Tablet:     640px - 1024px
Desktop:    > 1024px
```

---

## 🗂️ Component Structure

```
resources/js/
├── Components/
│   └── Landing/
│       ├── Preloader.vue              # Page loading animation
│       ├── Header/
│       │   ├── DesktopNav.vue         # Desktop navigation
│       │   ├── MobileNav.vue          # Mobile hamburger menu
│       │   └── NavLinks.vue           # Shared navigation links
│       ├── Hero/
│       │   ├── HeroBanner.vue         # Main hero with auto-slide
│       │   └── HeroSlide.vue          # Individual slide component
│       ├── Sections/
│       │   ├── AboutSection.vue       # About the bank
│       │   ├── ServicesSection.vue    # Banking services grid
│       │   ├── FeaturesSection.vue    # Why choose us
│       │   ├── StatsSection.vue       # Numbers/achievements
│       │   ├── TestimonialsSection.vue # Customer reviews
│       │   ├── SecuritySection.vue    # Trust & security
│       │   ├── AppDownloadSection.vue # Mobile app promo
│       │   └── CTASection.vue         # Final call to action
│       ├── Footer/
│       │   ├── MainFooter.vue         # Desktop footer
│       │   ├── FooterLinks.vue        # Footer navigation columns
│       │   ├── FooterLegal.vue        # Legal text & compliance
│       │   └── MobileStickyFooter.vue # Mobile sticky actions
│       └── common/
│           ├── SectionTitle.vue       # Reusable section headers
│           └── AnimatedCounter.vue    # Stats counter animation
├── Pages/
│   └── Welcome.vue                    # Main homepage (updated)
└── Layouts/
    └── GuestLayout.vue                # Public pages layout
```

---

## 📋 Implementation Tasks

### Phase 1: Foundation & Layout
- [ ] **Task 1.1** - Create GuestLayout.vue with responsive structure
- [ ] **Task 1.2** - Create Preloader.vue with bank logo animation
- [ ] **Task 1.3** - Setup CSS variables for banking color scheme

### Phase 2: Header & Navigation
- [ ] **Task 2.1** - Create DesktopNav.vue with sticky header
- [ ] **Task 2.2** - Create MobileNav.vue with slide-out menu
- [ ] **Task 2.3** - Add header scroll effects (shrink, shadow)

### Phase 3: Hero Banner Section
- [ ] **Task 3.1** - Create HeroBanner.vue with auto-sliding
- [ ] **Task 3.2** - Add text overlay animations
- [ ] **Task 3.3** - Add navigation dots/arrows
- [ ] **Task 3.4** - Mobile-optimized hero sizing

### Phase 4: Content Sections
- [ ] **Task 4.1** - AboutSection.vue with company info
- [ ] **Task 4.2** - ServicesSection.vue (6 banking services)
- [ ] **Task 4.3** - FeaturesSection.vue (why choose us)
- [ ] **Task 4.4** - StatsSection.vue with animated counters
- [ ] **Task 4.5** - TestimonialsSection.vue with carousel
- [ ] **Task 4.6** - SecuritySection.vue (trust badges)
- [ ] **Task 4.7** - AppDownloadSection.vue
- [ ] **Task 4.8** - CTASection.vue (open account CTA)

### Phase 5: Footer
- [ ] **Task 5.1** - MainFooter.vue with 4-column layout
- [ ] **Task 5.2** - FooterLinks.vue navigation columns
- [ ] **Task 5.3** - FooterLegal.vue with compliance text
- [ ] **Task 5.4** - MobileStickyFooter.vue with Login/Register

### Phase 6: Integration & Polish
- [ ] **Task 6.1** - Integrate all components in Welcome.vue
- [ ] **Task 6.2** - Add scroll animations (AOS or custom)
- [ ] **Task 6.3** - Performance optimization
- [ ] **Task 6.4** - Cross-browser testing
- [ ] **Task 6.5** - Final responsive adjustments

---

## 🎨 Design Specifications

### Color Palette
```css
--primary:          #1e3a5f;    /* Deep Navy Blue */
--primary-dark:     #0f2744;    /* Darker Navy */
--primary-light:    #2c5282;    /* Lighter Navy */
--secondary:        #c9a227;    /* Gold/Amber */
--secondary-light:  #d4af37;    /* Light Gold */
--accent:           #38a169;    /* Success Green */
--background:       #f7fafc;    /* Light Gray */
--surface:          #ffffff;    /* White */
--text-primary:     #1a202c;    /* Dark Gray */
--text-secondary:   #4a5568;    /* Medium Gray */
--text-muted:       #718096;    /* Light Gray */
```

### Typography
```css
--font-heading:     'Inter', 'SF Pro Display', sans-serif;
--font-body:        'Inter', 'SF Pro Text', sans-serif;

/* Sizes */
--text-hero:        3.5rem (desktop) / 2rem (mobile)
--text-h1:          2.5rem (desktop) / 1.75rem (mobile)
--text-h2:          2rem (desktop) / 1.5rem (mobile)
--text-h3:          1.5rem (desktop) / 1.25rem (mobile)
--text-body:        1rem
--text-small:       0.875rem
```

---

## 📐 Section Specifications

### 1. Preloader
- **Duration:** 2-3 seconds max
- **Animation:** Bank logo pulse/fade with loading bar
- **Behavior:** Disappears on page load complete
- **Mobile:** Same but smaller logo

### 2. Header/Navigation
**Desktop:**
- Fixed position, 80px height
- Logo left, Nav center, CTA buttons right
- On scroll: shrink to 60px, add shadow
- Transparent initially, solid on scroll

**Mobile:**
- Fixed position, 60px height
- Logo left, hamburger right
- Slide-out menu from right
- Full-screen overlay menu

**Nav Links:**
- Home
- About Us
- Services
- Online Banking
- Cards
- Loans
- Contact

### 3. Hero Banner (Auto-Sliding)
**Slides (4-5 total):**
1. "Welcome to Finora Bank" - Main brand message
2. "Secure Online Banking" - Digital services
3. "Personal & Business Loans" - Loan products
4. "Premium Banking Cards" - Card services
5. "24/7 Customer Support" - Support availability

**Features:**
- Auto-slide every 5 seconds
- Manual navigation (dots + arrows)
- Pause on hover (desktop)
- Text overlay with animations
- Full viewport height on desktop
- 70vh on mobile

**Text Overlay Structure:**
```
[Small Tag] - e.g., "WELCOME TO FINORA"
[Main Heading] - e.g., "Banking Made Simple"
[Subheading] - e.g., "Experience seamless digital banking..."
[CTA Buttons] - "Get Started" | "Learn More"
```

### 4. About Section
- Split layout: Image left, content right
- Company mission statement
- Years of experience badge
- Key highlights (3-4 points)
- "Learn More" button
- Mobile: Stack vertically

### 5. Services Section
**6 Service Cards:**
1. Personal Banking - Savings, checking accounts
2. Business Banking - Business accounts, merchant services
3. Online Banking - 24/7 digital access
4. Loans & Mortgages - Personal, auto, home loans
5. Credit Cards - Rewards, travel, business cards
6. Investments - Wealth management, advisory

**Card Design:**
- Icon (outlined style)
- Title
- Brief description
- "Learn More" link
- Hover animation

### 6. Features/Why Choose Us
**4 Key Features:**
1. Security First - Bank-grade encryption
2. 24/7 Access - Anytime, anywhere banking
3. Low Fees - Competitive rates
4. Expert Support - Dedicated assistance

### 7. Stats Section
**Animated Counters:**
- 15+ Years of Excellence
- 2M+ Happy Customers
- $50B+ Assets Managed
- 99.9% Uptime Guarantee

### 8. Testimonials Section
- Carousel with customer quotes
- Customer photo, name, role
- Star rating
- Auto-rotate every 6 seconds

### 9. Security Section
**Trust Badges:**
- FDIC Insured
- SSL Secured
- PCI DSS Compliant
- 256-bit Encryption

### 10. App Download Section
- Phone mockup image
- App features list
- App Store & Play Store buttons
- QR code option

### 11. CTA Section
- Strong headline
- Subtext
- "Open an Account" button
- Background gradient or image

---

## 🦶 Footer Specifications

### Desktop Footer (4 Columns + Bottom Bar)

**Column 1 - About:**
```
[LOGO]
Finora Bank is a leading financial institution 
providing innovative banking solutions for 
individuals and businesses worldwide.

[Social Icons: Facebook, Twitter, LinkedIn, Instagram]
```

**Column 2 - Quick Links:**
```
QUICK LINKS
├── About Us
├── Our Services
├── Online Banking
├── Mobile Banking
├── Careers
└── Contact Us
```

**Column 3 - Banking Services:**
```
BANKING SERVICES
├── Personal Accounts
├── Business Accounts
├── Credit Cards
├── Loans & Mortgages
├── Investments
└── Insurance
```

**Column 4 - Support & Legal:**
```
SUPPORT
├── Help Center
├── FAQs
├── Security Center
├── Report Fraud
├── Accessibility
└── Sitemap

CONTACT
📍 123 Financial District, New York, NY 10004
📞 1-800-FINORA (346-672)
✉️ support@finorabank.com
```

**Bottom Bar:**
```
────────────────────────────────────────────────────────
© 2026 Finora Bank. All Rights Reserved.

Member FDIC | Equal Housing Lender 🏠 | NMLS #123456

Privacy Policy | Terms of Service | Cookie Policy | 
Accessibility | Security | Disclosures

Banking products and services are provided by Finora Bank, N.A.
Member FDIC. Equal Housing Lender. NMLS ID 123456.
────────────────────────────────────────────────────────
```

### Mobile Footer
- Collapsed accordion sections
- Simplified layout
- Essential links only

### Mobile Sticky Footer (Fixed Bottom)
```
┌─────────────────────────────────────────┐
│  [LOGIN]            │        [REGISTER] │
│   Button            │         Button    │
└─────────────────────────────────────────┘
Height: 60px
Position: Fixed bottom
Z-index: 1000
Background: White with shadow
```

---

## ⚡ Animations & Effects

### Scroll Animations
- Fade in up for sections
- Slide in for cards
- Counter animation for stats
- Parallax for hero background

### Hover Effects
- Button scale + shadow
- Card lift effect
- Link underline animation
- Image zoom on service cards

### Transitions
- Page transitions via Inertia
- Smooth scroll for anchor links
- Menu slide animation

---

## 🔧 Technical Implementation

### Dependencies (Already Installed)
- Vue 3 with Composition API
- PrimeVue 4.x (components)
- Tailwind CSS 4.x (styling)
- Inertia.js (routing)

### New Dependencies (If Needed)
- Swiper.js - For hero carousel (optional, can use PrimeVue Carousel)
- AOS - Animate on scroll (optional, can use Intersection Observer)

### Image Assets Needed
```
public/images/
├── logo/
│   ├── logo-dark.svg
│   └── logo-light.svg
├── hero/
│   ├── slide-1.jpg (1920x1080)
│   ├── slide-2.jpg
│   ├── slide-3.jpg
│   ├── slide-4.jpg
│   └── slide-5.jpg
├── about/
│   └── about-image.jpg
├── services/
│   └── [service-icons].svg
├── app/
│   └── phone-mockup.png
└── misc/
    ├── pattern-bg.svg
    └── wave-divider.svg
```

---

## 🚀 Deployment Workflow

After each task completion:
```bash
# 1. Build Vue assets locally
npm run build

# 2. Commit changes
git add -A
git commit -m "feat: [description]"

# 3. Push to GitHub
git push origin main

# 4. Pull on production server
ssh finora-server "cd public_html && git pull origin main"

# 5. Clear caches if needed
ssh finora-server "cd public_html && /usr/php85/usr/bin/php artisan view:clear"
```

---

## ✅ Progress Tracking

### Current Status: Planning Complete ✓

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 1: Foundation | 🔲 Not Started | |
| Phase 2: Header/Nav | 🔲 Not Started | |
| Phase 3: Hero Banner | 🔲 Not Started | |
| Phase 4: Sections | 🔲 Not Started | |
| Phase 5: Footer | 🔲 Not Started | |
| Phase 6: Polish | 🔲 Not Started | |

---

## 📝 Notes

- **Mobile First:** Always design mobile layout first, then enhance for desktop
- **Performance:** Use lazy loading for images below the fold
- **SEO:** Proper heading hierarchy, meta tags, semantic HTML
- **Accessibility:** Alt texts, ARIA labels, keyboard navigation
- **Testing:** Test on real devices, not just browser dev tools

---

*Document created: January 26, 2026*
*Last updated: January 26, 2026*
