<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $pageKey = (string) $request->route('page');
        $pages = $this->pages();

        abort_unless(isset($pages[$pageKey]), 404);

        return Inertia::render('Public/ContentPage', [
            'page' => $pages[$pageKey],
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function pages(): array
    {
        $primaryCta = [
            'label' => 'Open an account',
            'href' => '/register',
        ];

        $secondaryCta = [
            'label' => 'Talk to a specialist',
            'href' => '/contact',
        ];

        $supportCta = [
            'label' => 'Visit support',
            'href' => '/help-center',
        ];

        return [
            'about' => [
                'eyebrow' => 'About Finora Bank',
                'title' => 'Modern banking built for trust',
                'summary' => 'We combine regulated safeguards with intuitive digital experiences so you can move money with confidence.',
                'primaryCta' => $primaryCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'What we stand for',
                        'description' => 'Security-first foundations paired with transparent service.',
                        'items' => [
                            ['title' => 'Protected accounts', 'description' => 'Layered security, encryption, and monitoring keep every session safe.'],
                            ['title' => 'Human support', 'description' => 'Real people to help when you need guidance or reassurance.'],
                            ['title' => 'Responsible banking', 'description' => 'Compliance, audits, and controls that respect your data and funds.'],
                        ],
                    ],
                    [
                        'title' => 'Experience designed for you',
                        'description' => 'Digital convenience without sacrificing clarity.',
                        'items' => [
                            ['title' => 'Clear fees', 'description' => 'No hidden surprises—just upfront pricing and alerts.'],
                            ['title' => 'Every device ready', 'description' => 'Seamless on web and mobile so you can bank anywhere.'],
                            ['title' => 'Global-ready', 'description' => 'Multi-currency readiness for travel and international payments.'],
                        ],
                    ],
                ],
            ],
            'services' => [
                'eyebrow' => 'Our Services',
                'title' => 'Banking that adapts to how you work',
                'summary' => 'From daily spending to long-term growth, Finora brings accounts, cards, and lending together.',
                'primaryCta' => $primaryCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Everyday banking',
                        'description' => 'Essential tools for individuals and teams.',
                        'items' => [
                            ['title' => 'Secure accounts', 'description' => 'Checking and savings with spending controls and alerts.'],
                            ['title' => 'Cards and payments', 'description' => 'Instant card management, limits, and dispute support.'],
                            ['title' => 'Online banking', 'description' => 'Track balances, transfers, and bills in one secure place.'],
                        ],
                    ],
                    [
                        'title' => 'Grow with confidence',
                        'description' => 'Flexible options to scale responsibly.',
                        'items' => [
                            ['title' => 'Loans and financing', 'description' => 'Personal, auto, business, and mortgage options with guidance.'],
                            ['title' => 'Business solutions', 'description' => 'Cash flow views, approvals, and multi-user controls.'],
                            ['title' => 'Insights and reporting', 'description' => 'Track spending patterns to make faster decisions.'],
                        ],
                    ],
                ],
            ],
            'personal-banking' => [
                'eyebrow' => 'Personal Banking',
                'title' => 'Everyday banking that feels effortless',
                'summary' => 'Manage spending, savings, and transfers with intuitive tools and real-time security.',
                'primaryCta' => $primaryCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Do more in one place',
                        'description' => 'Simplify how you bank day to day.',
                        'items' => [
                            ['title' => 'Smart accounts', 'description' => 'Goal-based savings, spending insights, and bill tracking.'],
                            ['title' => 'Instant transfers', 'description' => 'Send internally, domestically, or internationally with clear steps.'],
                            ['title' => 'Card controls', 'description' => 'Freeze cards, set limits, and manage travel notices in seconds.'],
                        ],
                    ],
                    [
                        'title' => 'Security you can feel',
                        'description' => 'Built-in protections every time you log in.',
                        'items' => [
                            ['title' => 'PIN and OTP layers', 'description' => 'Transaction PINs, codes, and OTP verification protect every move.'],
                            ['title' => 'Alerts that matter', 'description' => 'Real-time notifications for payments, deposits, and logins.'],
                            ['title' => 'Fraud response', 'description' => 'Fast investigation support with easy reporting paths.'],
                        ],
                    ],
                ],
            ],
            'business-banking' => [
                'eyebrow' => 'Business Banking',
                'title' => 'Controls and clarity for growing teams',
                'summary' => 'Run finances with approval flows, role-based access, and cash flow insights built for operators.',
                'primaryCta' => $primaryCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Built for operations',
                        'description' => 'Keep spending aligned with policy.',
                        'items' => [
                            ['title' => 'Treasury-like visibility', 'description' => 'Track balances, payables, and receivables in real time.'],
                            ['title' => 'Team permissions', 'description' => 'Assign roles, approvals, and limits by department.'],
                            ['title' => 'Vendor-friendly payments', 'description' => 'Domestic, international, and recurring payments with audit trails.'],
                        ],
                    ],
                    [
                        'title' => 'Enable growth',
                        'description' => 'Flexible tools that scale with you.',
                        'items' => [
                            ['title' => 'Working capital', 'description' => 'Financing options for inventory, payroll, and expansion.'],
                            ['title' => 'Expense discipline', 'description' => 'Cards by team, spending policies, and clear statements.'],
                            ['title' => 'Compliance ready', 'description' => 'Data exports and logs to simplify reviews and audits.'],
                        ],
                    ],
                ],
            ],
            'mobile-banking' => [
                'eyebrow' => 'Mobile Banking',
                'title' => 'Secure banking from any device',
                'summary' => 'Stay on top of balances, cards, and transfers with mobile-first controls.',
                'primaryCta' => $primaryCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Everything on the go',
                        'description' => 'Access the full Finora experience from your phone.',
                        'items' => [
                            ['title' => 'Instant card actions', 'description' => 'Freeze, replace, or adjust limits without calling support.'],
                            ['title' => 'Biometric login', 'description' => 'Face and fingerprint options keep sign-in quick and safe.'],
                            ['title' => 'Smart alerts', 'description' => 'Push notifications for transfers, deposits, and security events.'],
                        ],
                    ],
                    [
                        'title' => 'Protected by design',
                        'description' => 'Mobile security that mirrors the desktop experience.',
                        'items' => [
                            ['title' => 'Encrypted sessions', 'description' => 'End-to-end encryption with session timeouts.'],
                            ['title' => 'Location-aware checks', 'description' => 'Added verification for unusual activity.'],
                            ['title' => 'One-tap support', 'description' => 'Reach a specialist right from the app when you need help.'],
                        ],
                    ],
                ],
            ],
            'loans-and-mortgages' => [
                'eyebrow' => 'Loans & Mortgages',
                'title' => 'Financing built around your goals',
                'summary' => 'Apply for personal, business, auto, or mortgage financing with guided steps and clear milestones.',
                'primaryCta' => $primaryCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Borrow with clarity',
                        'description' => 'Simple flows with transparent requirements.',
                        'items' => [
                            ['title' => 'Tailored options', 'description' => 'Programs for personal needs, vehicles, businesses, and homes.'],
                            ['title' => 'Upfront timelines', 'description' => 'Know what to expect at each approval stage.'],
                            ['title' => 'Payment confidence', 'description' => 'Autopay, schedules, and reminders reduce surprises.'],
                        ],
                    ],
                    [
                        'title' => 'Guided support',
                        'description' => 'Experts ready from application through repayment.',
                        'items' => [
                            ['title' => 'Document help', 'description' => 'Checklists and upload guidance for faster review.'],
                            ['title' => 'Rate visibility', 'description' => 'Competitive rates with no hidden fees.'],
                            ['title' => 'Early payoff friendly', 'description' => 'Pay down balances on your schedule without surprises.'],
                        ],
                    ],
                ],
            ],
            'credit-cards' => [
                'eyebrow' => 'Cards',
                'title' => 'Cards with control and rewards built in',
                'summary' => 'Issue, manage, and protect your Finora cards with instant controls and meaningful benefits.',
                'primaryCta' => $primaryCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Stay in control',
                        'description' => 'Card management without the wait.',
                        'items' => [
                            ['title' => 'Instant issuance', 'description' => 'Create virtual cards, request physical, and set spend rules fast.'],
                            ['title' => 'Real-time alerts', 'description' => 'Every transaction comes with notifications and easy dispute paths.'],
                            ['title' => 'Travel ready', 'description' => 'International support with configurable limits and notifications.'],
                        ],
                    ],
                    [
                        'title' => 'Benefits that matter',
                        'description' => 'Perks designed for everyday use.',
                        'items' => [
                            ['title' => 'Rewards focus', 'description' => 'Earn on everyday categories you actually use.'],
                            ['title' => 'Fees kept low', 'description' => 'Transparent pricing and clear grace periods.'],
                            ['title' => 'Security layers', 'description' => '3-D Secure, OTP, and strong authentication options.'],
                        ],
                    ],
                ],
            ],
            'contact' => [
                'eyebrow' => 'Contact',
                'title' => 'Talk with Finora Support',
                'summary' => 'Reach our team for account help, security questions, or guidance on getting started.',
                'primaryCta' => $secondaryCta,
                'secondaryCta' => $supportCta,
                'sections' => [
                    [
                        'title' => 'Ways to reach us',
                        'description' => 'Choose the channel that fits your day.',
                        'items' => [
                            ['title' => 'Secure messaging', 'description' => 'Send a message from your account for the fastest routing.'],
                            ['title' => 'Phone support', 'description' => 'Call 1-800-FINORA for priority help during business hours.'],
                            ['title' => 'Email experts', 'description' => 'Write to support@finorabank.com for detailed questions.'],
                        ],
                    ],
                    [
                        'title' => 'What to include',
                        'description' => 'Help us help you quickly.',
                        'items' => [
                            ['title' => 'Contact details', 'description' => 'Share the best number or email to reach you.'],
                            ['title' => 'Context', 'description' => 'Briefly describe the issue, card, or transaction involved.'],
                            ['title' => 'Timing', 'description' => 'Let us know if this is urgent or time-sensitive.'],
                        ],
                    ],
                ],
            ],
            'careers' => [
                'eyebrow' => 'Careers',
                'title' => 'Build the future of banking with us',
                'summary' => 'Join a team focused on secure, customer-first financial products.',
                'primaryCta' => [
                    'label' => 'View open roles',
                    'href' => '/careers',
                ],
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Why Finora',
                        'description' => 'We ship thoughtfully and care about impact.',
                        'items' => [
                            ['title' => 'Mission-led', 'description' => 'Access, security, and clarity guide what we build.'],
                            ['title' => 'Remote-friendly', 'description' => 'Work flexibly with strong collaboration rituals.'],
                            ['title' => 'Benefits that matter', 'description' => 'Comprehensive health, learning budgets, and wellness support.'],
                        ],
                    ],
                    [
                        'title' => 'How we hire',
                        'description' => 'A transparent, respectful process.',
                        'items' => [
                            ['title' => 'Clear stages', 'description' => 'Intro chats, skills deep-dives, and culture add conversations.'],
                            ['title' => 'Timely responses', 'description' => 'We keep you updated at every step.'],
                            ['title' => 'Practical exercises', 'description' => 'Role-relevant tasks that respect your time.'],
                        ],
                    ],
                ],
            ],
            'press' => [
                'eyebrow' => 'Press & Media',
                'title' => 'Press resources and story leads',
                'summary' => 'Find media contacts, brand assets, and recent releases.',
                'primaryCta' => [
                    'label' => 'Contact press team',
                    'href' => '/contact',
                ],
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Media resources',
                        'description' => 'Everything you need to share our story.',
                        'items' => [
                            ['title' => 'Brand kit', 'description' => 'Logos, colors, and usage guidelines for Finora assets.'],
                            ['title' => 'Leadership bios', 'description' => 'Backgrounds and headshots for leadership interviews.'],
                            ['title' => 'Product facts', 'description' => 'Key product highlights and launch timelines.'],
                        ],
                    ],
                    [
                        'title' => 'Press support',
                        'description' => 'We respond quickly to media inquiries.',
                        'items' => [
                            ['title' => 'Direct line', 'description' => 'Reach our communications team for interviews.'],
                            ['title' => 'Announcements', 'description' => 'Access our latest releases and statements.'],
                            ['title' => 'Briefings', 'description' => 'Schedule background sessions with subject experts.'],
                        ],
                    ],
                ],
            ],
            'blog' => [
                'eyebrow' => 'Blog',
                'title' => 'Insights from the Finora team',
                'summary' => 'Stay updated on product releases, security tips, and financial guidance.',
                'primaryCta' => [
                    'label' => 'Read the blog',
                    'href' => '/blog',
                ],
                'secondaryCta' => $supportCta,
                'sections' => [
                    [
                        'title' => 'What we share',
                        'description' => 'Practical updates over marketing fluff.',
                        'items' => [
                            ['title' => 'Product notes', 'description' => 'Release highlights and how to get value fast.'],
                            ['title' => 'Security guidance', 'description' => 'Actionable steps to keep your accounts safe.'],
                            ['title' => 'Money management', 'description' => 'Tips for budgeting, saving, and borrowing responsibly.'],
                        ],
                    ],
                    [
                        'title' => 'How to engage',
                        'description' => 'We love feedback and questions.',
                        'items' => [
                            ['title' => 'Suggest topics', 'description' => 'Tell us what you want to read next.'],
                            ['title' => 'Share wins', 'description' => 'Feature stories from customers and partners.'],
                            ['title' => 'Stay notified', 'description' => 'Subscribe for monthly recaps, not spam.'],
                        ],
                    ],
                ],
            ],
            'help-center' => [
                'eyebrow' => 'Help Center',
                'title' => 'Guided answers for common questions',
                'summary' => 'Find how-tos for transfers, cards, and security topics without waiting on hold.',
                'primaryCta' => $supportCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Popular topics',
                        'description' => 'Get unstuck quickly.',
                        'items' => [
                            ['title' => 'Resetting PINs', 'description' => 'Steps to update or recover your transaction PIN.'],
                            ['title' => 'Transfer walkthroughs', 'description' => 'Guides for internal, domestic, and wire transfers.'],
                            ['title' => 'Card management', 'description' => 'Freeze, replace, and dispute transactions.'],
                        ],
                    ],
                    [
                        'title' => 'More ways to learn',
                        'description' => 'Pick the format that fits best.',
                        'items' => [
                            ['title' => 'Step-by-step checklists', 'description' => 'Printable flows for repeat tasks.'],
                            ['title' => 'Video shorts', 'description' => 'Quick walkthroughs for top questions.'],
                            ['title' => 'Dedicated support', 'description' => 'Escalate to chat or phone when you need a person.'],
                        ],
                    ],
                ],
            ],
            'faqs' => [
                'eyebrow' => 'FAQs',
                'title' => 'Quick answers at a glance',
                'summary' => 'A concise list of frequent questions about accounts, cards, and security.',
                'primaryCta' => $supportCta,
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Getting started',
                        'description' => 'Account setup and onboarding basics.',
                        'items' => [
                            ['title' => 'Eligibility and documents', 'description' => 'Know what you need before applying.'],
                            ['title' => 'Account activation', 'description' => 'Turn on online banking and mobile access.'],
                            ['title' => 'Deposit options', 'description' => 'Choose mobile, check, crypto, or external transfers.'],
                        ],
                    ],
                    [
                        'title' => 'Security and support',
                        'description' => 'Stay protected while you bank.',
                        'items' => [
                            ['title' => 'Two-factor authentication', 'description' => 'Set up OTP and device verification.'],
                            ['title' => 'Travel safety', 'description' => 'Prepare cards for trips and reduce declines.'],
                            ['title' => 'Help channels', 'description' => 'Pick chat, phone, or email for the fastest path.'],
                        ],
                    ],
                ],
            ],
            'security-center' => [
                'eyebrow' => 'Security Center',
                'title' => 'Security is built into every workflow',
                'summary' => 'Understand the controls, monitoring, and education we provide to keep your money safe.',
                'primaryCta' => $secondaryCta,
                'secondaryCta' => [
                    'label' => 'Report an issue',
                    'href' => '/report-fraud',
                ],
                'sections' => [
                    [
                        'title' => 'Protection layers',
                        'description' => 'Defense in depth across the platform.',
                        'items' => [
                            ['title' => 'Authentication controls', 'description' => 'PIN, OTP, and device checks on sensitive actions.'],
                            ['title' => 'Monitoring and alerts', 'description' => 'Behavioral monitoring with real-time notifications.'],
                            ['title' => 'Data safeguards', 'description' => 'Encryption at rest and in transit with strict access policies.'],
                        ],
                    ],
                    [
                        'title' => 'Your part in security',
                        'description' => 'Shared responsibility keeps accounts protected.',
                        'items' => [
                            ['title' => 'Recognize phishing', 'description' => 'Verify senders and never share OTPs.'],
                            ['title' => 'Device hygiene', 'description' => 'Use up-to-date operating systems and screen locks.'],
                            ['title' => 'Secure sharing', 'description' => 'Use official channels for support and verification.'],
                        ],
                    ],
                ],
            ],
            'report-fraud' => [
                'eyebrow' => 'Report Fraud',
                'title' => 'If something looks wrong, tell us fast',
                'summary' => 'We prioritize fraud and security reports with dedicated specialists.',
                'primaryCta' => [
                    'label' => 'Report suspicious activity',
                    'href' => '/contact',
                ],
                'secondaryCta' => [
                    'label' => 'Call 1-800-FINORA',
                    'href' => 'tel:1800346672',
                ],
                'sections' => [
                    [
                        'title' => 'What to do first',
                        'description' => 'Lock things down quickly.',
                        'items' => [
                            ['title' => 'Freeze cards', 'description' => 'Use card controls to pause spending immediately.'],
                            ['title' => 'Change credentials', 'description' => 'Update passwords and enable 2FA if you have not already.'],
                            ['title' => 'Document details', 'description' => 'Note dates, amounts, and any messages received.'],
                        ],
                    ],
                    [
                        'title' => 'How we respond',
                        'description' => 'A structured process with clear updates.',
                        'items' => [
                            ['title' => 'Rapid review', 'description' => 'Specialists investigate and secure the account.'],
                            ['title' => 'Temporary holds', 'description' => 'We can hold transactions while we validate legitimacy.'],
                            ['title' => 'Communication', 'description' => 'We keep you informed until the case is closed.'],
                        ],
                    ],
                ],
            ],
            'atm-locator' => [
                'eyebrow' => 'ATM Locator',
                'title' => 'Find surcharge-free ATMs near you',
                'summary' => 'Access cash without unexpected fees across our network.',
                'primaryCta' => [
                    'label' => 'Use locator',
                    'href' => '/atm-locator',
                ],
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Network coverage',
                        'description' => 'Plan trips and daily errands with confidence.',
                        'items' => [
                            ['title' => 'Nationwide access', 'description' => 'Thousands of partner ATMs with clear fee disclosures.'],
                            ['title' => 'International readiness', 'description' => 'Find global options and know what fees may apply.'],
                            ['title' => 'Accessibility filters', 'description' => 'Locate ATMs with ramps and 24/7 availability.'],
                        ],
                    ],
                    [
                        'title' => 'Best practices',
                        'description' => 'Keep withdrawals secure.',
                        'items' => [
                            ['title' => 'Inspect machines', 'description' => 'Avoid ATMs with loose parts or unusual readers.'],
                            ['title' => 'Shield PIN entry', 'description' => 'Cover the keypad and stay aware of surroundings.'],
                            ['title' => 'Travel alerts', 'description' => 'Set travel notices to reduce unnecessary declines.'],
                        ],
                    ],
                ],
            ],
            'fees' => [
                'eyebrow' => 'Fee Schedule',
                'title' => 'Transparent pricing with no surprises',
                'summary' => 'Know what you pay for accounts, cards, and services before you start.',
                'primaryCta' => [
                    'label' => 'View fees',
                    'href' => '/fees',
                ],
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Accounts and deposits',
                        'description' => 'Simple pricing that respects your balance.',
                        'items' => [
                            ['title' => 'Monthly maintenance', 'description' => 'Low or no fees with qualifying activity.'],
                            ['title' => 'ATM policies', 'description' => 'Know when surcharges apply and how to avoid them.'],
                            ['title' => 'Deposit methods', 'description' => 'Mobile, wire, and check deposit expectations.'],
                        ],
                    ],
                    [
                        'title' => 'Cards and lending',
                        'description' => 'Clarity for spending and borrowing.',
                        'items' => [
                            ['title' => 'Card fees', 'description' => 'Replacement, rush shipping, and foreign transaction details.'],
                            ['title' => 'Loan transparency', 'description' => 'Rate structures, late fees, and prepayment terms.'],
                            ['title' => 'Dispute handling', 'description' => 'What to expect if a merchant charge is challenged.'],
                        ],
                    ],
                ],
            ],
            'privacy-policy' => [
                'eyebrow' => 'Privacy Policy',
                'title' => 'Your data, protected and respected',
                'summary' => 'Learn how we collect, use, and safeguard your information across Finora experiences.',
                'primaryCta' => [
                    'label' => 'Review privacy policy',
                    'href' => '/privacy-policy',
                ],
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'How we use data',
                        'description' => 'Purpose-driven data practices.',
                        'items' => [
                            ['title' => 'Service delivery', 'description' => 'Data is used to provide banking features you request.'],
                            ['title' => 'Security and fraud', 'description' => 'Monitoring to keep accounts safe and compliant.'],
                            ['title' => 'No unnecessary sharing', 'description' => 'We do not sell personal data and limit third-party access.'],
                        ],
                    ],
                    [
                        'title' => 'Your controls',
                        'description' => 'Manage preferences easily.',
                        'items' => [
                            ['title' => 'Communication choices', 'description' => 'Set notification and marketing preferences anytime.'],
                            ['title' => 'Data access', 'description' => 'Request copies or corrections to your information.'],
                            ['title' => 'Retention clarity', 'description' => 'See how long we keep different data types.'],
                        ],
                    ],
                ],
            ],
            'terms' => [
                'eyebrow' => 'Terms of Service',
                'title' => 'Understand your agreement with Finora',
                'summary' => 'Review the terms that govern your use of Finora Bank products and services.',
                'primaryCta' => [
                    'label' => 'Read terms',
                    'href' => '/terms',
                ],
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Account usage',
                        'description' => 'Expectations for responsible banking.',
                        'items' => [
                            ['title' => 'Eligibility', 'description' => 'Who can open and maintain accounts with Finora.'],
                            ['title' => 'Acceptable use', 'description' => 'Guidelines for transactions and platform behavior.'],
                            ['title' => 'Limits and holds', 'description' => 'When holds apply and how we handle disputes.'],
                        ],
                    ],
                    [
                        'title' => 'Our commitments',
                        'description' => 'What you can expect from us.',
                        'items' => [
                            ['title' => 'Service availability', 'description' => 'How we plan for uptime and maintenance windows.'],
                            ['title' => 'Notifications', 'description' => 'How we communicate changes to terms or policies.'],
                            ['title' => 'Support standards', 'description' => 'Response expectations for various request types.'],
                        ],
                    ],
                ],
            ],
            'cookie-policy' => [
                'eyebrow' => 'Cookie Policy',
                'title' => 'How we use cookies and similar technologies',
                'summary' => 'Understand what data is captured through cookies and how to control preferences.',
                'primaryCta' => [
                    'label' => 'Manage cookies',
                    'href' => '/cookie-policy',
                ],
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Why cookies matter',
                        'description' => 'Better experiences with transparency.',
                        'items' => [
                            ['title' => 'Essential operations', 'description' => 'Keep sessions secure and stable.'],
                            ['title' => 'Performance insights', 'description' => 'Measure what works to improve usability.'],
                            ['title' => 'Preference storage', 'description' => 'Remember settings like language or theme.'],
                        ],
                    ],
                    [
                        'title' => 'Your choices',
                        'description' => 'Control how cookies are used.',
                        'items' => [
                            ['title' => 'Opt-in layers', 'description' => 'Adjust non-essential cookies anytime.'],
                            ['title' => 'Do Not Track', 'description' => 'How we handle browser signals where applicable.'],
                            ['title' => 'Third-party tools', 'description' => 'See which partners support our services.'],
                        ],
                    ],
                ],
            ],
            'accessibility' => [
                'eyebrow' => 'Accessibility',
                'title' => 'Banking that includes everyone',
                'summary' => 'We design Finora experiences to be usable and welcoming for all customers.',
                'primaryCta' => [
                    'label' => 'Accessibility support',
                    'href' => '/contact',
                ],
                'secondaryCta' => $secondaryCta,
                'sections' => [
                    [
                        'title' => 'Design principles',
                        'description' => 'Accessibility is part of our build process.',
                        'items' => [
                            ['title' => 'WCAG alignment', 'description' => 'Interfaces designed with industry accessibility standards.'],
                            ['title' => 'Responsive layouts', 'description' => 'Readable typography and adaptable components.'],
                            ['title' => 'Keyboard friendly', 'description' => 'Navigation and forms that work without a mouse.'],
                        ],
                    ],
                    [
                        'title' => 'Support when you need it',
                        'description' => 'We tailor assistance to your needs.',
                        'items' => [
                            ['title' => 'Alternate formats', 'description' => 'Request statements or disclosures in accessible formats.'],
                            ['title' => 'Assistive tech support', 'description' => 'Guidance for screen readers and voice input.'],
                            ['title' => 'Feedback loop', 'description' => 'Tell us where to improve and we will respond.'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
