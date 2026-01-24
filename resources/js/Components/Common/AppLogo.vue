<script setup>
/**
 * AppLogo Component
 * Displays the Finora Bank logo with optional text
 */
defineProps({
    showText: {
        type: Boolean,
        default: true,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    dark: {
        type: Boolean,
        default: false,
    },
});

const sizeClasses = {
    sm: { icon: 'w-6 h-6 text-xs', text: 'text-sm' },
    md: { icon: 'w-8 h-8 text-sm', text: 'text-lg' },
    lg: { icon: 'w-12 h-12 text-lg', text: 'text-2xl' },
};
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Logo Icon -->
        <div
            :class="[
                sizeClasses[size].icon,
                'flex items-center justify-center rounded-xl font-bold',
                dark ? 'bg-white text-indigo-600' : 'bg-indigo-600 text-white'
            ]"
        >
            F
        </div>

        <!-- Logo Text -->
        <span
            v-if="showText"
            :class="[
                sizeClasses[size].text,
                'font-semibold',
                dark ? 'text-white' : 'text-gray-900 dark:text-white'
            ]"
        >
            Finora Bank
        </span>
    </div>
</template>
