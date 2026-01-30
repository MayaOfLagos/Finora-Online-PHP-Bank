<script setup>
/**
 * Domestic Transfer Page
 * Transfer to other local banks with PIN + OTP verification
 * Flow: Form → Review → PIN → OTP (if enabled) → Complete
 */
import { ref, computed, watch } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import Steps from 'primevue/steps';
import Message from 'primevue/message';
import Divider from 'primevue/divider';
import ProgressBar from 'primevue/progressbar';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PinInput from '@/Components/Forms/PinInput.vue';
import OtpInput from '@/Components/Forms/OtpInput.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    banks: {
        type: Array,
        default: () => []
    },
    transferLimits: {
        type: Object,
        default: () => ({
            daily: 50000,
            perTransaction: 25000
        })
    },
    fees: {
        type: Object,
        default: () => ({
            flat: 5,
            percentage: 0.1
        })
    },
    verificationConfig: {
        type: Object,
        default: () => ({
            requiresOtp: true
        })
    }
});

const page = usePage();
const toast = useToast();
const { formatCurrency } = useCurrency();

// ==================== Form State ====================
const formData = ref({
    from_account_id: null,
    bank_id: null,
    beneficiary_name: '',
    beneficiary_account: '',
    amount: null,
    description: ''
});

// ==================== UI State ====================
const currentStep = ref(0);
const showVerificationModal = ref(false);
const verificationStep = ref('pin'); // pin, otp
const isProcessing = ref(false);
const transferComplete = ref(false);
const transferResult = ref(null);
const activeTransfer = ref(null);
const formErrors = ref({});

// PIN/OTP values
const pinValue = ref('');
const otpValue = ref('');

// ==================== Wizard Steps ====================
const wizardSteps = [
    { label: 'Details', icon: 'pi pi-file-edit' },
    { label: 'Review', icon: 'pi pi-eye' },
    { label: 'Verify', icon: 'pi pi-shield' },
    { label: 'Complete', icon: 'pi pi-check-circle' }
];

// ==================== Computed Properties ====================
const accountOptions = computed(() => {
    return props.accounts
        .filter(a => a.is_active)
        .map(account => ({
            label: `${account.account_type?.name || 'Account'} - ****${account.account_number.slice(-4)} (${formatCurrency(account.balance, account.currency)})`,
            value: account.id,
            account: account
        }));
});

const bankOptions = computed(() => {
    return props.banks.map(bank => ({
        label: bank.name,
        value: bank.id,
        code: bank.code
    }));
});

const selectedAccount = computed(() => {
    return props.accounts.find(a => a.id === formData.value.from_account_id);
});

const selectedBank = computed(() => {
    return props.banks.find(b => b.id === formData.value.bank_id);
});

const calculatedFee = computed(() => {
    if (!formData.value.amount) return 0;
    return props.fees.flat + (formData.value.amount * props.fees.percentage / 100);
});

const totalAmount = computed(() => {
    if (!formData.value.amount) return 0;
    return formData.value.amount + calculatedFee.value;
});

const isFormValid = computed(() => {
    return formData.value.from_account_id &&
           formData.value.bank_id &&
           formData.value.beneficiary_name &&
           formData.value.beneficiary_account &&
           formData.value.amount > 0 &&
           formData.value.amount <= props.transferLimits.perTransaction &&
           selectedAccount.value &&
           (totalAmount.value * 100) <= selectedAccount.value.balance;
});

const verificationStepTitle = computed(() => {
    return verificationStep.value === 'pin' ? 'Enter Transaction PIN' : 'Email Verification';
});

const verificationProgress = computed(() => {
    const steps = props.verificationConfig.requiresOtp ? ['pin', 'otp'] : ['pin'];
    const currentIndex = steps.indexOf(verificationStep.value);
    return ((currentIndex + 1) / steps.length) * 100;
});

// ==================== Methods ====================
const goToNextStep = () => {
    if (currentStep.value === 0 && isFormValid.value) {
        currentStep.value = 1; // Go to review
    } else if (currentStep.value === 1) {
        initiateTransfer();
    }
};

