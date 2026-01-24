<script setup>
/**
 * LoadingSpinner Component
 * Displays a loading indicator
 */
defineProps({
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl
        validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v),
    },
    color: {
        type: String,
        default: 'primary', // primary, white, gray
    },
    label: {
        type: String,
        default: '',
    },
});

const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
};

const colorClasses = {
    primary: 'text-indigo-600',
    white: 'text-white',
    gray: 'text-gray-400',
};
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-2">
        <svg
            :class="[sizeClasses[size], colorClasses[color], 'animate-spin']"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <span v-if="label" :class="[colorClasses[color], 'text-sm font-medium']">
            {{ label }}
        </span>
    </div>
</template>
