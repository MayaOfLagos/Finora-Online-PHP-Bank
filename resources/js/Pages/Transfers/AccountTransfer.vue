<script setup>
/**
 * Account Transfer Page
 * Transfer funds between your own accounts
 */
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PinInput from '@/Components/Forms/PinInput.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const { formatCurrency } = useCurrency();

// Form state
const form = useForm({
    from_account_id: null,
    to_account_id: null,
    amount: null,
    description: '',
    pin: ''
});

// UI State
const showPinModal = ref(false);
const isProcessing = ref(false);
const transferComplete = ref(false);
const transferResult = ref(null);

// Selected accounts
const fromAccount = computed(() => {
    return props.accounts.find(a => a.id === form.from_account_id);
});

const toAccount = computed(() => {
    return props.accounts.find(a => a.id === form.to_account_id);
});

// Validate form
const isFormValid = computed(() => {
    return form.from_account_id &&
           form.to_account_id &&
           form.from_account_id !== form.to_account_id &&
           form.amount &&
           form.amount > 0 &&
           fromAccount.value?.balance >= (form.amount * 100);
});

// Handle PIN submission
const handlePinSubmit = () => {
    if (form.pin.length !== 6) {
        toast.add({
            severity: 'error',
            summary: 'Invalid PIN',
            detail: 'Please enter your 6-digit transaction PIN',
            life: 3000
        });
        return;
    }

    if (!isFormValid.value) {
        toast.add({
            severity: 'error',
            summary: 'Invalid Transfer',
            detail: 'Please verify all transfer details',
            life: 3000
        });
        return;
    }

    showPinModal.value = false;
    isProcessing.value = true;

    form.transform((data) => ({
        ...data,
        amount: Math.round((data.amount || 0) * 100), // Convert dollars to cents
    })).post(route('transfers.account.process'), {
        preserveScroll: true,
        onSuccess: () => {
            transferComplete.value = true;
            transferResult.value = form.data();
            toast.add({
                severity: 'success',
                summary: 'Transfer Successful',
                detail: 'Your account transfer has been completed',
                life: 5000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Transfer Failed',
                detail: errors.pin || errors.general || 'Transfer could not be completed',
                life: 5000
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

// Start new transfer
const startNewTransfer = () => {
    form.reset();
    transferComplete.value = false;
    transferResult.value = null;
};
</script>

<template>
    <Head title="Account Transfer" />

    <DashboardLayout title="Account Transfer">
        <div class="max-w-2xl mx-auto">
            <!-- Back Button -->
            <Link
                href="/transfers"
                class="inline-flex items-center gap-2 mb-6 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <i class="pi pi-arrow-left"></i>
                Back to Transfers
            </Link>

            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Account Transfer
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Transfer funds between your own accounts instantly
                </p>
            </div>
        </div>

        <!-- Transfer Form -->
        <div class="max-w-2xl mx-auto">
            <!-- Transfer Card -->
            <Card v-if="!transferComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-exchange text-primary-500"></i>
                        Transfer Between Accounts
                    </div>
                </template>
                <template #content>
                    <div class="space-y-6">
                        <!-- From Account -->
                        <div>
                            <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-arrow-right text-primary-600"></i>
                                    From Account <span class="text-red-500">*</span>
                                </div>
                            </label>
                            <div class="space-y-3">
                                <div
                                    v-for="account in props.accounts.filter(a => a.is_active)"
                                    :key="account.id"
                                    @click="form.from_account_id = account.id; form.to_account_id = null"
                                    class="p-4 transition-all duration-300 ease-in-out border-2 rounded-lg cursor-pointer"
                                    :class="[
                                        form.from_account_id === account.id
                                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20 shadow-md shadow-green-500/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center w-10 h-10 transition-all duration-300 rounded-full"
                                                :class="[
                                                    form.from_account_id === account.id
                                                        ? 'bg-green-500 text-white'
                                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                                ]"
                                            >
                                                <i
                                                    class="pi transition-all duration-300"
                                                    :class="[
                                                        form.from_account_id === account.id
                                                            ? 'pi-check'
                                                            : 'pi-wallet'
                                                    ]"
                                                ></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ account.account_type?.name || 'Account' }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    ****{{ account.account_number.slice(-4) }} • {{ account.currency || 'USD' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="text-lg font-bold"
                                                :class="[
                                                    form.from_account_id === account.id
                                                        ? 'text-green-600'
                                                        : 'text-gray-900 dark:text-white'
                                                ]"
                                            >
                                                {{ formatCurrency(account.balance, account.currency) }}
                                            </p>
                                            <p class="text-xs text-gray-500">Available</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- To Account -->
                        <div v-if="form.from_account_id">
                            <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-arrow-left text-primary-600"></i>
                                    To Account <span class="text-red-500">*</span>
                                </div>
                            </label>
                            <div class="space-y-3">
                                <div
                                    v-for="account in props.accounts.filter(a => a.is_active && a.id !== form.from_account_id)"
                                    :key="account.id"
                                    @click="form.to_account_id = account.id"
                                    class="p-4 transition-all duration-300 ease-in-out border-2 rounded-lg cursor-pointer"
                                    :class="[
                                        form.to_account_id === account.id
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-md shadow-blue-500/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center w-10 h-10 transition-all duration-300 rounded-full"
                                                :class="[
                                                    form.to_account_id === account.id
                                                        ? 'bg-blue-500 text-white'
                                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                                ]"
                                            >
                                                <i
                                                    class="pi transition-all duration-300"
                                                    :class="[
                                                        form.to_account_id === account.id
                                                            ? 'pi-check'
                                                            : 'pi-wallet'
                                                    ]"
                                                ></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ account.account_type?.name || 'Account' }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    ****{{ account.account_number.slice(-4) }} • {{ account.currency || 'USD' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="text-lg font-bold"
                                                :class="[
                                                    form.to_account_id === account.id
                                                        ? 'text-blue-600'
                                                        : 'text-gray-900 dark:text-white'
                                                ]"
                                            >
                                                {{ formatCurrency(account.balance, account.currency) }}
                                            </p>
                                            <p class="text-xs text-gray-500">Current balance</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div v-if="form.to_account_id">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <InputNumber
                                v-model="form.amount"
                                mode="currency"
                                :currency="fromAccount?.currency || 'USD'"
                                locale="en-US"
                                placeholder="0.00"
                                class="w-full"
                                :min="1"
                                :max="fromAccount?.balance / 100 || 0"
                            />
                            <div class="flex justify-between mt-1 text-xs text-gray-500">
                                <span v-if="fromAccount">
                                    Available: {{ formatCurrency(fromAccount.balance, fromAccount.currency) }}
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="form.to_account_id">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description (Optional)
                            </label>
                            <Textarea
                                v-model="form.description"
                                rows="2"
                                placeholder="Add a note for this transfer"
                                class="w-full"
                            />
                        </div>

                        <!-- Summary -->
                        <Message v-if="isFormValid" severity="info" :closable="false">
                            <div class="space-y-2 text-sm">
                                <p><strong>From:</strong> {{ fromAccount?.account_type?.name }} (****{{ fromAccount?.account_number.slice(-4) }})</p>
                                <p><strong>To:</strong> {{ toAccount?.account_type?.name }} (****{{ toAccount?.account_number.slice(-4) }})</p>
                                <p><strong>Amount:</strong> {{ formatCurrency(form.amount * 100, fromAccount?.currency) }}</p>
                            </div>
                        </Message>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-4">
                            <Link href="/transfers">
                                <Button label="Cancel" outlined />
                            </Link>
                            <Button
                                label="Transfer"
                                icon="pi pi-send"
                                :disabled="!isFormValid"
                                @click="showPinModal = true"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Success Card -->
            <Card v-if="transferComplete" class="shadow-lg">
                <template #content>
                    <div class="py-8 text-center">
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30">
                            <i class="text-4xl text-green-600 pi pi-check dark:text-green-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Transfer Successful!
                        </h3>
                        <p class="mt-2 text-gray-500">
                            Your account transfer has been completed.
                        </p>

                        <div class="p-4 mt-6 text-left rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Reference</span>
                                    <span class="font-mono text-gray-900 dark:text-white">
                                        {{ transferResult?.transfer?.reference || 'TXN-XXXXXXXX' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Amount</span>
                                    <span class="font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(form.amount * 100, fromAccount?.currency) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">From</span>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ fromAccount?.account_type?.name }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">To</span>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ toAccount?.account_type?.name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-3 mt-6">
                            <Button
                                label="New Transfer"
                                icon="pi pi-plus"
                                outlined
                                @click="startNewTransfer"
                            />
                            <Link href="/accounts">
                                <Button
                                    label="View Accounts"
                                    icon="pi pi-wallet"
                                />
                            </Link>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- PIN Modal -->
        <Dialog
            v-model:visible="showPinModal"
            modal
            header="Enter Transaction PIN"
            :style="{ width: '480px' }"
            :closable="!isProcessing"
        >
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Enter your 6-digit transaction PIN to confirm this transfer.
                </p>

                <PinInput
                    v-model="form.pin"
                    :length="6"
                    masked
                    @complete="handlePinSubmit"
                />
            </div>

            <template #footer>
                <Button
                    label="Cancel"
                    text
                    @click="showPinModal = false"
                    :disabled="isProcessing"
                />
                <Button
                    label="Confirm"
                    icon="pi pi-check"
                    :loading="isProcessing"
                    @click="handlePinSubmit"
                />
            </template>
        </Dialog>
    </DashboardLayout>
</template>
