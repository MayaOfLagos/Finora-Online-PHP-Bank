<script setup>
/**
 * EmptyState Component
 * Displays a placeholder when there's no data
 */
import { Link } from '@inertiajs/vue3';

defineProps({
    icon: {
        type: String,
        default: 'pi pi-inbox',
    },
    title: {
        type: String,
        default: 'No data available',
    },
    description: {
        type: String,
        default: '',
    },
    actionLabel: {
        type: String,
        default: '',
    },
    actionHref: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['action']);
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
        <!-- Icon -->
        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
            <i :class="[icon, 'text-2xl text-gray-400 dark:text-gray-500']"></i>
        </div>

        <!-- Title -->
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
            {{ title }}
        </h3>

        <!-- Description -->
        <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-4">
            {{ description }}
        </p>

        <!-- Slot for custom content -->
        <slot />

        <!-- Action Button - Link version -->
        <Link
            v-if="actionLabel && actionHref"
            :href="actionHref"
            class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
        >
            {{ actionLabel }}
        </Link>

        <!-- Action Button - Emit version -->
        <button
            v-else-if="actionLabel"
            @click="emit('action')"
            class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
        >
            {{ actionLabel }}
        </button>
    </div>
</template>
