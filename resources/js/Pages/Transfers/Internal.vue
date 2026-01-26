<script setup>
/**
 * Internal Transfer Page
 * Transfer funds to another Finora Bank user
 */
import { ref, computed, watch } from 'vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import Steps from 'primevue/steps';
import Message from 'primevue/message';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PinInput from '@/Components/Forms/PinInput.vue';
import OtpInput from '@/Components/Forms/OtpInput.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    beneficiaries: {
        type: Array,
        default: () => []
    },
    transferLimits: {
        type: Object,
        default: () => ({
            daily: 50000,
            perTransaction: 10000
        })
    },
    preselectedAccountId: {
        type: [String, Number],
        default: null
    },
    verificationConfig: {
        type: Object,
        default: () => ({
            requiresOtp: true
        })
    }
});

const toast = useToast();
const { formatCurrency } = useCurrency();

// Form state
const form = useForm({
    from_account_id: props.preselectedAccountId || null,
    to_account_number: '',
    amount: null,
    description: '',
    pin: '',
    otp: ''
});

// UI State
const currentStep = ref(0);
const showPinModal = ref(false);
const showOtpModal = ref(false);
const isVerifyingRecipient = ref(false);
const recipientVerified = ref(false);
const recipientInfo = ref(null);
const isProcessing = ref(false);
const transferComplete = ref(false);
const transferResult = ref(null);

// Steps
const steps = [
    { label: 'Details' },
    { label: 'Confirm' },
    { label: 'Verify' },
    { label: 'Complete' }
];

// Account options
const accountOptions = computed(() => {
    return props.accounts
        .filter(a => a.is_active)
        .map(account => ({
            label: `${account.account_type?.name || 'Account'} - ****${account.account_number.slice(-4)} (${formatCurrency(account.balance, account.currency)})`,
            value: account.id,
            account: account
        }));
});

// Selected account
const selectedAccount = computed(() => {
    return props.accounts.find(a => a.id === form.from_account_id);
});

// Check if amount is valid
const isAmountValid = computed(() => {
    if (!form.amount || !selectedAccount.value) return false;
    const amountInCents = form.amount * 100;
    return amountInCents > 0 &&
           amountInCents <= selectedAccount.value.balance &&
           form.amount <= props.transferLimits.perTransaction;
});

// Can proceed to next step
const canProceed = computed(() => {
    if (currentStep.value === 0) {
        return form.from_account_id &&
               recipientVerified.value &&
               isAmountValid.value;
    }
    return true;
});

// Verify recipient
const verifyRecipient = async () => {
    if (!form.to_account_number || form.to_account_number.length < 10) {
        return;
    }

    isVerifyingRecipient.value = true;
    recipientVerified.value = false;
    recipientInfo.value = null;

    try {
        const response = await fetch(route('api.accounts.verify', { accountNumber: form.to_account_number }));
        const data = await response.json();

        if (data.success) {
            // Check if recipient account matches the sender's selected account
            const selectedAccount = props.accounts.find(a => a.id === form.from_account_id);
            if (selectedAccount && selectedAccount.account_number === form.to_account_number) {
                toast.add({
                    severity: 'error',
                    summary: 'Invalid Recipient',
                    detail: 'You cannot transfer to your own account',
                    life: 3000
                });
                return;
            }

            recipientVerified.value = true;
            recipientInfo.value = data.recipient;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Recipient Not Found',
                detail: 'No account found with this account number',
                life: 3000
            });
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Verification Failed',
            detail: 'Unable to verify recipient. Please try again.',
            life: 3000
        });
    } finally {
        isVerifyingRecipient.value = false;
    }
};

// Watch for account number changes
watch(() => form.to_account_number, () => {
    recipientVerified.value = false;
    recipientInfo.value = null;
});

// Navigation
const goToStep = (step) => {
    if (step < currentStep.value) {
        currentStep.value = step;
    }
};

const nextStep = () => {
    if (currentStep.value === 0 && canProceed.value) {
        currentStep.value = 1;
    } else if (currentStep.value === 1) {
        showPinModal.value = true;
    }
};

const prevStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

