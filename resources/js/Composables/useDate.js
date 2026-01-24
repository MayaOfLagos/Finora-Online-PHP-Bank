/**
 * Date formatting composable
 * Provides utility functions for date formatting
 */

export function useDate() {
    /**
     * Format date to readable string (e.g., "Jan 24, 2026")
     */
    const formatDate = (date) => {
        if (!date) return '';
        const d = new Date(date);
        return d.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    /**
     * Format date and time (e.g., "Jan 24, 2026 10:30 AM")
     */
    const formatDateTime = (date) => {
        if (!date) return '';
        const d = new Date(date);
        return d.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    };

    /**
     * Format time only (e.g., "10:30 AM")
     */
    const formatTime = (date) => {
        if (!date) return '';
        const d = new Date(date);
        return d.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    };

    /**
     * Get relative time (e.g., "2 hours ago", "3 days ago")
     */
    const timeAgo = (date) => {
        if (!date) return '';

        const now = new Date();
        const d = new Date(date);
        const seconds = Math.floor((now - d) / 1000);

        const intervals = {
            year: 31536000,
            month: 2592000,
            week: 604800,
            day: 86400,
            hour: 3600,
            minute: 60
        };

        for (const [unit, secondsInUnit] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / secondsInUnit);
            if (interval >= 1) {
                return interval === 1
                    ? `${interval} ${unit} ago`
                    : `${interval} ${unit}s ago`;
            }
        }

        return 'Just now';
    };

    /**
     * Format ISO date string for input fields (YYYY-MM-DD)
     */
    const formatISODate = (date) => {
        if (!date) return '';
        const d = new Date(date);
        return d.toISOString().split('T')[0];
    };

    /**
     * Check if date is today
     */
    const isToday = (date) => {
        if (!date) return false;
        const d = new Date(date);
        const today = new Date();
        return d.toDateString() === today.toDateString();
    };

    /**
     * Check if date is yesterday
     */
    const isYesterday = (date) => {
        if (!date) return false;
        const d = new Date(date);
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);
        return d.toDateString() === yesterday.toDateString();
    };

    /**
     * Get smart date display (Today, Yesterday, or formatted date)
     */
    const smartDate = (date) => {
        if (!date) return '';
        if (isToday(date)) return 'Today';
        if (isYesterday(date)) return 'Yesterday';
        return formatDate(date);
    };

    return {
        formatDate,
        formatDateTime,
        formatTime,
        timeAgo,
        formatISODate,
        isToday,
        isYesterday,
        smartDate
    };
}
