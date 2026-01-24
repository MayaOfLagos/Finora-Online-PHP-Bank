<script setup>
/**
 * PendingItemsWidget Component
 * Shows counts of pending actions
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    items: {
        type: Object,
        default: () => ({
            deposits: 0,
            loans: 0,
            tickets: 0,
            cardRequests: 0,
        }),
    },
});

const pendingItems = computed(() => [
    {
        label: 'Pending Deposits',
        count: props.items.deposits,
        icon: 'pi-download',
        color: 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/40',
        href: '/deposits?status=pending',
    },
    {
        label: 'Loan Applications',
        count: props.items.loans,
        icon: 'pi-building',
        color: 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40',
        href: '/loans/applications',
    },
    {
        label: 'Support Tickets',
        count: props.items.tickets,
        icon: 'pi-ticket',
        color: 'text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/40',
        href: '/support',
    },
    {
        label: 'Card Requests',
        count: props.items.cardRequests,
        icon: 'pi-credit-card',
        color: 'text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/40',
        href: '/cards/requests',
    },
]);

const totalPending = computed(() =>
    Object.values(props.items).reduce((sum, val) => sum + val, 0)
);
</script>

<template>
    <div class="pending-items-widget">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Actions</h4>
            <span
                v-if="totalPending > 0"
                class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-semibold rounded-full"
            >
                {{ totalPending }}
            </span>
        </div>

        <!-- Items List -->
        <div class="space-y-3">
            <Link
                v-for="item in pendingItems"
                :key="item.label"
                :href="item.href"
                :class="[
                    'flex items-center justify-between p-3 rounded-xl transition-all',
                    item.count > 0 ? 'bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700' : 'opacity-50'
                ]"
            >
                <div class="flex items-center gap-3">
                    <div :class="['w-9 h-9 rounded-lg flex items-center justify-center', item.color]">
                        <i :class="['pi', item.icon, 'text-sm']"></i>
                    </div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                </div>
                <span
                    :class="[
                        'text-sm font-semibold',
                        item.count > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'
                    ]"
                >
                    {{ item.count }}
                </span>
            </Link>
        </div>

        <!-- All Clear Message -->
        <div v-if="totalPending === 0" class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl text-center">
            <i class="pi pi-check-circle text-2xl text-green-500 dark:text-green-400 mb-2"></i>
            <p class="text-sm text-green-700 dark:text-green-400">All caught up! No pending items.</p>
        </div>
    </div>
</template>
