<script setup>
/**
 * Transaction History Page
 * Display user's transaction history with filters
 */
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    transactions: {
        type: Object,
        default: () => ({ data: [], meta: {} })
    },
    accounts: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const toast = useToast();
const { formatCurrency } = useCurrency();
const page = usePage();
const userCurrency = computed(() => page.props.auth?.currency || 'USD');

// Filter state
const search = ref(props.filters.search || '');
const selectedAccount = ref(props.filters.account || null);
const selectedType = ref(props.filters.type || null);
const selectedStatus = ref(props.filters.status || null);
const dateFrom = ref(props.filters.date_from ? new Date(props.filters.date_from) : null);
const dateTo = ref(props.filters.date_to ? new Date(props.filters.date_to) : null);

// Options
const accountOptions = computed(() => [
    { label: 'All Accounts', value: null },
    ...props.accounts.map(acc => ({
        label: `${acc.account_name || 'Account'} (****${acc.account_number.slice(-4)})`,
        value: acc.uuid
    }))
]);

const typeOptions = [
    { label: 'All Types', value: null },
    { label: 'Credit', value: 'credit' },
    { label: 'Debit', value: 'debit' },
];

const statusOptions = [
    { label: 'All Status', value: null },
    { label: 'Completed', value: 'completed' },
    { label: 'Pending', value: 'pending' },
    { label: 'Failed', value: 'failed' },
    { label: 'Reversed', value: 'reversed' },
];

// Transaction type icons
const typeIcons = {
    internal_transfer: 'pi-arrows-h',
    wire_transfer: 'pi-globe',
    domestic_transfer: 'pi-building',
    deposit: 'pi-download',
    withdrawal: 'pi-upload',
    fee: 'pi-percentage',
    interest: 'pi-chart-line',
    refund: 'pi-replay',
};

// Get icon for transaction type
const getTypeIcon = (type) => {
    return typeIcons[type] || 'pi-credit-card';
};

// Status severity mapping
const getStatusSeverity = (status) => {
    const map = {
        completed: 'success',
        pending: 'warning',
        failed: 'danger',
        reversed: 'info',
    };
    return map[status] || 'secondary';
};

