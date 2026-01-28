/**
 * Constants for Finora Bank
 */

// Transaction types
export const TRANSACTION_TYPES = {
    CREDIT: 'credit',
    DEBIT: 'debit',
    TRANSFER: 'transfer',
    DEPOSIT: 'deposit',
    WITHDRAWAL: 'withdrawal',
    FEE: 'fee',
    REFUND: 'refund',
};

// Transaction status
export const TRANSACTION_STATUS = {
    PENDING: 'pending',
    PROCESSING: 'processing',
    COMPLETED: 'completed',
    FAILED: 'failed',
    CANCELLED: 'cancelled',
    REVERSED: 'reversed',
};

// Transfer types
export const TRANSFER_TYPES = {
    WIRE: 'wire',
    INTERNAL: 'internal',
    DOMESTIC: 'domestic',
    ACCOUNT_TO_ACCOUNT: 'account_to_account',
};

// Account types
export const ACCOUNT_TYPES = {
    SAVINGS: 'savings',
    CHECKING: 'checking',
    BUSINESS: 'business',
    FIXED_DEPOSIT: 'fixed_deposit',
};

// Card types
export const CARD_TYPES = {
    DEBIT: 'debit',
    CREDIT: 'credit',
    VIRTUAL: 'virtual',
    PREPAID: 'prepaid',
};

// Card status
export const CARD_STATUS = {
    ACTIVE: 'active',
    FROZEN: 'frozen',
    BLOCKED: 'blocked',
    EXPIRED: 'expired',
    PENDING: 'pending',
};

// Loan status
export const LOAN_STATUS = {
    PENDING: 'pending',
    APPROVED: 'approved',
    REJECTED: 'rejected',
    ACTIVE: 'active',
    PAID: 'paid',
    DEFAULTED: 'defaulted',
};

// KYC status
export const KYC_STATUS = {
    NOT_STARTED: 'not_started',
    PENDING: 'pending',
    VERIFIED: 'verified',
    REJECTED: 'rejected',
};

// Support ticket status
export const TICKET_STATUS = {
    OPEN: 'open',
    IN_PROGRESS: 'in_progress',
    AWAITING_REPLY: 'awaiting_reply',
    RESOLVED: 'resolved',
    CLOSED: 'closed',
};

// Ticket priority
export const TICKET_PRIORITY = {
    LOW: 'low',
    MEDIUM: 'medium',
    HIGH: 'high',
    URGENT: 'urgent',
};

// Currencies
export const CURRENCIES = [
    { code: 'USD', name: 'US Dollar', symbol: '$' },
    { code: 'EUR', name: 'Euro', symbol: '€' },
    { code: 'GBP', name: 'British Pound', symbol: '£' },
    { code: 'CAD', name: 'Canadian Dollar', symbol: 'C$' },
    { code: 'AUD', name: 'Australian Dollar', symbol: 'A$' },
    { code: 'NGN', name: 'Nigerian Naira', symbol: '₦' },
    { code: 'KES', name: 'Kenyan Shilling', symbol: 'KSh' },
    { code: 'ZAR', name: 'South African Rand', symbol: 'R' },
    { code: 'GHS', name: 'Ghanaian Cedi', symbol: '₵' },
    { code: 'INR', name: 'Indian Rupee', symbol: '₹' },
];

// Cryptocurrencies
export const CRYPTOCURRENCIES = [
    { code: 'BTC', name: 'Bitcoin', icon: 'bitcoin' },
    { code: 'ETH', name: 'Ethereum', icon: 'ethereum' },
    { code: 'USDT', name: 'Tether', icon: 'tether' },
    { code: 'USDC', name: 'USD Coin', icon: 'usdc' },
];

// Status colors for badges
export const STATUS_COLORS = {
    pending: 'warning',
    processing: 'info',
    completed: 'success',
    failed: 'danger',
    cancelled: 'secondary',
    active: 'success',
    inactive: 'secondary',
    frozen: 'info',
    blocked: 'danger',
    verified: 'success',
    rejected: 'danger',
    open: 'info',
    resolved: 'success',
    closed: 'secondary',
};

