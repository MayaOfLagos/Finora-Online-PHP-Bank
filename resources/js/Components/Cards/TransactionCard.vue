<script setup>
/**
 * TransactionCard Component
 * Displays a single transaction item
 */
import { computed } from 'vue';
import CurrencyDisplay from '@/Components/Common/CurrencyDisplay.vue';
import StatusBadge from '@/Components/Common/StatusBadge.vue';
import { formatRelativeDate, getInitials } from '@/Utils/formatters';

const props = defineProps({
    transaction: {
        type: Object,
        required: true,
        // Expected: { id, type, amount, currency, description, status, created_at, counterparty }
    },
    showStatus: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['click']);

// Determine if transaction is credit or debit
const isCredit = computed(() => {
    const creditTypes = ['credit', 'deposit', 'refund', 'received'];
    return creditTypes.includes(props.transaction.type?.toLowerCase()) || props.transaction.amount > 0;
});

// Get appropriate icon
const transactionIcon = computed(() => {
    const icons = {
        transfer: 'pi pi-arrows-h',
        wire: 'pi pi-globe',
        internal: 'pi pi-users',
        domestic: 'pi pi-building',
        deposit: 'pi pi-download',
        withdrawal: 'pi pi-upload',
        payment: 'pi pi-credit-card',
        fee: 'pi pi-percentage',
        refund: 'pi pi-replay',
        credit: 'pi pi-arrow-down',
        debit: 'pi pi-arrow-up',
    };
    return icons[props.transaction.type?.toLowerCase()] || 'pi pi-circle';
});

// Get icon background color
const iconBgClass = computed(() => {
    if (isCredit.value) return 'bg-green-100 text-green-600';
    return 'bg-red-100 text-red-600';
});

// Format amount with sign
const displayAmount = computed(() => {
    const amount = Math.abs(props.transaction.amount);
    return { amount, isPositive: isCredit.value };
});
</script>

<template>
    <div
        @click="emit('click', transaction)"
        class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600 hover:shadow-sm transition-all cursor-pointer"
    >
        <!-- Icon -->
        <div :class="[iconBgClass, 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0']">
            <i :class="[transactionIcon, 'text-lg']"></i>
        </div>

        <!-- Details -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <p class="font-medium text-gray-900 dark:text-white truncate">
                    {{ transaction.description || transaction.type }}
                </p>
                <StatusBadge
                    v-if="showStatus && transaction.status !== 'completed'"
                    :status="transaction.status"
                    size="small"
                />
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                {{ transaction.counterparty || formatRelativeDate(transaction.created_at) }}
            </p>
        </div>

        <!-- Amount -->
        <div class="text-right flex-shrink-0">
            <CurrencyDisplay
                :amount="displayAmount.amount"
                :currency="transaction.currency || 'USD'"
                :color-code="true"
                :show-sign="true"
                :class="displayAmount.isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
            />
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                {{ formatRelativeDate(transaction.created_at) }}
            </p>
        </div>
    </div>
</template>