// Apply filters
const applyFilters = () => {
    router.get(route('transactions.index'), {
        search: search.value || undefined,
        account: selectedAccount.value || undefined,
        type: selectedType.value || undefined,
        status: selectedStatus.value || undefined,
        date_from: dateFrom.value ? dateFrom.value.toISOString().split('T')[0] : undefined,
        date_to: dateTo.value ? dateTo.value.toISOString().split('T')[0] : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    selectedAccount.value = null;
    selectedType.value = null;
    selectedStatus.value = null;
    dateFrom.value = null;
    dateTo.value = null;
    applyFilters();
};

// Pagination
const onPageChange = (event) => {
    router.get(route('transactions.index'), {
        ...props.filters,
        page: event.page + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// View transaction details
const viewTransaction = (transaction) => {
    router.visit(route('transactions.show', { transaction: transaction.uuid }));
};

// Debounced search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
});
</script>

<template>
    <Head title="Transaction History" />

    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Transaction History
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        View and track all your transactions
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm border-l-4 border-l-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transactions</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ stats.total_transactions || 0 }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30">
                            <i class="pi pi-list text-xl text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                    </div>
                </div>

                <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm border-l-4 border-l-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Credits</p>
                            <p class="mt-1 text-3xl font-bold text-green-600 dark:text-green-400">
                                {{ formatCurrency((stats.total_credits || 0) * 100, userCurrency) }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30">
                            <i class="pi pi-arrow-down text-xl text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                </div>

                <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm border-l-4 border-l-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Debits</p>
                            <p class="mt-1 text-3xl font-bold text-red-600 dark:text-red-400">
                                {{ formatCurrency((stats.total_debits || 0) * 100, userCurrency) }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30">
                            <i class="pi pi-arrow-up text-xl text-red-600 dark:text-red-400"></i>
                        </div>
                    </div>
                </div>

                <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm border-l-4 border-l-amber-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
                            <p class="mt-1 text-3xl font-bold text-amber-600 dark:text-amber-400">
                                {{ stats.pending_transactions || 0 }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30">
                            <i class="pi pi-clock text-xl text-amber-600 dark:text-amber-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <!-- Search -->
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="search"
                                placeholder="Search by reference..."
                                class="w-full"
                            />
                        </IconField>

                        <!-- Account Filter -->
                        <Dropdown
                            v-model="selectedAccount"
                            :options="accountOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select Account"
                            class="w-full"
                            @change="applyFilters"
                        />

                        <!-- Type Filter -->
                        <Dropdown
                            v-model="selectedType"
                            :options="typeOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Transaction Type"
                            class="w-full"
                            @change="applyFilters"
                        />

                        <!-- Status Filter -->
                        <Dropdown
                            v-model="selectedStatus"
                            :options="statusOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Status"
                            class="w-full"
                            @change="applyFilters"
                        />
                    </div>

                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                        <!-- Date Range -->
                        <div class="flex flex-col gap-2 md:flex-row md:items-center">
                            <Calendar
                                v-model="dateFrom"
                                placeholder="From Date"
                                dateFormat="yy-mm-dd"
                                showIcon
                                class="w-full md:w-40"
                                @date-select="applyFilters"
                            />
                            <span class="text-gray-500 dark:text-gray-400">to</span>
                            <Calendar
                                v-model="dateTo"
                                placeholder="To Date"
                                dateFormat="yy-mm-dd"
                                showIcon
                                class="w-full md:w-40"
                                @date-select="applyFilters"
                            />
                        </div>

                        <!-- Clear Filters -->
                        <Button
                            label="Clear Filters"
                            icon="pi pi-times"
                            outlined
                            size="small"
                            @click="clearFilters"
                        />
                    </div>
                </div>
            </div>

            <!-- Transactions Table (Desktop) -->
            <div class="hidden lg:block">
                <div class="overflow-hidden bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                    <DataTable
                        :value="transactions.data"
                        :rows="15"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        :rowHover="true"
                        @row-click="(e) => viewTransaction(e.data)"
                    >
                        <Column header="Date" style="width: 140px">
                            <template #body="{ data }">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ new Date(data.created_at).toLocaleDateString() }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ new Date(data.created_at).toLocaleTimeString() }}
                                    </p>
                                </div>
                            </template>
                        </Column>

                        <Column header="Reference" style="width: 160px">
                            <template #body="{ data }">
                                <span class="font-mono text-sm text-gray-900 dark:text-white">
                                    {{ data.reference_number }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Description">
                            <template #body="{ data }">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 rounded-lg"
                                        :class="data.type === 'credit' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'"
                                    >
                                        <i
                                            :class="[
                                                'pi',
                                                getTypeIcon(data.transaction_type),
                                                'text-lg',
                                                data.type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                                            ]"
                                        ></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ data.description || data.transaction_type }}
                                        </p>
                                        <p v-if="data.bank_account" class="text-xs text-gray-500 dark:text-gray-400">
                                            ****{{ data.bank_account.account_number.slice(-4) }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column header="Amount" style="width: 160px">
                            <template #body="{ data }">
                                <span
                                    class="text-lg font-bold"
                                    :class="data.type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                >
                                    {{ data.type === 'credit' ? '+' : '-' }}{{ formatCurrency(data.amount, data.currency || 'USD') }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Status" style="width: 120px">
                            <template #body="{ data }">
                                <Tag
                                    :value="data.status"
                                    :severity="getStatusSeverity(data.status)"
                                    class="uppercase"
                                />
                            </template>
                        </Column>

                        <template #empty>
                            <div class="flex flex-col items-center justify-center py-12">
                                <i class="pi pi-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                <p class="text-gray-500 dark:text-gray-400">No transactions found</p>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>

            <!-- Transactions List (Mobile) -->
            <div class="lg:hidden space-y-3">
                <div
                    v-for="transaction in transactions.data"
                    :key="transaction.uuid"
                    class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
                    @click="viewTransaction(transaction)"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-lg"
                                :class="transaction.type === 'credit' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'"
                            >
                                <i
                                    :class="[
                                        'pi',
                                        getTypeIcon(transaction.transaction_type),
                                        'text-lg',
                                        transaction.type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                                    ]"
                                ></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ transaction.description || transaction.transaction_type }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ transaction.created_at_human }}
                                </p>
                            </div>
                        </div>
                        <Tag
                            :value="transaction.status"
                            :severity="getStatusSeverity(transaction.status)"
                            class="uppercase text-xs"
                        />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                            {{ transaction.reference_number }}
                        </span>
                        <span
                            class="text-lg font-bold"
                            :class="transaction.type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                        >
                            {{ transaction.type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount, transaction.currency || 'USD') }}
                        </span>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="transactions.data.length === 0"
                    class="flex flex-col items-center justify-center py-16 text-center"
                >
                    <div class="flex items-center justify-center w-20 h-20 mb-4 rounded-full bg-gray-100 dark:bg-gray-800">
                        <i class="pi pi-inbox text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        No transactions found
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Your transaction history will appear here
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="transactions.data.length > 0" class="flex justify-center">
                <Paginator
                    :rows="transactions.per_page"
                    :totalRecords="transactions.total"
                    :first="(transactions.current_page - 1) * transactions.per_page"
                    @page="onPageChange"
                />
            </div>
        </div>
    </DashboardLayout>
</template>
