<script setup>
/**
 * Wire Transfer Page
 * International wire transfers with multi-step verification wizard
 * Flow: Form → Review → PIN → Transfer Codes (if set) → OTP (if enabled) → Complete
 */
import { ref, computed, watch, onMounted } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
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
    transferLimits: {
        type: Object,
        default: () => ({
            daily: 100000,
            perTransaction: 50000
        })
    },
    fees: {
        type: Object,
        default: () => ({
            flat: 25,
            percentage: 0.5
        })
    },
    verificationConfig: {
        type: Object,
        default: () => ({
            requiresOtp: true,
            hasTransferCodes: false,
            requiredCodes: []
        })
    },
    beneficiaryFields: {
        type: Array,
        default: () => []
    },
    countries: {
        type: Object,
        default: () => ({})
    }
});

const page = usePage();
const toast = useToast();
const { formatCurrency } = useCurrency();

// ==================== Form State ====================
const formData = ref({
    from_account_id: null,
    amount: null,
    remarks: ''
});

// Dynamic beneficiary fields data
const beneficiaryData = ref({});

// Initialize beneficiary data from fields
onMounted(() => {
    props.beneficiaryFields.forEach(field => {
        beneficiaryData.value[field.key] = '';
    });
});

// ==================== UI State ====================
const currentStep = ref(0);
const showVerificationModal = ref(false);
const verificationStep = ref('pin'); // pin, imf, tax, cot, otp
const isProcessing = ref(false);
const transferComplete = ref(false);
const transferResult = ref(null);
const activeTransfer = ref(null);
const formErrors = ref({});

// PIN/Code/OTP values
const pinValue = ref('');
const codeValue = ref('');
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

const selectedAccount = computed(() => {
    return props.accounts.find(a => a.id === formData.value.from_account_id);
});

// Use selected account's currency or default to USD
const currency = computed(() => {
    return selectedAccount.value?.currency || 'USD';
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
    // Check basic fields
    if (!formData.value.from_account_id || !formData.value.amount || formData.value.amount <= 0) {
        return false;
    }

    // Check amount limits
    if (formData.value.amount > props.transferLimits.perTransaction) {
        return false;
    }

    // Check balance
    if (selectedAccount.value && (totalAmount.value * 100) > selectedAccount.value.balance) {
        return false;
    }

    // Check required beneficiary fields
    for (const field of props.beneficiaryFields) {
        if (field.required && !beneficiaryData.value[field.key]) {
            return false;
        }
    }

    return true;
});

const verificationStepTitle = computed(() => {
    const titles = {
        pin: 'Enter Transaction PIN',
        imf: 'IMF Verification Code',
        tax: 'Tax Clearance Code',
        cot: 'COT Verification Code',
        otp: 'Email Verification'
    };
    return titles[verificationStep.value] || 'Verification';
});

const verificationStepDescription = computed(() => {
    const descriptions = {
        pin: 'Enter your 6-digit transaction PIN to continue',
        imf: 'Enter your International Monetary Fund verification code',
        tax: 'Enter your Tax clearance verification code',
        cot: 'Enter your Cost of Transfer verification code',
        otp: 'Enter the 6-digit code sent to your email'
    };
    return descriptions[verificationStep.value] || '';
});

const verificationProgress = computed(() => {
    const steps = getVerificationSteps();
    const currentIndex = steps.indexOf(verificationStep.value);
    return ((currentIndex + 1) / steps.length) * 100;
});

