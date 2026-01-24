<script setup>
/**
 * StatusBadge Component
 * Displays a status badge with appropriate colors
 */
import { computed } from 'vue';
import Tag from 'primevue/tag';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'normal', // small, normal, large
    },
});

// Map status to PrimeVue severity
const severity = computed(() => {
    const statusMap = {
        // Success states
        completed: 'success',
        approved: 'success',
        active: 'success',
        verified: 'success',
        paid: 'success',
        resolved: 'success',
        success: 'success',

        // Warning states
        pending: 'warn',
        processing: 'warn',
        awaiting_reply: 'warn',
        in_progress: 'warn',

        // Danger states
        failed: 'danger',
        rejected: 'danger',
        blocked: 'danger',
        cancelled: 'danger',
        defaulted: 'danger',
        expired: 'danger',

        // Info states
        frozen: 'info',
        open: 'info',

        // Secondary states
        closed: 'secondary',
        inactive: 'secondary',
        not_started: 'secondary',
    };

    return statusMap[props.status.toLowerCase()] || 'secondary';
});

// Format status for display
const displayStatus = computed(() => {
    return props.status
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
});
</script>

<template>
    <Tag
        :value="displayStatus"
        :severity="severity"
        :class="{ 'text-xs': size === 'small', 'text-sm': size === 'normal', 'text-base': size === 'large' }"
    />
</template>
