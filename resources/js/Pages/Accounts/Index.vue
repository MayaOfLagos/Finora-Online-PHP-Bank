<script setup>
/**
 * Accounts Page
 * Display all user bank accounts with management features
 */
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import AccountCard from '@/Components/Accounts/AccountCard.vue';
import AccountStats from '@/Components/Accounts/AccountStats.vue';
import CreateAccountModal from '@/Components/Accounts/CreateAccountModal.vue';
import PinInput from '@/Components/Forms/PinInput.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    accountTypes: {
        type: Array,
        default: () => []
    },
    currencies: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            totalBalance: 0,
            activeAccounts: 0,
            primaryAccount: null
        })
    },
    maxAccountsPerUser: {
        type: Number,
        default: 2
    }
});

const toast = useToast();
const confirm = useConfirm();
const { formatCurrency } = useCurrency();

// State
const showCreateModal = ref(false);
const searchQuery = ref('');
const selectedAccountType = ref(null);
const selectedStatus = ref(null);
const selectedCurrency = ref('USD');
const sortBy = ref('balance');

// PIN Modal state
const showPinModal = ref(false);
const pinAction = ref(null); // 'freeze' or 'unfreeze'
const selectedAccountForAction = ref(null);
const pin = ref('');
const isProcessing = ref(false);

// Account type options
const accountTypeOptions = [
    { label: 'All Types', value: null },
    ...props.accountTypes.map(type => ({ label: type.name, value: type.id }))
];

// Status options
const statusOptions = [
    { label: 'All Status', value: null },
    { label: 'Active', value: 'active' },
    { label: 'Frozen', value: 'frozen' },
    { label: 'Pending', value: 'pending' }
];

// Sort options
const sortOptions = [
    { label: 'Balance (High to Low)', value: 'balance' },
    { label: 'Balance (Low to High)', value: 'balance_asc' },
    { label: 'Recently Created', value: 'created' },
    { label: 'Account Name', value: 'name' }
];

// Filtered and sorted accounts
const filteredAccounts = computed(() => {
    let filtered = props.accounts;

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(account =>
            account.account_number.toLowerCase().includes(query) ||
            account.account_name?.toLowerCase().includes(query) ||
            account.account_type?.name.toLowerCase().includes(query)
        );
    }

    // Account type filter
    if (selectedAccountType.value) {
        filtered = filtered.filter(account =>
            account.account_type_id === selectedAccountType.value
        );
    }

    // Status filter
    if (selectedStatus.value) {
        filtered = filtered.filter(account =>
            account.status === selectedStatus.value
        );
    }

    // Sort
    const sorted = [...filtered].sort((a, b) => {
        switch (sortBy.value) {
            case 'balance':
                return b.balance - a.balance;
            case 'balance_asc':
                return a.balance - b.balance;
            case 'created':
                return new Date(b.created_at) - new Date(a.created_at);
            case 'name':
                return (a.account_name || '').localeCompare(b.account_name || '');
            default:
                return 0;
        }
    });

    return sorted;
});

// Check if there are active filters
const hasActiveFilters = computed(() => {
    return searchQuery.value || selectedAccountType.value || selectedStatus.value;
});

// Check if user can create more accounts
const canCreateAccount = computed(() => {
    return props.accounts.length < props.maxAccountsPerUser;
});

// Account limit message
const accountLimitMessage = computed(() => {
    if (canCreateAccount.value) return '';
    return `You have reached the maximum limit of ${props.maxAccountsPerUser} account(s). Contact support to request more.`;
});

// Account actions
const handleViewDetails = (account) => {
    router.visit(route('accounts.show', { account: account.uuid }));
};

const handleTransfer = (account) => {
    router.visit(route('transfers.internal'), {
        data: { from_account_id: account.uuid }
    });
};

const handleFreeze = (account) => {
    confirm.require({
        message: `Are you sure you want to freeze account ${account.account_number}?`,
        header: 'Freeze Account',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-warning',
        accept: () => {
            selectedAccountForAction.value = account;
            pinAction.value = 'freeze';
            pin.value = '';
            showPinModal.value = true;
        }
    });
};

const handleUnfreeze = (account) => {
    selectedAccountForAction.value = account;
    pinAction.value = 'unfreeze';
    pin.value = '';
    showPinModal.value = true;
};

const handlePinSubmit = () => {
    if (pin.value.length !== 6) {
        toast.add({
            severity: 'error',
            summary: 'Invalid PIN',
            detail: 'Please enter your 6-digit transaction PIN',
            life: 3000
        });
        return;
    }

    isProcessing.value = true;
    const endpoint = pinAction.value === 'freeze'
        ? route('accounts.freeze', { account: selectedAccountForAction.value.uuid })
        : route('accounts.unfreeze', { account: selectedAccountForAction.value.uuid });

    router.patch(endpoint, { pin: pin.value }, {
        onSuccess: () => {
            showPinModal.value = false;
            toast.add({
                severity: 'success',
                summary: pinAction.value === 'freeze' ? 'Account Frozen' : 'Account Unfrozen',
                detail: `Account has been ${pinAction.value === 'freeze' ? 'frozen' : 'unfrozen'} successfully`,
                life: 3000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Action Failed',
                detail: errors.pin || 'Invalid PIN or action could not be completed',
                life: 3000
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const handleSetPrimary = (account) => {
    router.patch(route('accounts.set-primary', { account: account.uuid }), {}, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Primary Account Set',
                detail: 'This account is now your primary account',
                life: 3000
            });
        }
    });
};

const handleDownloadStatement = (account) => {
    router.get(route('accounts.statement', { account: account.uuid }), {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.success) {
                toast.add({
                    severity: 'success',
                    summary: 'Statement Sent',
                    detail: page.props.flash.success,
                    life: 5000
                });
            }
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Statement Error',
                detail: errors.statement || 'Unable to generate statement. Please try again.',
                life: 5000
            });
        }
    });
};