// Transaction icons
export const TRANSACTION_ICONS = {
    credit: 'pi pi-arrow-down',
    debit: 'pi pi-arrow-up',
    transfer: 'pi pi-arrows-h',
    deposit: 'pi pi-download',
    withdrawal: 'pi pi-upload',
    fee: 'pi pi-percentage',
    refund: 'pi pi-replay',
};

// Navigation items for dashboard (Desktop - More items separated)
export const NAV_ITEMS = [
    {
        name: 'Dashboard',
        href: '/dashboard',
        icon: 'pi pi-home'
    },
    {
        name: 'Accounts',
        href: '/accounts',
        icon: 'pi pi-wallet',
        children: [
            { name: 'My Accounts', href: '/accounts', icon: 'pi pi-wallet' },
            { name: 'Transaction History', href: '/transactions', icon: 'pi pi-history' },
            { name: 'Beneficiaries', href: '/beneficiaries', icon: 'pi pi-users' },
        ]
    },
    {
        name: 'Transfers',
        href: '/transfers',
        icon: 'pi pi-send',
        children: [
            { name: 'Wire Transfer', href: '/transfers/wire', icon: 'pi pi-globe' },
            { name: 'Domestic Transfer', href: '/transfers/domestic', icon: 'pi pi-map-marker' },
            { name: 'Internal Transfer', href: '/transfers/internal', icon: 'pi pi-arrow-right-arrow-left' },
        ]
    },
    {
        name: 'Deposits',
        href: '/deposits',
        icon: 'pi pi-download',
        children: [
            { name: 'Mobile Deposit', href: '/deposits/mobile', icon: 'pi pi-mobile' },
            { name: 'Check Deposit', href: '/deposits/check', icon: 'pi pi-file-check' },
            { name: 'Crypto Deposit', href: '/deposits/crypto', icon: 'pi pi-bitcoin' },
        ]
    },
    {
        name: 'Withdrawals',
        href: '/withdrawals',
        icon: 'pi pi-upload'
    },
    {
        name: 'Loans',
        href: '/loans',
        icon: 'pi pi-building',
        children: [
            { name: 'Loan Programs', href: '/loans', icon: 'pi pi-list' },
            { name: 'My Applications', href: '/loans/applications', icon: 'pi pi-file' },
        ]
    },
    {
        name: 'Cards',
        href: '/cards',
        icon: 'pi pi-credit-card'
    },
    {
        name: 'Grants',
        href: '/grants',
        icon: 'pi pi-gift',
        children: [
            { name: 'Grant Programs', href: '/grants', icon: 'pi pi-list' },
            { name: 'My Applications', href: '/grants/applications', icon: 'pi pi-file' },
        ]
    },
    {
        name: 'Money Requests',
        href: '/money-requests',
        icon: 'pi pi-money-bill'
    },
    {
        name: 'Exchange Money',
        href: '/exchange',
        icon: 'pi pi-sync'
    },
    {
        name: 'Tax Refunds',
        href: '/tax-refunds',
        icon: 'pi pi-percentage'
    },
    {
        name: 'Vouchers',
        href: '/vouchers',
        icon: 'pi pi-ticket'
    },
    {
        name: 'Rewards',
        href: '/rewards',
        icon: 'pi pi-star'
    },
    {
        name: 'KYC Verification',
        href: '/kyc',
        icon: 'pi pi-id-card',
        badge: 'kyc' // Special badge key for KYC status
    },
    {
        name: 'Support',
        href: '/support',
        icon: 'pi pi-comments'
    },
];

