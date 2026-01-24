<script setup>
/**
 * CurrencyDisplay Component
 * Displays formatted currency with optional color coding
 */
import { computed } from 'vue';
import { formatCurrency, formatCompactCurrency } from '@/Utils/formatters';

const props = defineProps({
    amount: {
        type: Number,
        required: true,
    },
    currency: {
        type: String,
        default: 'USD',
    },
    fromCents: {
        type: Boolean,
        default: true,
    },
    colorCode: {
        type: Boolean,
        default: false, // Show green for positive, red for negative
    },
    compact: {
        type: Boolean,
        default: false, // Use compact notation (1.2K, 3.5M)
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl
    },
    showSign: {
        type: Boolean,
        default: false, // Show + for positive amounts
    },
    hideBalance: {
        type: Boolean,
        default: false, // Show **** instead of balance
    },
});

const formattedAmount = computed(() => {
    if (props.hideBalance) {
        return '****';
    }

    const formatter = props.compact ? formatCompactCurrency : formatCurrency;
    let formatted = formatter(props.amount, props.currency, props.fromCents);

    if (props.showSign && props.amount > 0) {
        formatted = '+' + formatted;
    }

    return formatted;
});

const colorClass = computed(() => {
    if (!props.colorCode) return '';

    const value = props.fromCents ? props.amount / 100 : props.amount;
    if (value > 0) return 'text-green-600 dark:text-green-400';
    if (value < 0) return 'text-red-600 dark:text-red-400';
    return 'text-gray-600 dark:text-gray-400';
});

const sizeClasses = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-2xl',
    '2xl': 'text-3xl',
};
</script>

<template>
    <span
        :class="[
            sizeClasses[size],
            colorClass,
            'font-semibold tabular-nums',
        ]"
    >
        {{ formattedAmount }}
    </span>
</template>
