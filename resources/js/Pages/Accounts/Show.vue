<script setup>
/**
 * Account Details Page
 * Display detailed account information with transaction history
 */
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { useConfirm } from 'primevue/useconfirm';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Badge from 'primevue/badge';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Menu from 'primevue/menu';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PinInput from '@/Components/Forms/PinInput.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { useDate } from '@/Composables/useDate';

const props = defineProps({
    account: {
        type: Object,
        required: true
    },
    transactions: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            totalIn: 0,
            totalOut: 0,
            transactionCount: 0
        })
    }
});

const toast = useToast();
const confirm = useConfirm();
const { formatCurrency } = useCurrency();
const { formatDate, formatDateTime, timeAgo } = useDate();

// State
const showPinModal = ref(false);
const pinAction = ref(null);
const pin = ref('');
const isProcessing = ref(false);
const selectedTransaction = ref(null);
const showTransactionDetails = ref(false);

// Filters
const dateRange = ref(null);
const transactionType = ref(null);

const transactionTypeOptions = [
    { label: 'All Types', value: null },
    { label: 'Credits', value: 'credit' },
    { label: 'Debits', value: 'debit' }
];

// Account type icon mapping
const accountTypeIcons = {
    savings: 'pi-dollar',
    checking: 'pi-wallet',
    business: 'pi-building',
    fixed_deposit: 'pi-lock'
};

// Status badge severity
const statusSeverity = {
    active: 'success',
    frozen: 'info',
    pending: 'warning',
    inactive: 'danger'
};

// Account icon
const accountIcon = computed(() => {
    const type = props.account.account_type?.slug || 'savings';
    return accountTypeIcons[type] || 'pi-wallet';
});

// Is account frozen
const isFrozen = computed(() => props.account.status === 'frozen');

// Filtered transactions
const filteredTransactions = computed(() => {
    let filtered = props.transactions;

    if (transactionType.value) {
        filtered = filtered.filter(t => t.type === transactionType.value);
    }

    if (dateRange.value && dateRange.value[0] && dateRange.value[1]) {
        const startDate = new Date(dateRange.value[0]);
        const endDate = new Date(dateRange.value[1]);
        endDate.setHours(23, 59, 59, 999);

        filtered = filtered.filter(t => {
            const txDate = new Date(t.created_at);
            return txDate >= startDate && txDate <= endDate;
        });
    }

    return filtered;
});

// Quick action menu
const actionMenuRef = ref();
const actionMenuItems = computed(() => {
    const items = [
        {
            label: 'Transfer Money',
            icon: 'pi pi-send',
            command: () => handleTransfer()
        },
        {
            label: 'Download Statement',
            icon: 'pi pi-download',
            command: () => handleDownloadStatement()
        },
        { separator: true }
    ];

    if (isFrozen.value) {
        items.push({
            label: 'Unfreeze Account',
            icon: 'pi pi-lock-open',
            command: () => openPinModal('unfreeze')
        });
    } else {
        items.push({
            label: 'Freeze Account',
            icon: 'pi pi-lock',
            class: 'text-orange-600',
            command: () => openPinModal('freeze')
        });
    }

    if (!props.account.is_primary) {
        items.push({
            label: 'Set as Primary',
            icon: 'pi pi-star',
            command: () => handleSetPrimary()
        });
    }

    return items;
});

// Actions
const handleTransfer = () => {
    router.visit(route('transfers.internal'), {
        data: { from_account_id: props.account.uuid }
    });
};

const handleDownloadStatement = () => {
    router.get(route('accounts.statement', { account: props.account.uuid }), {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.success) {
                toast.success(page.props.flash.success, 'Statement Sent');
            }
        },
        onError: (errors) => {
            toast.error(
                errors.statement || 'Unable to generate statement. Please try again.',
                'Statement Error'
            );
        }
    });
};

const handleSetPrimary = () => {
    router.patch(route('accounts.set-primary', { account: props.account.uuid }), {}, {
        onSuccess: () => {
            toast.success('This account is now your primary account', 'Primary Account Set');
        }
    });
};

const openPinModal = (action) => {
    pinAction.value = action;
    pin.value = '';
    showPinModal.value = true;
};

