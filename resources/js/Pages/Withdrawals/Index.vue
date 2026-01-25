<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import ConfirmDialog from 'primevue/confirmdialog';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    withdrawals: Object,
    bankAccounts: Array,
});

const toast = useToast();
const confirm = useConfirm();
const { formatCurrency } = useCurrency();

const showDialog = ref(false);
const isProcessing = ref(false);

const form = useForm({
    bank_account_id: null,
    amount: null,
    reason: ''
});

const selectedAccount = computed(() => {
    return props.bankAccounts.find(acc => acc.id === form.bank_account_id);
});

const getStatusSeverity = (status) => {
    const severities = {
        'pending': 'warn',
        'approved': 'info',
        'completed': 'success',
        'rejected': 'danger',
        'failed': 'danger',
        'cancelled': 'secondary'
    };
    return severities[status] || 'secondary';
};

const getStatusLabel = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const openDialog = () => {
    form.reset();
    form.bank_account_id = props.bankAccounts[0]?.id;
    showDialog.value = true;
};

const submitWithdrawal = () => {
    if (!form.bank_account_id || !form.amount) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Please select an account and enter amount',
            life: 3000
        });
        return;
    }

    if (form.amount > (selectedAccount.value?.balance / 100)) {
        toast.add({
            severity: 'error',
            summary: 'Insufficient Balance',
            detail: 'Withdrawal amount exceeds available balance',
            life: 3000
        });
        return;
    }

    isProcessing.value = true;

    form.transform((data) => ({
        ...data,
        amount: Math.round((data.amount || 0) * 100), // Convert dollars to cents
    })).post(route('withdrawals.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showDialog.value = false;
            form.reset();
            toast.add({
                severity: 'success',
                summary: 'Withdrawal Requested',
                detail: 'Your withdrawal request has been submitted for approval',
                life: 5000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Withdrawal Failed',
                detail: errors.amount || errors.error || 'Unable to process withdrawal',
                life: 5000
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const cancelWithdrawal = (withdrawal) => {
    confirm.require({
        message: `Are you sure you want to cancel this withdrawal of ${formatCurrency(withdrawal.amount, withdrawal.currency)}?`,
        header: 'Cancel Withdrawal',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Yes, Cancel',
        rejectLabel: 'No',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.post(route('withdrawals.cancel', withdrawal.id), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: 'Withdrawal Cancelled',
                        detail: 'Amount has been refunded to your account',
                        life: 4000
                    });
                },
                onError: (errors) => {
                    toast.add({
                        severity: 'error',
                        summary: 'Cancellation Failed',
                        detail: errors.error || 'Unable to cancel withdrawal',
                        life: 3000
                    });
                }
            });
        }
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Withdrawals" />

    <DashboardLayout title="Withdrawals">
        <ConfirmDialog />

        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Withdrawals
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Request funds withdrawal to your external bank account
                    </p>
                </div>
                <Button
                    label="New Withdrawal"
                    icon="pi pi-plus"
                    @click="openDialog"
                    size="large"
                />
            </div>

            <!-- Withdrawals List -->
            <Card>
                <template #content>
                    <DataTable
                        :value="props.withdrawals.data"
                        :rows="20"
                        :paginator="props.withdrawals.data.length > 0"
                        responsiveLayout="scroll"
                        stripedRows
                    >
                        <template #empty>
                            <div class="text-center py-12">
                                <i class="pi pi-inbox text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                <p class="text-gray-500 dark:text-gray-400">No withdrawals yet</p>
                                <Button
                                    label="Make Your First Withdrawal"
                                    icon="pi pi-plus"
                                    class="mt-4"
                                    outlined
                                    @click="openDialog"
                                />
                            </div>
                        </template>

                        <Column field="reference_number" header="Reference" style="min-width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono text-sm">{{ data.reference_number }}</span>
                            </template>
                        </Column>

                        <Column field="bank_account" header="Account" style="min-width: 200px">
                            <template #body="{ data }">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ data.bank_account?.account_type?.name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        ****{{ data.bank_account?.account_number.slice(-4) }}
                                    </p>
                                </div>
                            </template>
                        </Column>

                        <Column field="amount" header="Amount" style="min-width: 120px">
                            <template #body="{ data }">
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(data.amount, data.currency) }}
                                </span>
                            </template>
                        </Column>

                        <Column field="status" header="Status" style="min-width: 120px">
                            <template #body="{ data }">
                                <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
                            </template>
                        </Column>

                        <Column field="reason" header="Reason" style="min-width: 200px">
                            <template #body="{ data }">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ data.reason || '-' }}
                                </span>
                            </template>
                        </Column>

                        <Column field="created_at" header="Date" style="min-width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>

                        <Column header="Actions" style="min-width: 120px">
                            <template #body="{ data }">
                                <Button
                                    v-if="data.status === 'pending'"
                                    label="Cancel"
                                    icon="pi pi-times"
                                    severity="danger"
                                    size="small"
                                    outlined
                                    @click="cancelWithdrawal(data)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- New Withdrawal Dialog -->
        <Dialog
            v-model:visible="showDialog"
            modal
            header="New Withdrawal Request"
            :style="{ width: '600px' }"
            :closable="!isProcessing"
        >
            <div class="space-y-4">
                <Message severity="info" :closable="false">
                    <p class="text-sm">
                        Withdrawal requests require admin approval. Funds will be deducted from your account immediately
                        and refunded if rejected.
                    </p>
                </Message>

                <!-- Select Account -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Withdraw From <span class="text-red-500">*</span>
                    </label>
                    <Dropdown
                        v-model="form.bank_account_id"
                        :options="props.bankAccounts"
                        optionLabel="account_type.name"
                        optionValue="id"
                        placeholder="Select Account"
                        class="w-full"
                        :disabled="isProcessing"
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center justify-between">
                                <span>{{ selectedAccount?.account_type?.name }}</span>
                                <span class="text-gray-500">****{{ selectedAccount?.account_number.slice(-4) }}</span>
                            </div>
                            <span v-else>{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex items-center justify-between py-2">
                                <div>
                                    <div class="font-medium">{{ slotProps.option.account_type?.name }}</div>
                                    <div class="text-sm text-gray-500">****{{ slotProps.option.account_number.slice(-4) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">{{ formatCurrency(slotProps.option.balance, slotProps.option.currency) }}</div>
                                    <div class="text-xs text-gray-500">Available</div>
                                </div>
                            </div>
                        </template>
                    </Dropdown>
                    <p v-if="selectedAccount" class="mt-1 text-xs text-gray-500">
                        Available Balance: {{ formatCurrency(selectedAccount.balance, selectedAccount.currency) }}
                    </p>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Amount <span class="text-red-500">*</span>
                    </label>
                    <InputNumber
                        v-model="form.amount"
                        mode="currency"
                        :currency="selectedAccount?.currency || 'USD'"
                        locale="en-US"
                        placeholder="0.00"
                        class="w-full"
                        :min="1"
                        :max="selectedAccount ? (selectedAccount.balance / 100) : undefined"
                        :disabled="!form.bank_account_id || isProcessing"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Enter the amount you want to withdraw
                    </p>
                </div>

                <!-- Reason -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Reason (Optional)
                    </label>
                    <Textarea
                        v-model="form.reason"
                        rows="3"
                        placeholder="e.g., Personal expenses, Emergency..."
                        class="w-full"
                        maxlength="500"
                        :disabled="isProcessing"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        {{ form.reason?.length || 0 }}/500 characters
                    </p>
                </div>

                <Message severity="warn" :closable="false">
                    <p class="text-sm">
                        <strong>Important:</strong> The amount will be deducted immediately. If your withdrawal is
                        rejected, it will be refunded to your account.
                    </p>
                </Message>
            </div>

            <template #footer>
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    @click="showDialog = false"
                    :disabled="isProcessing"
                />
                <Button
                    label="Submit Request"
                    icon="pi pi-check"
                    @click="submitWithdrawal"
                    :loading="isProcessing"
                    :disabled="!form.bank_account_id || !form.amount"
                />
            </template>
        </Dialog>
    </DashboardLayout>
</template>
