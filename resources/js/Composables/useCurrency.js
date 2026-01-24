/**
 * useCurrency Composable
 * Currency formatting utilities
 */
import { computed } from 'vue';

const CURRENCY_SYMBOLS = {
    USD: '$',
    EUR: '€',
    GBP: '£',
    NGN: '₦',
    GHS: '₵',
    KES: 'KSh',
    ZAR: 'R',
    AED: 'د.إ',
    INR: '₹',
    JPY: '¥',
    CNY: '¥',
    CAD: 'C$',
    AUD: 'A$',
};

const CURRENCY_LOCALES = {
    USD: 'en-US',
    EUR: 'de-DE',
    GBP: 'en-GB',
    NGN: 'en-NG',
    GHS: 'en-GH',
    KES: 'en-KE',
    ZAR: 'en-ZA',
    AED: 'ar-AE',
    INR: 'en-IN',
    JPY: 'ja-JP',
    CNY: 'zh-CN',
    CAD: 'en-CA',
    AUD: 'en-AU',
};

export function useCurrency(defaultCurrency = 'USD') {
    /**
     * Format amount in cents to currency string
     */
    const formatCurrency = (cents, currency = defaultCurrency, options = {}) => {
        if (cents === null || cents === undefined) return '—';

        const locale = CURRENCY_LOCALES[currency] || 'en-US';
        const amount = cents / 100;

        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: options.decimals ?? 2,
            maximumFractionDigits: options.decimals ?? 2,
        }).format(amount);
    };

    /**
     * Format amount with compact notation (e.g., $1.2K, $3.5M)
     */
    const formatCompact = (cents, currency = defaultCurrency) => {
        if (cents === null || cents === undefined) return '—';

        const amount = cents / 100;
        const locale = CURRENCY_LOCALES[currency] || 'en-US';

        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency,
            notation: 'compact',
            maximumFractionDigits: 1,
        }).format(amount);
    };

    /**
     * Get currency symbol
     */
    const getSymbol = (currency = defaultCurrency) => {
        return CURRENCY_SYMBOLS[currency] || currency;
    };

    /**
     * Parse currency string to cents
     */
    const parseToCents = (value) => {
        if (typeof value === 'number') return Math.round(value * 100);
        if (typeof value === 'string') {
            const cleaned = value.replace(/[^0-9.-]/g, '');
            return Math.round(parseFloat(cleaned) * 100);
        }
        return 0;
    };

    /**
     * Format as sign-prefixed amount (for transactions)
     */
    const formatWithSign = (cents, currency = defaultCurrency, type = 'credit') => {
        const formatted = formatCurrency(Math.abs(cents), currency);
        if (type === 'credit' || type === 'deposit') {
            return `+${formatted}`;
        }
        return `-${formatted}`;
    };

    return {
        formatCurrency,
        formatCompact,
        getSymbol,
        parseToCents,
        formatWithSign,
        CURRENCY_SYMBOLS,
        CURRENCY_LOCALES,
    };
}
