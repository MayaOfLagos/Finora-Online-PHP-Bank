<script setup>
/**
 * AccountCard Component
 * Individual account card with quick actions
 */
import { computed } from 'vue';
import Button from 'primevue/button';
import Badge from 'primevue/badge';
import Menu from 'primevue/menu';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    account: {
        type: Object,
        required: true
    },
    showActions: {
        type: Boolean,
        default: true
    },
    compact: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits([
    'view-details',
    'transfer',
    'freeze',
    'unfreeze',
    'set-primary',
    'download-statement'
]);

const { formatCurrency } = useCurrency();

// Account type icon mapping
const accountTypeIcons = {
    savings: 'pi-dollar',
    checking: 'pi-wallet',
    business: 'pi-building',
    fixed_deposit: 'pi-lock'
};

// Status badge severity mapping
const statusSeverity = {
    active: 'success',
    frozen: 'info',
    pending: 'warning',
    inactive: 'danger'
};

// Masked account number
const maskedAccountNumber = computed(() => {
    const number = props.account.account_number;
    if (number.length <= 4) return number;
    return '••••' + number.slice(-4);
});

// Account type icon
const accountIcon = computed(() => {
    const type = props.account.account_type?.slug || 'savings';
    return accountTypeIcons[type] || 'pi-wallet';
});

// Is primary account
const isPrimary = computed(() => props.account.is_primary);

// Is frozen
const isFrozen = computed(() => props.account.status === 'frozen');

// Quick actions menu
const quickActionsMenu = computed(() => {
    const items = [
        {
            label: 'View Details',
            icon: 'pi pi-eye',
            command: () => emit('view-details', props.account)
        },
        {
            label: 'Transfer Money',
            icon: 'pi pi-send',
            command: () => emit('transfer', props.account)
        },
        {
            label: 'Download Statement',
            icon: 'pi pi-download',
            command: () => emit('download-statement', props.account)
        }
    ];

    // Add freeze/unfreeze
    if (isFrozen.value) {
        items.push({
            label: 'Unfreeze Account',
            icon: 'pi pi-lock-open',
            command: () => emit('unfreeze', props.account)
        });
    } else {
        items.push({
            label: 'Freeze Account',
            icon: 'pi pi-lock',
            command: () => emit('freeze', props.account)
        });
    }

    // Add set primary if not already
    if (!isPrimary.value) {
        items.push({
            label: 'Set as Primary',
            icon: 'pi pi-star',
            command: () => emit('set-primary', props.account)
        });
    }

    return items;
});
</script>

<template>
    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm hover:shadow-lg">
        <!-- Card Header -->
        <div class="p-4 pb-0">
            <div class="flex items-start justify-between">
                <!-- Account Type Icon -->
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600">
                    <i :class="['pi', accountIcon, 'text-white text-xl']"></i>
                </div>

                <!-- Status Badge -->
                <Badge
                    :value="account.status"
                    :severity="statusSeverity[account.status] || 'secondary'"
                    class="uppercase"
                />
            </div>
        </div>

        <!-- Card Content -->
        <div class="p-4 space-y-3">
            <!-- Account Type & Name -->
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ account.account_type?.name || 'Account' }}
                </p>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ account.account_name || maskedAccountNumber }}
                </h3>
            </div>

            <!-- Account Number -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Account Number
                </span>
                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                    {{ maskedAccountNumber }}
                </span>
            </div>

            <!-- Balance -->
            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">
                    Available Balance
                </p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ formatCurrency(account.balance, account.currency) }}
                </p>
            </div>

            <!-- Primary Badge -->
            <div v-if="isPrimary" class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                <i class="pi pi-star-fill text-sm"></i>
                <span class="text-xs font-medium">Primary Account</span>
            </div>
        </div>

        <!-- Card Footer -->
        <div v-if="showActions" class="flex gap-2 p-4 pt-0">
            <!-- View Details -->
            <Button
                label="View Details"
                icon="pi pi-eye"
                outlined
                size="small"
                class="flex-1"
                @click="emit('view-details', account)"
            />

            <!-- Transfer -->
            <Button
                label="Transfer"
                icon="pi pi-send"
                size="small"
                class="flex-1"
                :disabled="isFrozen"
                @click="emit('transfer', account)"
            />

            <!-- More Actions Menu -->
            <Button
                icon="pi pi-ellipsis-v"
                outlined
                size="small"
                @click="(event) => $refs.actionsMenu.toggle(event)"
            />
            <Menu
                ref="actionsMenu"
                :model="quickActionsMenu"
                popup
            />
        </div>
    </div>
</template>