const goToPrevStep = () => {
    if (currentStep.value > 0 && !showVerificationModal.value) {
        currentStep.value--;
    }
};

const initiateTransfer = () => {
    isProcessing.value = true;
    formErrors.value = {};

    router.post(route('transfers.domestic.initiate'), {
        ...formData.value,
        amount: Math.round((formData.value.amount || 0) * 100) // Convert dollars to cents
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            const flash = response.props.flash;
            if (flash?.transfer) {
                activeTransfer.value = flash.transfer;
                currentStep.value = 2;
                showVerificationModal.value = true;
                verificationStep.value = 'pin';
                toast.info('Please complete the verification steps', 'Verification Required');
            }
        },
        onError: (errors) => {
            formErrors.value = errors;
            toast.error(errors.general || 'Failed to initiate transfer', 'Error');
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const handlePinSubmit = (pin) => {
    if (pin.length !== 6) {
        toast.error('Please enter your 6-digit PIN', 'Invalid PIN');
        return;
    }

    isProcessing.value = true;

    router.post(route('transfers.domestic.verify-pin', { domesticTransfer: activeTransfer.value.uuid }), {
        pin: pin
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            const flash = response.props.flash;

            // Check if transfer completed directly (no OTP required)
            if (flash?.transfer) {
                transferResult.value = flash.transfer;
                transferComplete.value = true;
                currentStep.value = 3;
                showVerificationModal.value = false;
                toast.success('Your domestic transfer has been submitted for processing', 'Transfer Submitted');
            } else if (flash?.nextStep === 'otp') {
                verificationStep.value = 'otp';
                requestOtp();
                toast.success('Please verify with OTP', 'PIN Verified');
            }
        },
        onError: (errors) => {
            toast.error(errors.pin || 'Invalid PIN', 'Verification Failed');
            pinValue.value = '';
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const requestOtp = () => {
    isProcessing.value = true;

    router.post(route('transfers.domestic.request-otp', { domesticTransfer: activeTransfer.value.uuid }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('A verification code has been sent to your email', 'OTP Sent');
        },
        onError: (errors) => {
            toast.error(errors.general || 'Failed to send OTP', 'Error');
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const handleOtpSubmit = (otp) => {
    if (otp.length !== 6) {
        toast.error('Please enter the 6-digit code', 'Invalid OTP');
        return;
    }

    isProcessing.value = true;

    router.post(route('transfers.domestic.verify-otp', { domesticTransfer: activeTransfer.value.uuid }), {
        otp: otp
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            const flash = response.props.flash;
            if (flash?.transfer) {
                transferResult.value = flash.transfer;
                transferComplete.value = true;
                currentStep.value = 3;
                showVerificationModal.value = false;
                toast.success('Your domestic transfer has been submitted for processing', 'Transfer Submitted');
            }
        },
        onError: (errors) => {
            toast.error(errors.otp || 'Invalid OTP', 'Verification Failed');
            otpValue.value = '';
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const startNewTransfer = () => {
    formData.value = {
        from_account_id: null,
        bank_id: null,
        beneficiary_name: '',
        beneficiary_account: '',
        amount: null,
        description: ''
    };
    currentStep.value = 0;
    transferComplete.value = false;
    transferResult.value = null;
    activeTransfer.value = null;
    pinValue.value = '';
    otpValue.value = '';
};

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success && !showVerificationModal.value) {
        toast.success(flash.success, 'Success');
    }
}, { immediate: true });
</script>

<template>
    <Head title="Domestic Transfer" />

    <DashboardLayout title="Domestic Transfer">
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
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600">
                        <i class="text-xl text-white pi pi-building"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Domestic Transfer
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Transfer money to other local banks
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-2xl mx-auto">
            <!-- Step 1: Transfer Details -->
            <Card v-if="currentStep === 0" class="shadow-lg">
                <template #title>
                    <div class="mb-4">
                        <Steps :model="wizardSteps" :activeStep="currentStep" :readonly="true" class="custom-steps" />
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
                                    @click="formData.from_account_id = account.id"
                                    class="p-4 transition-all duration-300 ease-in-out border-2 rounded-lg cursor-pointer"
                                    :class="[
                                        formData.from_account_id === account.id
                                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20 shadow-md shadow-green-500/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center w-10 h-10 transition-all duration-300 rounded-full"
                                                :class="[
                                                    formData.from_account_id === account.id
                                                        ? 'bg-green-500 text-white'
                                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                                ]"
                                            >
                                                <i
                                                    class="pi transition-all duration-300"
                                                    :class="[
                                                        formData.from_account_id === account.id
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
                                                    formData.from_account_id === account.id
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
                            <small v-if="formErrors.from_account_id" class="mt-2 text-red-500 block">
                                {{ formErrors.from_account_id }}
                            </small>
                        </div>

                        <Divider />

                        <!-- Destination Bank -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Destination Bank <span class="text-red-500">*</span>
                            </label>
                            <Dropdown
                                v-model="formData.bank_id"
                                :options="bankOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Select destination bank"
                                class="w-full"
                                filter
                                :class="{ 'p-invalid': formErrors.bank_id }"
                            />
                            <small v-if="formErrors.bank_id" class="text-red-500">
                                {{ formErrors.bank_id }}
                            </small>
                        </div>

                        <!-- Beneficiary Name -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Beneficiary Name <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                v-model="formData.beneficiary_name"
                                placeholder="Full name as registered with bank"
                                class="w-full"
                                :class="{ 'p-invalid': formErrors.beneficiary_name }"
                            />
                            <small v-if="formErrors.beneficiary_name" class="text-red-500">
                                {{ formErrors.beneficiary_name }}
                            </small>
                        </div>

                        <!-- Beneficiary Account -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Account Number <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                v-model="formData.beneficiary_account"
                                placeholder="Beneficiary's account number"
                                class="w-full"
                                :class="{ 'p-invalid': formErrors.beneficiary_account }"
                            />
                            <small v-if="formErrors.beneficiary_account" class="text-red-500">
                                {{ formErrors.beneficiary_account }}
                            </small>
                        </div>

                        <Divider />

                        <!-- Amount -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <InputNumber
                                v-model="formData.amount"
                                mode="currency"
                                :currency="selectedAccount?.currency || 'USD'"
                                locale="en-US"
                                placeholder="0.00"
                                class="w-full"
                                :min="1"
                                :max="transferLimits.perTransaction"
                                :class="{ 'p-invalid': formErrors.amount }"
                            />
                            <small v-if="formErrors.amount" class="text-red-500">
                                {{ formErrors.amount }}
                            </small>
                            <small v-else class="text-gray-500">
                                Max: {{ formatCurrency(transferLimits.perTransaction, selectedAccount?.currency || 'USD') }} per transaction
                            </small>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description (Optional)
                            </label>
                            <Textarea
                                v-model="formData.description"
                                placeholder="Add a note for this transfer"
                                :rows="2"
                                class="w-full"
                            />
                        </div>

                        <!-- Fee Summary -->
                        <Message v-if="formData.amount" severity="info" :closable="false">
                            <div class="space-y-3">
                                <!-- Transfer Amount Row -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Transfer Amount</span>
                                    <span class="font-semibold text-gray-900 dark:text-white ml-4 text-right">{{ formatCurrency(Math.round((formData.amount || 0) * 100), selectedAccount?.currency || 'USD') }}</span>
                                </div>

                                <!-- Transfer Fee Row -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Transfer Fee</span>
                                    <span class="text-gray-900 dark:text-white ml-4 text-right">{{ formatCurrency(Math.round((calculatedFee || 0) * 100), selectedAccount?.currency || 'USD') }}</span>
                                </div>

                                <!-- Divider -->
                                <Divider class="my-2" />

                                <!-- Total Amount Row -->
                                <div class="flex items-center justify-between w-full pt-1">
                                    <span class="text-gray-900 dark:text-white font-bold text-base">Total Amount</span>
                                    <span class="text-primary-600 dark:text-primary-400 font-bold text-base ml-4 text-right">{{ formatCurrency(Math.round((totalAmount || 0) * 100), selectedAccount?.currency || 'USD') }}</span>
                                </div>
                            </div>
                        </Message>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-4">
                            <Link href="/transfers">
                                <Button label="Cancel" severity="secondary" outlined />
                            </Link>
                            <Button
                                label="Continue to Review"
                                icon="pi pi-arrow-right"
                                iconPos="right"
                                :disabled="!isFormValid"
                                @click="goToNextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 2: Review -->
            <Card v-if="currentStep === 1" class="shadow-lg">
                <template #title>
                    <div class="mb-4">
                        <Steps :model="wizardSteps" :activeStep="currentStep" :readonly="true" class="custom-steps" />
                    </div>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-eye text-primary-500"></i>
                        Review Transfer
                    </div>
                </template>
                <template #content>
                    <div class="space-y-6">
                        <!-- Transfer Summary -->
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <h4 class="mb-4 font-semibold text-gray-800 dark:text-white">Transfer Summary</h4>

                            <div class="space-y-3">
                                <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">From Account</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        ****{{ selectedAccount?.account_number?.slice(-4) }}
                                    </span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">To Bank</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ selectedBank?.name }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">Beneficiary</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formData.beneficiary_name }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">Account Number</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formData.beneficiary_account }}</span>
                                </div>
                                <div v-if="formData.description" class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">Description</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formData.description }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Details -->
                        <div class="p-4 border-2 border-purple-200 rounded-lg bg-purple-50 dark:bg-purple-900/20 dark:border-purple-800">
                            <div class="space-y-3">
                                <!-- Transfer Amount Row -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Transfer Amount</span>
                                    <span class="font-semibold text-gray-900 dark:text-white ml-4 text-right">{{ formatCurrency(Math.round((formData.amount || 0) * 100), selectedAccount?.currency || 'USD') }}</span>
                                </div>

                                <!-- Transfer Fee Row -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Transfer Fee</span>
                                    <span class="text-gray-900 dark:text-white ml-4 text-right">{{ formatCurrency(Math.round((calculatedFee || 0) * 100), selectedAccount?.currency || 'USD') }}</span>
                                </div>

                                <!-- Divider -->
                                <Divider class="my-2" />

                                <!-- Total Debit Row -->
                                <div class="flex items-center justify-between w-full pt-1">
                                    <span class="text-purple-600 dark:text-purple-400 font-bold text-base">Total Debit</span>
                                    <span class="text-purple-600 dark:text-purple-400 font-bold text-base ml-4 text-right">{{ formatCurrency(Math.round((totalAmount || 0) * 100), selectedAccount?.currency || 'USD') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Processing Time Notice -->
                        <Message severity="warn" :closable="false">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-clock"></i>
                                <span>Domestic transfers typically take <strong>1-3 business days</strong> to process.</span>
                            </div>
                        </Message>

                        <!-- Verification Steps Info -->
                        <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <h4 class="flex items-center gap-2 mb-2 font-semibold text-blue-800 dark:text-blue-300">
                                <i class="pi pi-shield"></i>
                                Verification Steps
                            </h4>
                            <p class="text-sm text-blue-700 dark:text-blue-400">
                                You will need to complete the following verification steps:
                            </p>
                            <ul class="mt-2 ml-4 text-sm text-blue-700 list-disc dark:text-blue-400">
                                <li>Transaction PIN</li>
                                <li v-if="verificationConfig.requiresOtp">Email OTP</li>
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-between gap-3 pt-4">
                            <Button
                                label="Back to Details"
                                icon="pi pi-arrow-left"
                                severity="secondary"
                                outlined
                                @click="goToPrevStep"
                            />
                            <Button
                                label="Proceed to Verify"
                                icon="pi pi-shield"
                                iconPos="right"
                                :loading="isProcessing"
                                @click="goToNextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 3: Verification (handled by modal) -->
            <Card v-if="currentStep === 2 && !showVerificationModal" class="shadow-lg">
                <template #title>
                    <div class="mb-4">
                        <Steps :model="wizardSteps" :activeStep="currentStep" :readonly="true" class="custom-steps" />
                    </div>
                </template>
                <template #content>
                    <div class="py-8 text-center">
                        <i class="mb-4 text-6xl pi pi-spin pi-spinner text-primary-500"></i>
                        <p class="text-gray-600 dark:text-gray-400">Processing verification...</p>
                    </div>
                </template>
            </Card>

            <!-- Step 4: Complete -->
            <Card v-if="currentStep === 3 && transferComplete" class="shadow-lg">
                <template #title">
                    <div class="mb-4">
                        <Steps :model="wizardSteps" :activeStep="currentStep" :readonly="true" class="custom-steps" />
                    </div>
                </template>
                <template #content>
                    <div class="py-8 text-center">
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-green-100 dark:bg-green-900">
                            <i class="text-4xl text-green-600 pi pi-check dark:text-green-400"></i>
                        </div>
                        <h2 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                            Transfer Submitted!
                        </h2>
                        <p class="mb-6 text-gray-600 dark:text-gray-400">
                            Your domestic transfer has been submitted for processing
                        </p>

                        <div v-if="transferResult" class="max-w-md p-4 mx-auto mb-6 text-left rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Reference</span>
                                    <span class="font-mono font-medium">{{ transferResult.reference }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Amount</span>
                                    <span class="font-medium">{{ formatCurrency(transferResult.amount, selectedAccount?.currency || 'USD') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Beneficiary</span>
                                    <span class="font-medium">{{ transferResult.beneficiary }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Status</span>
                                    <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                        Processing
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-3">
                            <Link href="/transactions">
                                <Button label="View Transactions" severity="secondary" outlined />
                            </Link>
                            <Button
                                label="New Transfer"
                                icon="pi pi-plus"
                                @click="startNewTransfer"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Verification Modal -->
        <Dialog
            v-model:visible="showVerificationModal"
            :header="verificationStepTitle"
            modal
            :closable="false"
            :style="{ width: '450px' }"
            class="verification-dialog"
        >
            <!-- Progress Bar -->
            <div class="mb-6">
                <ProgressBar :value="verificationProgress" :showValue="false" style="height: 6px" />
                <p class="mt-2 text-xs text-center text-gray-500">
                    Step {{ verificationStep === 'pin' ? 1 : 2 }} of {{ verificationConfig.requiresOtp ? 2 : 1 }}
                </p>
            </div>

            <!-- PIN Verification -->
            <div v-if="verificationStep === 'pin'" class="py-4">
                <div class="mb-6 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-primary-100 dark:bg-primary-900">
                        <i class="text-2xl pi pi-lock text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Enter your 6-digit transaction PIN to continue
                    </p>
                </div>
                <PinInput
                    v-model="pinValue"
                    :length="6"
                    :masked="true"
                    label=""
                    @complete="handlePinSubmit"
                />
                <div class="flex justify-center mt-6">
                    <Button
                        label="Verify PIN"
                        :loading="isProcessing"
                        :disabled="pinValue.length !== 6"
                        @click="handlePinSubmit(pinValue)"
                    />
                </div>
            </div>

            <!-- OTP Verification -->
            <div v-if="verificationStep === 'otp'" class="py-4">
                <OtpInput
                    v-model="otpValue"
                    :length="6"
                    :countdown="600"
                    :loading="isProcessing"
                    @complete="handleOtpSubmit"
                    @resend="requestOtp"
                />
                <div class="flex justify-center mt-6">
                    <Button
                        label="Verify & Complete Transfer"
                        :loading="isProcessing"
                        :disabled="otpValue.length !== 6"
                        @click="handleOtpSubmit(otpValue)"
                    />
                </div>
            </div>

            <!-- Cancel Option -->
            <div class="pt-4 mt-4 text-center border-t border-gray-200 dark:border-gray-700">
                <Link href="/transfers">
                    <Button
                        label="Cancel Transfer"
                        severity="secondary"
                        text
                        size="small"
                    />
                </Link>
            </div>
        </Dialog>
    </DashboardLayout>
</template>

<style scoped>
.custom-steps :deep(.p-steps-item) {
    flex: 1;
}

.custom-steps :deep(.p-steps-item .p-menuitem-link) {
    flex-direction: column;
    gap: 0.5rem;
}

.verification-dialog :deep(.p-dialog-header) {
    text-align: center;
    justify-content: center;
}
</style>