// Clear filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedAccountType.value = null;
    selectedStatus.value = null;
};

// Create account
const handleAccountCreated = () => {
    showCreateModal.value = false;
    toast.add({
        severity: 'success',
        summary: 'Account Created',
        detail: 'Your new account has been created successfully',
        life: 3000
    });
};
</script>

<template>
    <Head title="My Accounts" />

    <DashboardLayout title="My Accounts">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        My Accounts
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage your bank accounts and view balances
                    </p>
                </div>
                <Button
                    label="Open New Account"
                    icon="pi pi-plus"
                    @click="showCreateModal = true"
                    :disabled="!canCreateAccount"
                    class="bg-gradient-to-r from-indigo-600 to-indigo-700"
                    v-tooltip.bottom="accountLimitMessage"
                />
            </div>
        </div>

        <!-- Account Statistics -->
        <AccountStats
            :accounts="accounts"
            :stats="stats"
            :selected-currency="selectedCurrency"
            class="mb-6"
        />

        <!-- Filters & Search -->
        <div class="p-4 mb-6 bg-white border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700 rounded-xl">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText
                            v-model="searchQuery"
                            placeholder="Search accounts..."
                            class="w-full"
                        />
                    </IconField>
                </div>

                <!-- Account Type Filter -->
                <Dropdown
                    v-model="selectedAccountType"
                    :options="accountTypeOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Account Type"
                    class="w-full"
                />

                <!-- Status Filter -->
                <Dropdown
                    v-model="selectedStatus"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Status"
                    class="w-full"
                />

                <!-- Sort -->
                <Dropdown
                    v-model="sortBy"
                    :options="sortOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Sort by"
                    class="w-full"
                />
            </div>

            <!-- Clear Filters -->
            <div v-if="hasActiveFilters" class="flex items-center justify-between pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ filteredAccounts.length }} account(s) found
                </p>
                <Button
                    label="Clear Filters"
                    icon="pi pi-times"
                    text
                    @click="clearFilters"
                    class="p-button-sm"
                />
            </div>
        </div>

        <!-- Accounts Grid -->
        <div v-if="filteredAccounts.length > 0" class="space-y-6">
            <!-- Account Limit Warning -->
            <div v-if="!canCreateAccount" class="p-4 border-l-4 border-amber-500 bg-amber-50 dark:bg-amber-900/20">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="text-amber-500 pi pi-exclamation-triangle"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700 dark:text-amber-400">
                            {{ accountLimitMessage }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Accounts Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <AccountCard
                    v-for="account in filteredAccounts"
                    :key="account.id"
                    :account="account"
                    @view-details="handleViewDetails"
                    @transfer="handleTransfer"
                    @freeze="handleFreeze"
                    @unfreeze="handleUnfreeze"
                    @set-primary="handleSetPrimary"
                    @download-statement="handleDownloadStatement"
                />
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="py-12 text-center">
            <div class="flex flex-col items-center justify-center">
                <div class="flex items-center justify-center w-20 h-20 mb-4 bg-gray-100 rounded-full dark:bg-gray-700">
                    <i class="text-4xl text-gray-400 pi pi-wallet dark:text-gray-500"></i>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                    {{ hasActiveFilters ? 'No accounts found' : 'No accounts yet' }}
                </h3>
                <p class="mb-6 text-gray-500 dark:text-gray-400">
                    {{ hasActiveFilters ? 'Try adjusting your search or filters' : 'Open your first account to get started' }}
                </p>
                <Button
                    v-if="!hasActiveFilters"
                    label="Open Your First Account"
                    icon="pi pi-plus"
                    @click="showCreateModal = true"
                    :disabled="!canCreateAccount"
                    class="bg-gradient-to-r from-indigo-600 to-indigo-700"
                />
                <Button
                    v-else
                    label="Clear Filters"
                    icon="pi pi-times"
                    outlined
                    @click="clearFilters"
                />
            </div>
        </div>

        <!-- Create Account Modal -->
        <CreateAccountModal
            v-model:visible="showCreateModal"
            :account-types="accountTypes"
            :currencies="currencies"
            @account-created="handleAccountCreated"
        />

        <!-- PIN Verification Modal -->
        <Dialog
            v-model:visible="showPinModal"
            modal
            :header="pinAction === 'freeze' ? 'Freeze Account' : 'Unfreeze Account'"
            :style="{ width: '400px' }"
            :closable="!isProcessing"
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
    </DashboardLayout>
</template>
