<script setup>
/**
 * Beneficiaries Page
 * Manage saved beneficiaries for quick transfers
 */
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Menu from 'primevue/menu';
import InputNumber from 'primevue/inputnumber';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PinInput from '@/Components/Forms/PinInput.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    beneficiaries: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    }
});

const toast = useToast();
const confirm = useConfirm();
const { formatCurrency } = useCurrency();

// State
const searchQuery = ref('');
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedBeneficiary = ref(null);
const isVerifying = ref(false);
const verifiedAccount = ref(null);

// Add beneficiary form
const addForm = useForm({
    account_number: '',
    nickname: '',
    pin: '',
});

// Edit beneficiary form
const editForm = useForm({
    nickname: '',
    is_favorite: false,
    transfer_limit: null,
});

// Delete form (for PIN)
const deleteForm = useForm({
    pin: '',
});

// Filtered beneficiaries
const filteredBeneficiaries = computed(() => {
    if (!searchQuery.value) return props.beneficiaries;

    const query = searchQuery.value.toLowerCase();
    return props.beneficiaries.filter(b =>
        b.nickname?.toLowerCase().includes(query) ||
        b.beneficiary_account?.account_number?.includes(query) ||
        b.beneficiary_user?.name?.toLowerCase().includes(query) ||
        b.beneficiary_user?.email?.toLowerCase().includes(query)
    );
});

// Verify account number
const verifyAccount = async () => {
    if (!addForm.account_number || addForm.account_number.length < 10) {
        toast.add({
            severity: 'warn',
            summary: 'Invalid',
            detail: 'Please enter a valid account number',
            life: 3000
        });
        return;
    }

    isVerifying.value = true;
    verifiedAccount.value = null;

    try {
        const response = await fetch(route('api.accounts.verify', { accountNumber: addForm.account_number }), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.recipient) {
            verifiedAccount.value = {
                account_name: data.recipient.name,
                account_type: data.recipient.account_type,
            };
            toast.add({
                severity: 'success',
                summary: 'Account Found',
                detail: `Account holder: ${data.recipient.name}`,
                life: 3000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Not Found',
                detail: data.message || 'No account found with this number',
                life: 3000
            });
        }
    } catch (error) {
        console.error('Verify account error:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to verify account',
            life: 3000
        });
    } finally {
        isVerifying.value = false;
    }
};

// Submit add beneficiary
const submitAdd = () => {
    if (!verifiedAccount.value) {
        toast.add({
            severity: 'warn',
            summary: 'Verify Account',
            detail: 'Please verify the account number first',
            life: 3000
        });
        return;
    }

    addForm.post(route('beneficiaries.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Beneficiary added successfully',
                life: 3000
            });
            closeAddModal();
        },
        onError: (errors) => {
            if (errors.pin) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: errors.pin,
                    life: 3000
                });
            }
        }
    });
};

// Open edit modal
const openEditModal = (beneficiary) => {
    selectedBeneficiary.value = beneficiary;
    editForm.nickname = beneficiary.nickname || '';
    editForm.is_favorite = beneficiary.is_favorite || false;
    editForm.transfer_limit = beneficiary.transfer_limit ? beneficiary.transfer_limit / 100 : null;
    showEditModal.value = true;
};

// Submit edit
const submitEdit = () => {
    editForm.transform((data) => ({
        ...data,
        transfer_limit: data.transfer_limit ? data.transfer_limit * 100 : null,
    })).patch(route('beneficiaries.update', { beneficiary: selectedBeneficiary.value.uuid }), {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Beneficiary updated successfully',
                life: 3000
            });
            closeEditModal();
        },
    });
};

// Toggle favorite
const toggleFavorite = (beneficiary) => {
    router.patch(route('beneficiaries.toggle-favorite', { beneficiary: beneficiary.uuid }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: beneficiary.is_favorite ? 'Removed from favorites' : 'Added to favorites',
                life: 3000
            });
        },
    });
};

// Open delete modal
const openDeleteModal = (beneficiary) => {
    selectedBeneficiary.value = beneficiary;
    deleteForm.reset();
    showDeleteModal.value = true;
};

// Submit delete
const submitDelete = () => {
    deleteForm.delete(route('beneficiaries.destroy', { beneficiary: selectedBeneficiary.value.uuid }), {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Beneficiary deleted successfully',
                life: 3000
            });
            closeDeleteModal();
        },
        onError: (errors) => {
            if (errors.pin) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: errors.pin,
                    life: 3000
                });
            }
        }
    });
};

// Quick transfer
const quickTransfer = (beneficiary) => {
    router.visit(route('transfers.internal'), {
        data: { beneficiary: beneficiary.uuid }
    });
};

// Close modals
const closeAddModal = () => {
    showAddModal.value = false;
    addForm.reset();
    verifiedAccount.value = null;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedBeneficiary.value = null;
    editForm.reset();
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedBeneficiary.value = null;
    deleteForm.reset();
};