const handlePinSubmit = () => {
    if (pin.value.length !== 6) {
        toast.error('Please enter your 6-digit transaction PIN', 'Invalid PIN');
        return;
    }

    isProcessing.value = true;

    const endpoint = pinAction.value === 'freeze'
        ? route('accounts.freeze', { account: props.account.uuid })
        : route('accounts.unfreeze', { account: props.account.uuid });

    router.patch(endpoint, { pin: pin.value }, {
        onSuccess: () => {
            showPinModal.value = false;
            toast.success(
                `Account has been ${pinAction.value === 'freeze' ? 'frozen' : 'unfrozen'} successfully`,
                pinAction.value === 'freeze' ? 'Account Frozen' : 'Account Unfrozen'
            );
        },
        onError: () => {
            toast.error(
                'Invalid PIN or action could not be completed',
                'Action Failed'
            );
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const viewTransactionDetails = (transaction) => {
    selectedTransaction.value = transaction;
    showTransactionDetails.value = true;
};

// Format transaction amount with color
const getTransactionAmountClass = (type) => {
    return type === 'credit'
        ? 'text-green-600 dark:text-green-400'
        : 'text-red-600 dark:text-red-400';
};

const getTransactionPrefix = (type) => {
    return type === 'credit' ? '+' : '-';
};

// Copy to clipboard
const copyToClipboard = (text, label) => {
    navigator.clipboard.writeText(text);
    toast.success(`${label} copied to clipboard`, 'Copied');
};
</script>

<template>
    <Head :title="`Account - ${account.account_number}`" />

    <DashboardLayout title="Account Details">
        <!-- Back Button & Header -->
        <div class="mb-6">
            <Link
                href="/accounts"
                class="inline-flex items-center gap-2 mb-4 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <i class="pi pi-arrow-left"></i>
                Back to Accounts
            </Link>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600">
                        <i :class="['pi', accountIcon, 'text-white text-2xl']"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ account.account_type?.name || 'Account' }}
                            </h1>
                            <Badge
                                :value="account.status"
                                :severity="statusSeverity[account.status] || 'secondary'"
                                class="uppercase"
                            />
                            <Badge
                                v-if="account.is_primary"
                                value="Primary"
                                severity="warning"
                            />
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ account.account_name || account.account_number }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Button
                        label="Transfer"
                        icon="pi pi-send"
                        :disabled="isFrozen"
                        @click="handleTransfer"
                    />
                    <Button
                        icon="pi pi-ellipsis-v"
                        outlined
                        @click="(event) => actionMenuRef.toggle(event)"
                    />
                    <Menu
                        ref="actionMenuRef"
                        :model="actionMenuItems"
                        popup
                    />
                </div>
            </div>
        </div>

        <!-- Account Overview Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Balance Card -->
            <div class="p-5 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg">
                <div class="text-white">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="pi pi-wallet text-lg opacity-80"></i>
                        <p class="text-sm font-medium opacity-90">Available Balance</p>
                    </div>
                    <p class="text-3xl font-bold">
                        {{ formatCurrency(account.balance, account.currency) }}
                    </p>
                    <p class="mt-2 text-xs opacity-70">
                        Last updated {{ timeAgo(account.updated_at) }}
                    </p>
                </div>
            </div>

            <!-- Total Credits -->
            <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30">
                        <i class="pi pi-arrow-down-left text-xl text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Credits</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">
                            +{{ formatCurrency(stats.totalIn, account.currency) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Debits -->
            <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30">
                        <i class="pi pi-arrow-up-right text-xl text-red-600 dark:text-red-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Debits</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">
                            -{{ formatCurrency(stats.totalOut, account.currency) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Transaction Count -->
            <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30">
                        <i class="pi pi-list text-xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Transactions</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ stats.transactionCount }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Content -->
        <div class="bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm overflow-hidden">
            <TabView class="account-tabs">
                    <!-- Transactions Tab -->
                    <TabPanel header="Transactions">
                        <!-- Filters -->
                        <div class="flex flex-col gap-4 mb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-wrap gap-2">
                                <Dropdown
                                    v-model="transactionType"
                                    :options="transactionTypeOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="All Types"
                                    class="w-36"
                                />
                                <Calendar
                                    v-model="dateRange"
                                    selectionMode="range"
                                    placeholder="Date Range"
                                    dateFormat="dd/mm/yy"
                                    showIcon
                                    :showButtonBar="true"
                                />
                            </div>
                            <Button
                                v-if="dateRange || transactionType"
                                label="Clear Filters"
                                icon="pi pi-times"
                                text
                                size="small"
                                @click="dateRange = null; transactionType = null"
                            />
                        </div>

                        <!-- Transactions Table -->
                        <DataTable
                            :value="filteredTransactions"
                            :paginator="true"
                            :rows="10"
                            :rowsPerPageOptions="[10, 25, 50]"
                            stripedRows
                            responsiveLayout="scroll"
                            class="p-datatable-sm"
                            emptyMessage="No transactions found"
                        >
                            <Column field="created_at" header="Date" sortable style="width: 150px">
                                <template #body="{ data }">
                                    <div>
                                        <p class="font-medium">{{ formatDate(data.created_at) }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDateTime(data.created_at).split(' ').slice(-2).join(' ') }}</p>
                                    </div>
                                </template>
                            </Column>

                            <Column field="description" header="Description">
                                <template #body="{ data }">
                                    <div class="flex items-center gap-3">
                                        <div :class="[
                                            'w-10 h-10 rounded-full flex items-center justify-center',
                                            data.type === 'credit' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'
                                        ]">
                                            <i :class="[
                                                'pi text-sm',
                                                data.type === 'credit' ? 'pi-arrow-down-left text-green-600' : 'pi-arrow-up-right text-red-600'
                                            ]"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ data.description }}</p>
                                            <p class="text-xs text-gray-500">{{ data.reference }}</p>
                                        </div>
                                    </div>
                                </template>
                            </Column>

                            <Column field="amount" header="Amount" sortable style="width: 150px">
                                <template #body="{ data }">
                                    <span :class="['font-semibold', getTransactionAmountClass(data.type)]">
                                        {{ getTransactionPrefix(data.type) }}{{ formatCurrency(data.amount, account.currency) }}
                                    </span>
                                </template>
                            </Column>

                            <Column field="balance_after" header="Balance" style="width: 150px">
                                <template #body="{ data }">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ formatCurrency(data.balance_after, account.currency) }}
                                    </span>
                                </template>
                            </Column>

                            <Column header="Actions" style="width: 80px">
                                <template #body="{ data }">
                                    <Button
                                        icon="pi pi-eye"
                                        text
                                        rounded
                                        size="small"
                                        @click="viewTransactionDetails(data)"
                                        v-tooltip.top="'View Details'"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>

                    <!-- Account Details Tab -->
                    <TabPanel header="Account Details">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Account Information</h3>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Account Number</p>
                                            <p class="font-mono font-medium text-gray-900 dark:text-white">
                                                {{ account.account_number }}
                                            </p>
                                        </div>
                                        <Button
                                            icon="pi pi-copy"
                                            text
                                            rounded
                                            size="small"
                                            @click="copyToClipboard(account.account_number, 'Account number')"
                                        />
                                    </div>

                                    <div v-if="account.routing_number" class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Routing Number</p>
                                            <p class="font-mono font-medium text-gray-900 dark:text-white">
                                                {{ account.routing_number }}
                                            </p>
                                        </div>
                                        <Button
                                            icon="pi pi-copy"
                                            text
                                            rounded
                                            size="small"
                                            @click="copyToClipboard(account.routing_number, 'Routing number')"
                                        />
                                    </div>

                                    <div v-if="account.swift_code" class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">SWIFT/BIC Code</p>
                                            <p class="font-mono font-medium text-gray-900 dark:text-white">
                                                {{ account.swift_code }}
                                            </p>
                                        </div>
                                        <Button
                                            icon="pi pi-copy"
                                            text
                                            rounded
                                            size="small"
                                            @click="copyToClipboard(account.swift_code, 'SWIFT code')"
                                        />
                                    </div>

                                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Account Type</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ account.account_type?.name }}
                                        </p>
                                    </div>

                                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Currency</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ account.currency }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Account Status</h3>

                                <div class="space-y-3">
                                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                                        <Badge
                                            :value="account.status"
                                            :severity="statusSeverity[account.status] || 'secondary'"
                                            class="mt-1 uppercase"
                                        />
                                    </div>

                                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Primary Account</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ account.is_primary ? 'Yes' : 'No' }}
                                        </p>
                                    </div>

                                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Opened On</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ formatDate(account.created_at) }}
                                        </p>
                                    </div>

                                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Last Activity</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ timeAgo(account.updated_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TabPanel>
                </TabView>
            </div>

        <!-- PIN Verification Modal -->
        <Dialog
            v-model:visible="showPinModal"
            modal
            :header="pinAction === 'freeze' ? 'Freeze Account' : 'Unfreeze Account'"
            :style="{ width: '400px' }"
        >
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ pinAction === 'freeze'
                        ? 'Enter your transaction PIN to freeze this account. You will not be able to make any transactions until unfrozen.'
                        : 'Enter your transaction PIN to unfreeze this account and resume normal operations.'
                    }}
                </p>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Transaction PIN
                    </label>
                    <PinInput
                        v-model="pin"
                        :length="6"
                        masked
                        @complete="handlePinSubmit"
                    />
                </div>
            </div>

            <template #footer>
                <Button
                    label="Cancel"
                    text
                    @click="showPinModal = false"
                    :disabled="isProcessing"
                />
                <Button
                    :label="pinAction === 'freeze' ? 'Freeze Account' : 'Unfreeze Account'"
                    :icon="pinAction === 'freeze' ? 'pi pi-lock' : 'pi pi-lock-open'"
                    :severity="pinAction === 'freeze' ? 'warning' : 'success'"
                    :loading="isProcessing"
                    @click="handlePinSubmit"
                />
            </template>
        </Dialog>

        <!-- Transaction Details Modal -->
        <Dialog
            v-model:visible="showTransactionDetails"
            modal
            header="Transaction Details"
            :style="{ width: '500px' }"
        >
            <div v-if="selectedTransaction" class="space-y-4">
                <div class="flex items-center justify-center">
                    <div :class="[
                        'w-16 h-16 rounded-full flex items-center justify-center',
                        selectedTransaction.type === 'credit' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'
                    ]">
                        <i :class="[
                            'pi text-2xl',
                            selectedTransaction.type === 'credit' ? 'pi-arrow-down-left text-green-600' : 'pi-arrow-up-right text-red-600'
                        ]"></i>
                    </div>
                </div>

                <div class="text-center">
                    <p :class="['text-3xl font-bold', getTransactionAmountClass(selectedTransaction.type)]">
                        {{ getTransactionPrefix(selectedTransaction.type) }}{{ formatCurrency(selectedTransaction.amount, account.currency) }}
                    </p>
                    <p class="mt-1 text-gray-500">{{ selectedTransaction.description }}</p>
                </div>

                <div class="pt-4 space-y-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Reference</span>
                        <span class="font-mono text-gray-900 dark:text-white">{{ selectedTransaction.reference }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date</span>
                        <span class="text-gray-900 dark:text-white">{{ formatDateTime(selectedTransaction.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Type</span>
                        <Badge
                            :value="selectedTransaction.type"
                            :severity="selectedTransaction.type === 'credit' ? 'success' : 'danger'"
                            class="uppercase"
                        />
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Balance After</span>
                        <span class="text-gray-900 dark:text-white">{{ formatCurrency(selectedTransaction.balance_after, account.currency) }}</span>
                    </div>
                </div>
            </div>
        </Dialog>
    </DashboardLayout>
</template>

<style scoped>
/* TabView styling */
:deep(.account-tabs .p-tabview-panels) {
    padding: 1.25rem;
}

:deep(.account-tabs .p-tabview-nav) {
    border-bottom: 1px solid rgb(229 231 235);
    padding: 0 1.25rem;
}

:deep(.dark .account-tabs .p-tabview-nav) {
    border-bottom-color: rgb(55 65 81);
}

:deep(.account-tabs .p-tabview-nav-link) {
    padding: 1rem 1.25rem;
}
</style>