// PIN verification
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

    showPinModal.value = false;
    isProcessing.value = true;

    // Check if OTP is required
    if (props.verificationConfig.requiresOtp) {
        // Request OTP
        router.post('/transfers/internal/request-otp', {
            pin: form.pin,
            from_account_id: form.from_account_id,
            amount: Math.round((form.amount || 0) * 100) // Convert dollars to cents
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                currentStep.value = 2;
                showOtpModal.value = true;
                const successMessage = page?.props?.flash?.success || 'A verification code has been sent to your email';
                toast.add({
                    severity: 'success',
                    summary: 'OTP Sent',
                    detail: successMessage,
                    life: 5000
                });
            },
            onError: (errors) => {
                toast.add({
                    severity: 'error',
                    summary: 'Verification Failed',
                    detail: errors.pin || 'Invalid PIN. Please try again.',
                    life: 3000
                });
            },
            onFinish: () => {
                isProcessing.value = false;
            }
        });
    } else {
        // Process transfer directly without OTP
        router.post(route('transfers.internal.process'), {
            from_account_id: form.from_account_id,
            to_account_number: form.to_account_number,
            amount: Math.round((form.amount || 0) * 100), // Convert dollars to cents
            description: form.description,
            pin: form.pin,
            otp: 'skip_otp' // Special flag for skipped OTP
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                transferComplete.value = true;
                transferResult.value = page.props.transfer;
                currentStep.value = 3;
                toast.add({
                    severity: 'success',
                    summary: 'Transfer Successful',
                    detail: 'Your transfer has been completed',
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
    }
};

// OTP verification and transfer
const handleOtpSubmit = () => {
    if (form.otp.length !== 6) {
        toast.add({
            severity: 'error',
            summary: 'Invalid OTP',
            detail: 'Please enter the 6-digit verification code',
            life: 3000
        });
        return;
    }

    isProcessing.value = true;

    router.post(route('transfers.internal.process'), {
        from_account_id: form.from_account_id,
        to_account_number: form.to_account_number,
        amount: Math.round((form.amount || 0) * 100), // Convert dollars to cents
        description: form.description,
        pin: form.pin,
        otp: form.otp
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            showOtpModal.value = false;
            transferComplete.value = true;
            transferResult.value = page.props.transfer;
            currentStep.value = 3;
            toast.add({
                severity: 'success',
                summary: 'Transfer Successful',
                detail: 'Your transfer has been completed',
                life: 5000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Transfer Failed',
                detail: errors.otp || errors.general || 'Transfer could not be completed',
                life: 5000
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

// Resend OTP
const resendOtp = () => {
    router.post(route('transfers.internal.request-otp'), {
        pin: form.pin,
        from_account_id: form.from_account_id,
        amount: form.amount
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            const successMessage = page?.props?.flash?.success || 'A new verification code has been sent';
            toast.add({
                severity: 'success',
                summary: 'OTP Resent',
                detail: successMessage,
                life: 3000
            });
        }
    });
};

// Start new transfer
const startNewTransfer = () => {
    form.reset();
    currentStep.value = 0;
    transferComplete.value = false;
    transferResult.value = null;
    recipientVerified.value = false;
    recipientInfo.value = null;
};
</script>

<template>
    <Head title="Internal Transfer" />

    <DashboardLayout title="Internal Transfer">
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
                    Internal Transfer
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Transfer funds instantly to another Finora Bank user
                </p>
            </div>
        </div>

        <!-- Transfer Form -->
        <div class="max-w-2xl mx-auto">
            <!-- Step 1: Transfer Details -->
            <Card v-if="currentStep === 0">
                <template #title>
                    <div class="mb-4">
                        <Steps :model="steps" :activeStep="currentStep" :readonly="true" />
                    </div>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-file-edit text-primary-500"></i>
                        Transfer Details
                    </div>
                </template>
                <template #content>
                    <div class="space-y-6">
                        <!-- Source Account Selector -->
                        <div>
                            <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-wallet text-primary-600"></i>
                                    Select Source Account <span class="text-red-500">*</span>
                                </div>
                            </label>
                            <div class="space-y-3">
                                <div
                                    v-for="account in props.accounts.filter(a => a.is_active)"
                                    :key="account.id"
                                    @click="form.from_account_id = account.id"
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
                            <small v-if="form.errors.from_account_id" class="mt-2 text-red-500 block">
                                {{ form.errors.from_account_id }}
                            </small>
                        </div>

                        <!-- To Account Number -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Recipient Account Number <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <InputText
                                    v-model="form.to_account_number"
                                    placeholder="Enter account number"
                                    class="flex-1"
                                    :class="{ 'p-invalid': form.errors.to_account_number }"
                                />
                                <Button
                                    label="Verify"
                                    icon="pi pi-check"
                                    :loading="isVerifyingRecipient"
                                    :disabled="!form.to_account_number || form.to_account_number.length < 10"
                                    @click="verifyRecipient"
                                />
                            </div>
                            <small v-if="form.errors.to_account_number" class="text-red-500">
                                {{ form.errors.to_account_number }}
                            </small>
                        </div>

                        <!-- Recipient Info -->
                        <Message v-if="recipientVerified && recipientInfo" severity="success" :closable="false">
                            <div class="flex items-center gap-3">
                                <i class="text-xl pi pi-user"></i>
                                <div>
                                    <p class="font-semibold">{{ recipientInfo.name }}</p>
                                    <p class="text-sm opacity-80">{{ recipientInfo.account_type }} Account</p>
                                </div>
                            </div>
                        </Message>

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
                                :class="{ 'p-invalid': form.errors.amount }"
                                :min="1"
                                :max="props.transferLimits.perTransaction"
                            />
                            <div class="flex justify-between mt-1 text-xs text-gray-500">
                                <span v-if="selectedAccount">
                                    Available: {{ formatCurrency(selectedAccount.balance, selectedAccount.currency) }}
                                </span>
                                <span>
                                    Limit: {{ formatCurrency(props.transferLimits.perTransaction * 100, selectedAccount?.currency || 'USD') }}/transaction
                                </span>
                            </div>
                            <small v-if="form.errors.amount" class="text-red-500">
                                {{ form.errors.amount }}
                            </small>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description (Optional)
                            </label>
                            <Textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Add a note for this transfer"
                                class="w-full"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-4">
                            <Link href="/transfers">
                                <Button label="Cancel" outlined />
                            </Link>
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                iconPos="right"
                                :disabled="!canProceed"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 2: Confirmation -->
            <Card v-if="currentStep === 1">
                <template #title>
                    <div class="mb-4">
                        <Steps :model="steps" :activeStep="currentStep" :readonly="true" />
                    </div>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-eye text-primary-500"></i>
                        Confirm Transfer
                    </div>
                </template>
                <template #content>
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Confirm Transfer Details
                        </h3>

                        <div class="p-4 space-y-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="flex justify-between">
                                <span class="text-gray-500">From</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    ****{{ selectedAccount?.account_number.slice(-4) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">To</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ recipientInfo?.name }} (****{{ form.to_account_number.slice(-4) }})
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Amount</span>
                                <span class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(form.amount * 100, selectedAccount?.currency || 'USD') }}
                                </span>
                            </div>
                            <div v-if="form.description" class="flex justify-between">
                                <span class="text-gray-500">Description</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ form.description }}
                                </span>
                            </div>
                            <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-gray-500">Fee</span>
                                <span class="font-medium text-green-600">Free</span>
                            </div>
                        </div>

                        <Message severity="info" :closable="false">
                            <p class="text-sm">
                                By proceeding, you authorize this transfer. This action cannot be undone.
                            </p>
                        </Message>

                        <!-- Actions -->
                        <div class="flex justify-between gap-3 pt-4">
                            <Button
                                label="Back"
                                icon="pi pi-arrow-left"
                                outlined
                                @click="prevStep"
                            />
                            <Button
                                label="Confirm & Enter PIN"
                                icon="pi pi-lock"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 3: Verification (handled by modals) -->
            <Card v-if="currentStep === 2">
                <template #title>
                    <div class="mb-4">
                        <Steps :model="steps" :activeStep="currentStep" :readonly="true" />
                    </div>
                </template>
                <template #content>
                    <div class="py-8 text-center">
                        <i class="mb-4 text-5xl text-indigo-600 pi pi-shield"></i>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Verification in Progress
                        </h3>
                        <p class="mt-2 text-gray-500">
                            Please complete the verification to proceed with your transfer.
                        </p>
                        <Button
                            label="Enter Verification Code"
                            icon="pi pi-key"
                            class="mt-4"
                            @click="showOtpModal = true"
                        />
                    </div>
                </template>
            </Card>

            <!-- Step 4: Complete -->
            <Card v-if="currentStep === 3 && transferComplete">
                <template #title>
                    <div class="mb-4">
                        <Steps :model="steps" :activeStep="currentStep" :readonly="true" />
                    </div>
                </template>
                <template #content>
                    <div class="py-8 text-center">
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30">
                            <i class="text-4xl text-green-600 pi pi-check dark:text-green-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Transfer Successful!
                        </h3>
                        <p class="mt-2 text-gray-500">
                            Your transfer has been completed successfully.
                        </p>

                        <div class="p-4 mt-6 text-left rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Reference</span>
                                    <span class="font-mono text-gray-900 dark:text-white">
                                        {{ transferResult?.reference || 'TXN-XXXXXXXX' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Amount</span>
                                    <span class="font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(form.amount * 100, selectedAccount?.currency || 'USD') }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Recipient</span>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ recipientInfo?.name }}
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
                    Enter your 6-digit transaction PIN to continue.
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
                    label="Verify"
                    icon="pi pi-check"
                    :loading="isProcessing"
                    @click="handlePinSubmit"
                />
            </template>
        </Dialog>

        <!-- OTP Modal -->
        <Dialog
            v-model:visible="showOtpModal"
            modal
            header="Email Verification"
            :style="{ width: '400px' }"
            :closable="!isProcessing"
        >
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Enter the 6-digit code sent to your registered email address.
                </p>

                <OtpInput
                    v-model="form.otp"
                    :length="6"
                    @complete="handleOtpSubmit"
                    @resend="resendOtp"
                />
            </div>

            <template #footer>
                <Button
                    label="Cancel"
                    text
                    @click="showOtpModal = false; currentStep = 1"
                    :disabled="isProcessing"
                />
                <Button
                    label="Verify & Transfer"
                    icon="pi pi-send"
                    :loading="isProcessing"
                    @click="handleOtpSubmit"
                />
            </template>
        </Dialog>
    </DashboardLayout>
</template>
