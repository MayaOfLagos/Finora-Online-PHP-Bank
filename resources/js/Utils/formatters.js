/**
 * Formatters for Finora Bank
 * Currency, dates, account numbers, etc.
 */

/**
 * Format amount as currency
 * @param {number} amount - Amount in cents or dollars
 * @param {string} currency - Currency code (USD, EUR, etc.)
 * @param {boolean} fromCents - Whether amount is in cents
 * @returns {string} Formatted currency string
 */
export function formatCurrency(amount, currency = 'USD', fromCents = true) {
    const value = fromCents ? amount / 100 : amount;

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
}

/**
 * Format amount as compact currency (e.g., $1.2K, $3.5M)
 * @param {number} amount - Amount in cents or dollars
 * @param {string} currency - Currency code
 * @param {boolean} fromCents - Whether amount is in cents
 * @returns {string} Compact formatted currency
 */
export function formatCompactCurrency(amount, currency = 'USD', fromCents = true) {
    const value = fromCents ? amount / 100 : amount;

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(value);
}

/**
 * Format date relative to now (e.g., "2 hours ago", "Yesterday")
 * @param {string|Date} date - Date to format
 * @returns {string} Relative date string
 */
export function formatRelativeDate(date) {
    const now = new Date();
    const then = new Date(date);
    const diffMs = now - then;
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSecs < 60) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;

    return formatDate(date);
}

/**
 * Format date as readable string
 * @param {string|Date} date - Date to format
 * @param {string} format - Format style ('short', 'medium', 'long')
 * @returns {string} Formatted date string
 */
export function formatDate(date, format = 'medium') {
    const options = {
        short: { month: 'short', day: 'numeric' },
        medium: { month: 'short', day: 'numeric', year: 'numeric' },
        long: { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' },
        datetime: { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' },
    };

    return new Intl.DateTimeFormat('en-US', options[format] || options.medium).format(new Date(date));
}

/**
 * Format account number with masking (e.g., ****1234)
 * @param {string} accountNumber - Full account number
 * @param {number} visibleDigits - Number of digits to show at end
 * @returns {string} Masked account number
 */
export function maskAccountNumber(accountNumber, visibleDigits = 4) {
    if (!accountNumber) return '';
    const str = String(accountNumber);
    if (str.length <= visibleDigits) return str;
    return '****' + str.slice(-visibleDigits);
}

/**
 * Format account number with spaces for readability
 * @param {string} accountNumber - Account number
 * @param {number} groupSize - Size of each group
 * @returns {string} Formatted account number
 */
export function formatAccountNumber(accountNumber, groupSize = 4) {
    if (!accountNumber) return '';
    const str = String(accountNumber).replace(/\s/g, '');
    return str.match(new RegExp(`.{1,${groupSize}}`, 'g'))?.join(' ') || str;
}

/**
 * Format card number with masking (e.g., **** **** **** 1234)
 * @param {string} cardNumber - Full card number
 * @returns {string} Masked card number
 */
export function maskCardNumber(cardNumber) {
    if (!cardNumber) return '';
    const str = String(cardNumber).replace(/\s/g, '');
    if (str.length < 4) return str;
    return `**** **** **** ${str.slice(-4)}`;
}

/**
 * Format phone number
 * @param {string} phone - Phone number
 * @returns {string} Formatted phone number
 */
export function formatPhoneNumber(phone) {
    if (!phone) return '';
    const cleaned = String(phone).replace(/\D/g, '');
    const match = cleaned.match(/^(\d{1,3})?(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        const countryCode = match[1] ? `+${match[1]} ` : '';
        return `${countryCode}(${match[2]}) ${match[3]}-${match[4]}`;
    }
    return phone;
}

/**
 * Format percentage
 * @param {number} value - Value to format
 * @param {number} decimals - Decimal places
 * @returns {string} Formatted percentage
 */
export function formatPercentage(value, decimals = 2) {
    return `${Number(value).toFixed(decimals)}%`;
}

/**
 * Truncate text with ellipsis
 * @param {string} text - Text to truncate
 * @param {number} length - Max length
 * @returns {string} Truncated text
 */
export function truncateText(text, length = 50) {
    if (!text || text.length <= length) return text;
    return text.slice(0, length).trim() + '...';
}

/**
 * Get initials from name
 * @param {string} name - Full name
 * @returns {string} Initials (max 2 characters)
 */
export function getInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

export default {
    formatCurrency,
    formatCompactCurrency,
    formatRelativeDate,
    formatDate,
    maskAccountNumber,
    formatAccountNumber,
    maskCardNumber,
    formatPhoneNumber,
    formatPercentage,
    truncateText,
    getInitials,
};