// ==================== Methods ====================
const getVerificationSteps = () => {
    const steps = ['pin'];

    // Add transfer codes if user has them
    if (props.verificationConfig.hasTransferCodes) {
        props.verificationConfig.requiredCodes.forEach(code => {
            steps.push(code);
        });
    }

    // Add OTP if required
    if (props.verificationConfig.requiresOtp) {
        steps.push('otp');
    }

    return steps;
};

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

    router.post(route('transfers.wire.initiate'), {
        ...formData.value,
        ...beneficiaryData.value // Include all dynamic beneficiary fields
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            const flash = response.props.flash;
            if (flash?.transfer) {
                activeTransfer.value = flash.transfer;
                currentStep.value = 2;
                showVerificationModal.value = true;
                verificationStep.value = 'pin';
                toast.add({
                    severity: 'info',
                    summary: 'Verification Required',
                    detail: 'Please complete the verification steps',
                    life: 3000
                });
            }
        },
        onError: (errors) => {
            formErrors.value = errors;
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: errors.general || 'Failed to initiate transfer',
                life: 5000
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const handlePinSubmit = (pin) => {
    if (pin.length !== 6) {
        toast.add({
            severity: 'error',
            summary: 'Invalid PIN',
            detail: 'Please enter your 6-digit PIN',
            life: 3000
        });
        return;
    }

    isProcessing.value = true;

    router.post(route('transfers.wire.verify-pin', { wireTransfer: activeTransfer.value.uuid }), {
        pin: pin
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            const flash = response.props.flash;
            if (flash?.nextStep) {
                moveToNextVerificationStep(flash.nextStep);
            }
            toast.add({
                severity: 'success',
                summary: 'PIN Verified',
                detail: 'Your PIN has been verified',
                life: 2000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Verification Failed',
                detail: errors.pin || 'Invalid PIN',
                life: 3000
            });
            pinValue.value = '';
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const handleCodeSubmit = (code, codeType) => {
    if (!code) {
        toast.add({
            severity: 'error',
            summary: 'Invalid Code',
            detail: `Please enter your ${codeType.toUpperCase()} code`,
            life: 3000
        });
        return;
    }

    isProcessing.value = true;

    router.post(route('transfers.wire.verify-code', { wireTransfer: activeTransfer.value.uuid }), {
        code_type: codeType,
        code: code
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            const flash = response.props.flash;
            if (flash?.nextStep) {
                moveToNextVerificationStep(flash.nextStep);
            }
            toast.add({
                severity: 'success',
                summary: 'Code Verified',
                detail: `${codeType.toUpperCase()} code has been verified`,
                life: 2000
            });
            codeValue.value = '';
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Verification Failed',
                detail: errors.code || 'Invalid code',
                life: 3000
            });
            codeValue.value = '';
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const requestOtp = () => {
    isProcessing.value = true;

    router.post(route('transfers.wire.request-otp', { wireTransfer: activeTransfer.value.uuid }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'OTP Sent',
                detail: 'A verification code has been sent to your email',
                life: 5000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: errors.general || 'Failed to send OTP',
                life: 3000
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const handleOtpSubmit = (otp) => {
    if (otp.length !== 6) {
        toast.add({
            severity: 'error',
            summary: 'Invalid OTP',
            detail: 'Please enter the 6-digit code',
            life: 3000
        });
        return;
    }

    isProcessing.value = true;

    router.post(route('transfers.wire.verify-otp', { wireTransfer: activeTransfer.value.uuid }), {
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
                toast.add({
                    severity: 'success',
                    summary: 'Transfer Submitted',
                    detail: 'Your wire transfer has been submitted for processing',
                    life: 5000
                });
            }
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Verification Failed',
                detail: errors.otp || 'Invalid OTP',
                life: 3000
            });
            otpValue.value = '';
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const moveToNextVerificationStep = (nextStep) => {
    verificationStep.value = nextStep;

    // If moving to OTP step, automatically request OTP
    if (nextStep === 'otp') {
        requestOtp();
    }

    // If completed, show success
    if (nextStep === 'completed') {
        transferComplete.value = true;
        currentStep.value = 3;
        showVerificationModal.value = false;
    }
};

const startNewTransfer = () => {
    formData.value = {
        from_account_id: null,
        beneficiary_name: '',
        beneficiary_account: '',
        beneficiary_bank_name: '',
        beneficiary_bank_address: '',
        swift_code: '',
        routing_number: '',
        amount: null,
        currency: 'USD',
        purpose: ''
    };
    currentStep.value = 0;
    transferComplete.value = false;
    transferResult.value = null;
    activeTransfer.value = null;
    pinValue.value = '';
    codeValue.value = '';
    otpValue.value = '';
};

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success && !showVerificationModal.value) {
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: flash.success,
            life: 3000
        });
    }
}, { immediate: true });
</script>

<template>
    <Head title="Wire Transfer" />

    <DashboardLayout title="Wire Transfer">
        <div class="max-w-3xl mx-auto">
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
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600">
                        <i class="text-xl text-white pi pi-globe"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Wire Transfer
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Send money internationally via SWIFT network
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-3xl mx-auto">
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

                        <!-- Beneficiary Section - Dynamic Fields -->
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Beneficiary Information</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div v-for="field in props.beneficiaryFields" :key="field.key" :class="{'md:col-span-2': field.type === 'textarea'}">
                                <!-- Text Input -->
                                <div v-if="field.type === 'text'">
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ field.label }}
                                        <span v-if="field.required" class="text-red-500">*</span>
                                    </label>
                                    <InputText
                                        v-model="beneficiaryData[field.key]"
                                        :placeholder="field.placeholder || `Enter ${field.label.toLowerCase()}`"
                                        class="w-full"
                                        :class="{ 'p-invalid': formErrors[field.key] }"
                                    />
                                    <small v-if="field.helperText" class="text-gray-500">{{ field.helperText }}</small>
                                    <small v-if="formErrors[field.key]" class="block mt-1 text-red-500">
                                        {{ formErrors[field.key] }}
                                    </small>
                                </div>

                                <!-- Textarea -->
                                <div v-else-if="field.type === 'textarea'">
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ field.label }}
                                        <span v-if="field.required" class="text-red-500">*</span>
                                    </label>
                                    <Textarea
                                        v-model="beneficiaryData[field.key]"
                                        :placeholder="field.placeholder || `Enter ${field.label.toLowerCase()}`"
                                        :rows="3"
                                        class="w-full"
                                        :class="{ 'p-invalid': formErrors[field.key] }"
                                    />
                                    <small v-if="field.helperText" class="text-gray-500">{{ field.helperText }}</small>
                                    <small v-if="formErrors[field.key]" class="block mt-1 text-red-500">
                                        {{ formErrors[field.key] }}
                                    </small>
                                </div>

                                <!-- Select Dropdown -->
                                <div v-else-if="field.type === 'select'">
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ field.label }}
                                        <span v-if="field.required" class="text-red-500">*</span>
                                    </label>
                                    <Dropdown
                                        v-model="beneficiaryData[field.key]"
                                        :options="Object.entries(field.options || {}).map(([value, label]) => ({ label, value }))"
                                        optionLabel="label"
                                        optionValue="value"
                                        :placeholder="field.placeholder || `Select ${field.label.toLowerCase()}`"
                                        class="w-full"
                                        :class="{ 'p-invalid': formErrors[field.key] }"
                                    />
                                    <small v-if="field.helperText" class="text-gray-500">{{ field.helperText }}</small>
                                    <small v-if="formErrors[field.key]" class="block mt-1 text-red-500">
                                        {{ formErrors[field.key] }}
                                    </small>
                                </div>

                                <!-- Country Selector -->
                                <div v-else-if="field.type === 'country'">
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ field.label }}
                                        <span v-if="field.required" class="text-red-500">*</span>
                                    </label>
                                    <Dropdown
                                        v-model="beneficiaryData[field.key]"
                                        :options="Object.entries(props.countries || {}).map(([value, label]) => ({ label, value }))"
                                        optionLabel="label"
                                        optionValue="value"
                                        :placeholder="field.placeholder || 'Select country'"
                                        filter
                                        class="w-full"
                                        :class="{ 'p-invalid': formErrors[field.key] }"
                                    />
                                    <small v-if="field.helperText" class="text-gray-500">{{ field.helperText }}</small>
                                    <small v-if="formErrors[field.key]" class="block mt-1 text-red-500">
                                        {{ formErrors[field.key] }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <Divider />

                        <!-- Amount Section -->
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Transfer Amount</h3>

                        <!-- Amount -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <InputNumber
                                v-model="formData.amount"
                                mode="currency"
                                :currency="currency"
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
                                Max: {{ formatCurrency(transferLimits.perTransaction, currency) }} per transaction
                            </small>
                            <small v-if="selectedAccount" class="block mt-1 text-gray-600 dark:text-gray-400">
                                Currency: {{ currency }} (from selected account)
                            </small>
                        </div>

                        <!-- Remarks -->
                        <div class="mt-4">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Remarks
                            </label>
                            <Textarea
                                v-model="formData.remarks"
                                placeholder="Provide details about this transfer"
                                :rows="3"
                                class="w-full"
                                :class="{ 'p-invalid': formErrors.remarks }"
                            />
                            <small class="text-gray-500">Optional: Add any additional notes or purpose for this transfer</small>
                        </div>

                        <!-- Fee Summary -->
                        <Message v-if="formData.amount" severity="info" :closable="false" class="mt-4">
                            <div class="space-y-3">
                                <!-- Transfer Amount Row -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Transfer Amount</span>
                                    <span class="font-semibold text-gray-900 dark:text-white ml-4 text-right">{{ formatCurrency(formData.amount, currency) }}</span>
                                </div>

                                <!-- Transfer Fee Row -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Transfer Fee</span>
                                    <span class="text-gray-900 dark:text-white ml-4 text-right">{{ formatCurrency(calculatedFee, currency) }}</span>
                                </div>

                                <!-- Divider -->
                                <Divider class="my-2" />

                                <!-- Total Amount Row -->
                                <div class="flex items-center justify-between w-full pt-1">
                                    <span class="text-gray-900 dark:text-white font-bold text-base">Total Amount</span>
                                    <span class="text-primary-600 dark:text-primary-400 font-bold text-base ml-4 text-right">{{ formatCurrency(totalAmount, currency) }}</span>
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

                                <!-- Dynamic Beneficiary Fields -->
                                <div
                                    v-for="field in props.beneficiaryFields.filter(f => beneficiaryData[f.key])"
                                    :key="field.key"
                                    class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <span class="text-gray-600 dark:text-gray-400">{{ field.label }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        <template v-if="field.type === 'country'">
                                            {{ props.countries[beneficiaryData[field.key]] || beneficiaryData[field.key] }}
                                        </template>
                                        <template v-else>
                                            {{ beneficiaryData[field.key] }}
                                        </template>
                                    </span>
                                </div>

                                <!-- Amount -->
                                <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">Amount</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(formData.amount, currency) }}</span>
                                </div>

                                <!-- Remarks -->
                                <div v-if="formData.remarks" class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">Remarks</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formData.remarks }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Details -->
                        <div class="p-4 border-2 border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/20 dark:border-orange-800">
                            <div class="space-y-3">
                                <!-- Transfer Amount Row -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Transfer Amount</span>
                                    <span class="font-semibold text-gray-900 dark:text-white ml-4 text-right">{{ formatCurrency(formData.amount, currency) }}</span>
                                </div>

                                <!-- Wire Transfer Fee Row -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Wire Transfer Fee</span>
                                    <span class="text-gray-900 dark:text-white ml-4 text-right">{{ formatCurrency(calculatedFee, currency) }}</span>
                                </div>

                                <!-- Divider -->
                                <Divider class="my-2" />

                                <!-- Total Debit Row -->
                                <div class="flex items-center justify-between w-full pt-1">
                                    <span class="text-orange-600 dark:text-orange-400 font-bold text-base">Total Debit</span>
                                    <span class="text-orange-600 dark:text-orange-400 font-bold text-base ml-4 text-right">{{ formatCurrency(totalAmount, currency) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Processing Time Notice -->
                        <Message severity="warn" :closable="false">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-clock"></i>
                                <span>Wire transfers typically take <strong>3-5 business days</strong> to process.</span>
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
                                <li v-if="verificationConfig.requiredCodes.includes('imf')">IMF Verification Code</li>
                                <li v-if="verificationConfig.requiredCodes.includes('tax')">Tax Clearance Code</li>
                                <li v-if="verificationConfig.requiredCodes.includes('cot')">COT Verification Code</li>
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
                <template #title>
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
                            Your wire transfer has been submitted for processing
                        </p>

                        <div v-if="transferResult" class="max-w-md p-4 mx-auto mb-6 text-left rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Reference</span>
                                    <span class="font-mono font-medium">{{ transferResult.reference }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Amount</span>
                                    <span class="font-medium">{{ formatCurrency(transferResult.amount, currency) }}</span>
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
                    Step {{ getVerificationSteps().indexOf(verificationStep) + 1 }} of {{ getVerificationSteps().length }}
                </p>
            </div>

            <!-- PIN Verification -->
            <div v-if="verificationStep === 'pin'" class="py-4">
                <div class="mb-6 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-primary-100 dark:bg-primary-900">
                        <i class="text-2xl pi pi-lock text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ verificationStepDescription }}
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

            <!-- Transfer Code Verification (IMF, Tax, COT) -->
            <div v-if="['imf', 'tax', 'cot'].includes(verificationStep)" class="py-4">
                <div class="mb-6 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-orange-100 dark:bg-orange-900">
                        <i class="text-2xl pi pi-key text-orange-600 dark:text-orange-400"></i>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ verificationStepDescription }}
                    </p>
                </div>
                <div class="max-w-xs mx-auto">
                    <InputText
                        v-model="codeValue"
                        placeholder="Enter verification code"
                        class="w-full text-center"
                        @keyup.enter="handleCodeSubmit(codeValue, verificationStep)"
                    />
                </div>
                <div class="flex justify-center mt-6">
                    <Button
                        :label="`Verify ${verificationStep.toUpperCase()} Code`"
                        :loading="isProcessing"
                        :disabled="!codeValue"
                        @click="handleCodeSubmit(codeValue, verificationStep)"
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