// Mobile Navigation items (More items grouped under submenu)
export const MOBILE_NAV_ITEMS = [
    {
        name: 'Dashboard',
        href: '/dashboard',
        icon: 'pi pi-home'
    },
    {
        name: 'Accounts',
        href: '/accounts',
        icon: 'pi pi-wallet',
        children: [
            { name: 'My Accounts', href: '/accounts', icon: 'pi pi-wallet' },
            { name: 'Transaction History', href: '/transactions', icon: 'pi pi-history' },
            { name: 'Beneficiaries', href: '/beneficiaries', icon: 'pi pi-users' },
        ]
    },
    {
        name: 'Transfers',
        href: '/transfers',
        icon: 'pi pi-send',
        children: [
            { name: 'Wire Transfer', href: '/transfers/wire', icon: 'pi pi-globe' },
            { name: 'Domestic Transfer', href: '/transfers/domestic', icon: 'pi pi-map-marker' },
            { name: 'Internal Transfer', href: '/transfers/internal', icon: 'pi pi-arrow-right-arrow-left' },
        ]
    },
    {
        name: 'Deposits',
        href: '/deposits',
        icon: 'pi pi-download',
        children: [
            { name: 'Mobile Deposit', href: '/deposits/mobile', icon: 'pi pi-mobile' },
            { name: 'Check Deposit', href: '/deposits/check', icon: 'pi pi-file-check' },
            { name: 'Crypto Deposit', href: '/deposits/crypto', icon: 'pi pi-bitcoin' },
        ]
    },
    {
        name: 'Withdrawals',
        href: '/withdrawals',
        icon: 'pi pi-upload'
    },
    {
        name: 'Loans',
        href: '/loans',
        icon: 'pi pi-building',
        children: [
            { name: 'Loan Programs', href: '/loans', icon: 'pi pi-list' },
            { name: 'My Applications', href: '/loans/applications', icon: 'pi pi-file' },
        ]
    },
    {
        name: 'Cards',
        href: '/cards',
        icon: 'pi pi-credit-card'
    },
    {
        name: 'Grants',
        href: '/grants',
        icon: 'pi pi-gift',
        children: [
            { name: 'Grant Programs', href: '/grants', icon: 'pi pi-list' },
            { name: 'My Applications', href: '/grants/applications', icon: 'pi pi-file' },
        ]
    },
    {
        name: 'More',
        href: '#',
        icon: 'pi pi-ellipsis-h',
        children: [
            { name: 'Money Requests', href: '/money-requests', icon: 'pi pi-money-bill' },
            { name: 'Exchange Money', href: '/exchange', icon: 'pi pi-sync' },
            { name: 'Tax Refunds', href: '/tax-refunds', icon: 'pi pi-percentage' },
            { name: 'Vouchers', href: '/vouchers', icon: 'pi pi-ticket' },
            { name: 'Rewards', href: '/rewards', icon: 'pi pi-star' },
            { name: 'KYC Verification', href: '/kyc', icon: 'pi pi-id-card', badge: 'kyc' },
        ]
    },
    {
        name: 'Support',
        href: '/support',
        icon: 'pi pi-comments'
    },
];

// Mobile bottom navigation
export const BOTTOM_NAV_ITEMS = [
    { name: 'Home', href: '/dashboard', icon: 'pi pi-home' },
    { name: 'Cards', href: '/cards', icon: 'pi pi-credit-card' },
    { name: 'Send', href: '/transfers', icon: 'pi pi-send' },
    { name: 'Profile', href: '/profile', icon: 'pi pi-user' },
];

// Breakpoints (matching Tailwind)
export const BREAKPOINTS = {
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280,
    '2xl': 1536,
};

export default {
    TRANSACTION_TYPES,
    TRANSACTION_STATUS,
    TRANSFER_TYPES,
    ACCOUNT_TYPES,
    CARD_TYPES,
    CARD_STATUS,
    LOAN_STATUS,
    KYC_STATUS,
    TICKET_STATUS,
    TICKET_PRIORITY,
    CURRENCIES,
    CRYPTOCURRENCIES,
    STATUS_COLORS,
    TRANSACTION_ICONS,
    NAV_ITEMS,
    MOBILE_NAV_ITEMS,
    BOTTOM_NAV_ITEMS,
    BREAKPOINTS,
};