// Get action menu items
const getMenuItems = (beneficiary) => [
    {
        label: 'Quick Transfer',
        icon: 'pi pi-send',
        command: () => quickTransfer(beneficiary)
    },
    {
        label: 'Edit',
        icon: 'pi pi-pencil',
        command: () => openEditModal(beneficiary)
    },
    {
        label: beneficiary.is_favorite ? 'Remove from Favorites' : 'Add to Favorites',
        icon: beneficiary.is_favorite ? 'pi pi-star' : 'pi pi-star-fill',
        command: () => toggleFavorite(beneficiary)
    },
    {
        separator: true
    },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        class: 'text-red-600',
        command: () => openDeleteModal(beneficiary)
    }
];
</script>

<template>
    <Head title="Beneficiaries" />

    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Beneficiaries
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage your saved beneficiaries for quick transfers
                    </p>
                </div>
                <Button
                    label="Add Beneficiary"
                    icon="pi pi-plus"
                    @click="showAddModal = true"
                />
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm border-l-4 border-l-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Beneficiaries</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ stats.total_beneficiaries || 0 }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30">
                            <i class="pi pi-users text-xl text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                    </div>
                </div>

                <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm border-l-4 border-l-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ stats.verified_beneficiaries || 0 }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30">
                            <i class="pi pi-check-circle text-xl text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                </div>

                <div class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm border-l-4 border-l-amber-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Favorites</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ stats.favorite_beneficiaries || 0 }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30">
                            <i class="pi pi-star-fill text-xl text-amber-600 dark:text-amber-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <IconField class="w-full md:w-80">
                    <InputIcon class="pi pi-search" />
                    <InputText
                        v-model="searchQuery"
                        placeholder="Search beneficiaries..."
                        class="w-full"
                    />
                </IconField>
            </div>

            <!-- Beneficiaries Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="beneficiary in filteredBeneficiaries"
                    :key="beneficiary.uuid"
                    class="overflow-hidden bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all duration-300"
                >
                    <!-- Card Header -->
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <!-- Avatar -->
                                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 text-white font-bold text-lg">
                                    {{ (beneficiary.beneficiary_user?.name || beneficiary.nickname || 'B').charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ beneficiary.nickname || beneficiary.beneficiary_user?.name || 'Unknown' }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ beneficiary.beneficiary_user?.email || '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <i
                                    v-if="beneficiary.is_favorite"
                                    class="pi pi-star-fill text-amber-500"
                                    title="Favorite"
                                ></i>
                                <Button
                                    icon="pi pi-ellipsis-v"
                                    text
                                    rounded
                                    size="small"
                                    @click="(event) => $refs[`menu_${beneficiary.uuid}`][0].toggle(event)"
                                />
                                <Menu
                                    :ref="`menu_${beneficiary.uuid}`"
                                    :model="getMenuItems(beneficiary)"
                                    popup
                                />
                            </div>
                        </div>

                        <!-- Account Details -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between py-2 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Account Number</span>
                                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                    ****{{ beneficiary.beneficiary_account?.account_number?.slice(-4) || '****' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Account Type</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ beneficiary.beneficiary_account?.account_type || 'Savings' }}
                                </span>
                            </div>
                            <div v-if="beneficiary.transfer_limit" class="flex items-center justify-between py-2 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Transfer Limit</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrency(beneficiary.transfer_limit, beneficiary.beneficiary_account?.currency || 'USD') }}
                                </span>
                            </div>
                            <div v-if="beneficiary.last_used_at" class="flex items-center justify-between py-2 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Last Used</span>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ beneficiary.last_used_at_human }}
                                </span>
                            </div>
                        </div>

                        <!-- Status Tags -->
                        <div class="flex gap-2 mt-4">
                            <Tag
                                :value="beneficiary.is_verified ? 'Verified' : 'Unverified'"
                                :severity="beneficiary.is_verified ? 'success' : 'warning'"
                                class="text-xs"
                            />
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50">
                        <Button
                            label="Quick Transfer"
                            icon="pi pi-send"
                            class="w-full"
                            @click="quickTransfer(beneficiary)"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredBeneficiaries.length === 0"
                class="flex flex-col items-center justify-center py-16 text-center"
            >
                <div class="flex items-center justify-center w-20 h-20 mb-4 rounded-full bg-gray-100 dark:bg-gray-800">
                    <i class="pi pi-users text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ searchQuery ? 'No beneficiaries found' : 'No beneficiaries yet' }}
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ searchQuery ? 'Try adjusting your search' : 'Add beneficiaries for quick transfers' }}
                </p>
                <Button
                    v-if="!searchQuery"
                    label="Add Your First Beneficiary"
                    icon="pi pi-plus"
                    class="mt-4"
                    @click="showAddModal = true"
                />
            </div>
        </div>

        <!-- Add Beneficiary Modal -->
        <Dialog
            v-model:visible="showAddModal"
            modal
            header="Add Beneficiary"
            :style="{ width: '450px' }"
            :closable="!addForm.processing"
            @hide="closeAddModal"
        >
            <div class="space-y-4">
                <!-- Account Number -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Account Number
                    </label>
                    <div class="flex gap-2">
                        <InputText
                            v-model="addForm.account_number"
                            placeholder="Enter account number"
                            class="flex-1"
                            :disabled="addForm.processing"
                        />
                        <Button
                            icon="pi pi-search"
                            :loading="isVerifying"
                            @click="verifyAccount"
                        />
                    </div>
                    <p v-if="addForm.errors.account_number" class="mt-1 text-sm text-red-600">
                        {{ addForm.errors.account_number }}
                    </p>
                </div>

                <!-- Verified Account Info -->
                <div v-if="verifiedAccount" class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30">
                            <i class="pi pi-check text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-green-800 dark:text-green-200">
                                {{ verifiedAccount.account_name }}
                            </p>
                            <p class="text-sm text-green-600 dark:text-green-400">
                                {{ verifiedAccount.account_type }} - Account verified
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Nickname -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nickname (Optional)
                    </label>
                    <InputText
                        v-model="addForm.nickname"
                        placeholder="E.g., Mom, John's Account"
                        class="w-full"
                        :disabled="addForm.processing"
                    />
                </div>

                <!-- Transaction PIN -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Transaction PIN
                    </label>
                    <PinInput
                        v-model="addForm.pin"
                        :length="6"
                        masked
                        :disabled="addForm.processing"
                    />
                    <p v-if="addForm.errors.pin" class="mt-1 text-sm text-red-600">
                        {{ addForm.errors.pin }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button
                        label="Cancel"
                        outlined
                        :disabled="addForm.processing"
                        @click="closeAddModal"
                    />
                    <Button
                        label="Add Beneficiary"
                        icon="pi pi-plus"
                        :loading="addForm.processing"
                        :disabled="!verifiedAccount || !addForm.pin"
                        @click="submitAdd"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Edit Beneficiary Modal -->
        <Dialog
            v-model:visible="showEditModal"
            modal
            header="Edit Beneficiary"
            :style="{ width: '450px' }"
            :closable="!editForm.processing"
            @hide="closeEditModal"
        >
            <div class="space-y-4">
                <!-- Nickname -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nickname
                    </label>
                    <InputText
                        v-model="editForm.nickname"
                        placeholder="E.g., Mom, John's Account"
                        class="w-full"
                        :disabled="editForm.processing"
                    />
                </div>

                <!-- Transfer Limit -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Transfer Limit (Optional)
                    </label>
                    <InputNumber
                        v-model="editForm.transfer_limit"
                        mode="currency"
                        currency="USD"
                        locale="en-US"
                        placeholder="No limit"
                        class="w-full"
                        :disabled="editForm.processing"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Set a maximum transfer amount for this beneficiary
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button
                        label="Cancel"
                        outlined
                        :disabled="editForm.processing"
                        @click="closeEditModal"
                    />
                    <Button
                        label="Save Changes"
                        icon="pi pi-check"
                        :loading="editForm.processing"
                        @click="submitEdit"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog
            v-model:visible="showDeleteModal"
            modal
            header="Delete Beneficiary"
            :style="{ width: '400px' }"
            :closable="!deleteForm.processing"
            @hide="closeDeleteModal"
        >
            <div class="space-y-4">
                <div class="flex items-center gap-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30">
                        <i class="pi pi-exclamation-triangle text-2xl text-red-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-red-800 dark:text-red-200">
                            Are you sure?
                        </p>
                        <p class="text-sm text-red-600 dark:text-red-400">
                            This action cannot be undone.
                        </p>
                    </div>
                </div>

                <p class="text-gray-600 dark:text-gray-400">
                    You are about to delete <strong>{{ selectedBeneficiary?.nickname || 'this beneficiary' }}</strong>.
                </p>

                <!-- Transaction PIN -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Enter Transaction PIN to confirm
                    </label>
                    <PinInput
                        v-model="deleteForm.pin"
                        :length="6"
                        masked
                        :disabled="deleteForm.processing"
                    />
                    <p v-if="deleteForm.errors.pin" class="mt-1 text-sm text-red-600">
                        {{ deleteForm.errors.pin }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button
                        label="Cancel"
                        outlined
                        :disabled="deleteForm.processing"
                        @click="closeDeleteModal"
                    />
                    <Button
                        label="Delete"
                        icon="pi pi-trash"
                        severity="danger"
                        :loading="deleteForm.processing"
                        :disabled="!deleteForm.pin"
                        @click="submitDelete"
                    />
                </div>
            </template>
        </Dialog>
    </DashboardLayout>
</template>
