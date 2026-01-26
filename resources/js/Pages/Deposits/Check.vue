<script setup>
/**
 * Check Deposit Page
 * Upload check images for mobile deposit
 */
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import FileUpload from 'primevue/fileupload';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    depositLimits: {
        type: Object,
        default: () => ({
            daily: 50000,
            perTransaction: 25000,
            holdDays: 5
        })
    },
    todaysTotal: {
        type: Number,
        default: 0
    }
});

const toast = useToast();
const { formatCurrency } = useCurrency();
const page = usePage();
const userCurrency = computed(() => page.props.auth?.currency || 'USD');

const form = useForm({
    bank_account_id: null,
    check_number: '',
    check_front_image: null,
    check_back_image: null,
    amount: null
});

const depositComplete = ref(false);
const isProcessing = ref(false);
const depositResult = ref(null);
const frontImagePreview = ref(null);
const backImagePreview = ref(null);
const currentStep = ref(0);

const steps = [
    { label: 'Select Account' },
    { label: 'Check Details' },
    { label: 'Upload Images' },
    { label: 'Review & Submit' },
    { label: 'Complete' }
];

const selectedAccount = computed(() => {
    return props.accounts.find(a => a.id === form.bank_account_id);
});

const remainingDaily = computed(() => {
    return (props.depositLimits.daily - props.todaysTotal) / 100;
});

const maxAmount = computed(() => {
    return Math.min(
        props.depositLimits.perTransaction / 100,
        remainingDaily.value
    );
});

const holdUntilDate = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() + props.depositLimits.holdDays);
    return date.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
});

const isFormValid = computed(() => {
    return form.bank_account_id &&
        form.check_number &&
        form.check_front_image &&
        form.check_back_image &&
        form.amount &&
        form.amount > 0 &&
        form.amount <= maxAmount.value;
});

const nextStep = () => {
    if (currentStep.value < 3 && validateStep()) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

const validateStep = () => {
    if (currentStep.value === 0) {
        return !!form.bank_account_id;
    } else if (currentStep.value === 1) {
        return form.check_number && form.amount && form.amount > 0 && form.amount <= maxAmount.value;
    } else if (currentStep.value === 2) {
        return form.check_front_image && form.check_back_image;
    }
    return true;
};

const handleFrontImageSelect = (event) => {
    const file = event.files[0];
    if (file) {
        form.check_front_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            frontImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleBackImageSelect = (event) => {
    const file = event.files[0];
    if (file) {
        form.check_back_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            backImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeFrontImage = () => {
    form.check_front_image = null;
    frontImagePreview.value = null;
};

const removeBackImage = () => {
    form.check_back_image = null;
    backImagePreview.value = null;
};

const submitDeposit = () => {
    if (!isFormValid.value) {
        toast.add({
            severity: 'error',
            summary: 'Invalid Check Deposit',
            detail: 'Please verify all check details and images',
            life: 3000
        });
        return;
    }

    isProcessing.value = true;

    form.transform((data) => ({
        ...data,
        amount: Math.round((data.amount || 0) * 100), // Convert dollars to cents
    })).post(route('deposits.check.submit'), {
        preserveScroll: true,
        onSuccess: (page) => {
            depositComplete.value = true;
            depositResult.value = {
                ...form.data(),
                holdUntilDate: holdUntilDate.value
            };
            currentStep.value = 4;
            toast.add({
                severity: 'success',
                summary: 'Check Deposit Submitted',
                detail: 'Your check will be processed and funds available after hold period',
                life: 5000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Deposit Failed',
                detail: errors.check_front_image || errors.check_back_image || errors.amount || 'Unable to submit deposit',
                life: 5000
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const startNewDeposit = () => {
    form.reset();
    currentStep.value = 0;
    depositComplete.value = false;
    depositResult.value = null;
    frontImagePreview.value = null;
    backImagePreview.value = null;
};
</script>

<template>
    <Head title="Check Deposit" />

    <DashboardLayout title="Check Deposit">
        <div class="max-w-2xl mx-auto">
            <Link
                href="/deposits"
                class="inline-flex items-center gap-2 mb-6 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <i class="pi pi-arrow-left"></i>
                Back to Deposits
            </Link>

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Check Deposit
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Deposit checks by uploading images
                </p>
            </div>
        </div>

        <!-- Deposit Form -->
        <div class="max-w-2xl mx-auto">
            <!-- Steps -->
            <Card v-if="!depositComplete" class="mb-6">
                <template #content>
                    <div class="flex items-center justify-between">
                        <div
                            v-for="(step, index) in steps"
                            :key="index"
                            class="flex items-center"
                            :class="{ 'flex-1': index < steps.length - 1 }"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold transition-all"
                                :class="[
                                    index === currentStep
                                        ? 'bg-blue-500 text-white'
                                        : index < currentStep
                                        ? 'bg-green-500 text-white'
                                        : 'bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300'
                                ]"
                            >
                                {{ index + 1 }}
                            </div>
                            <div
                                v-if="index < steps.length - 1"
                                class="flex-1 h-1 mx-2 transition-all"
                                :class="[
                                    index < currentStep
                                        ? 'bg-green-500'
                                        : 'bg-gray-300 dark:bg-gray-600'
                                ]"
                            ></div>
                        </div>
                    </div>
                    <p class="mt-3 text-sm font-medium text-center text-gray-600 dark:text-gray-400">
                        {{ steps[currentStep].label }}
                    </p>
                </template>
            </Card>

            <!-- Step 1: Select Account -->
            <Card v-if="currentStep === 0 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-wallet text-primary-500"></i>
                        Select Deposit Account
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div
                            v-for="account in props.accounts"
                            :key="account.id"
                            @click="form.bank_account_id = account.id"
                            class="p-4 transition-all duration-300 border-2 rounded-lg cursor-pointer"
                            :class="[
                                form.bank_account_id === account.id
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 rounded-full transition-all"
                                        :class="[
                                            form.bank_account_id === account.id
                                                ? 'bg-green-500 text-white'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-500'
                                        ]"
                                    >
                                        <i
                                            class="pi"
                                            :class="[
                                                form.bank_account_id === account.id
                                                    ? 'pi-check'
                                                    : 'pi-wallet'
                                            ]"
                                        ></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ account.account_type?.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            ****{{ account.account_number.slice(-4) }}
                                        </p>
                                    </div>
                                </div>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(account.balance, account.currency) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link href="/deposits">
                                <Button label="Cancel" outlined />
                            </Link>
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                :disabled="!form.bank_account_id"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 2: Check Details -->
            <Card v-if="currentStep === 1 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-id-card text-primary-500"></i>
                        Check Details
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Check Number <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                v-model="form.check_number"
                                type="text"
                                placeholder="e.g., 123456"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Deposit Amount <span class="text-red-500">*</span>
                            </label>
                            <InputNumber
                                v-model="form.amount"
                                mode="currency"
                                :currency="userCurrency"
                                locale="en-US"
                                placeholder="0.00"
                                class="w-full"
                                :min="1"
                                :max="maxAmount"
                            />
                            <div class="flex justify-between mt-2 text-xs text-gray-500">
                                <span>Max per check: {{ formatCurrency(depositLimits.perTransaction, userCurrency) }}</span>
                                <span>Available today: {{ formatCurrency(remainingDaily * 100, userCurrency) }}</span>
                            </div>
                        </div>

                        <Message v-if="remainingDaily <= 0" severity="error" :closable="false">
                            <p class="text-sm">Daily check deposit limit reached</p>
                        </Message>

                        <Message severity="info" :closable="false">
                            <p class="text-sm"><strong>Account:</strong> {{ selectedAccount?.account_type?.name }}</p>
                            <p class="text-sm mt-1"><strong>Funds Available:</strong> {{ holdUntilDate }}</p>
                        </Message>

                        <div class="flex justify-between gap-3 pt-4">
                            <Button label="Back" icon="pi pi-arrow-left" outlined @click="prevStep" />
                            <Button
                                label="Continue"
                                icon="pi pi-arrow-right"
                                :disabled="!validateStep()"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 3: Upload Images -->
            <Card v-if="currentStep === 2 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-image text-primary-500"></i>
                        Upload Check Images
                    </div>
                </template>
                <template #content>
                    <div class="space-y-6">
                        <Message severity="info" :closable="false">
                            <p class="text-sm"><strong>Upload Requirements:</strong></p>
                            <ul class="mt-2 text-sm space-y-1 list-disc list-inside">
                                <li>Maximum 5MB per image</li>
                                <li>Clear, legible images (JPG, PNG)</li>
                                <li>Front: Account holder name visible</li>
                                <li>Back: Endorsement signature required</li>
                            </ul>
                        </Message>

                        <!-- Front Image -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Check Front <span class="text-red-500">*</span>
                            </label>

                            <div v-if="!frontImagePreview" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer">
                                <FileUpload
                                    name="check_front"
                                    @select="handleFrontImageSelect"
                                    accept="image/*"
                                    :auto="false"
                                    :show-upload-button="false"
                                    :show-cancel-button="false"
                                >
                                    <template #content>
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="text-4xl text-gray-400 pi pi-cloud-upload"></i>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Click to upload check front image</p>
                                        </div>
                                    </template>
                                </FileUpload>
                            </div>

                            <div v-else class="relative rounded-lg overflow-hidden">
                                <img :src="frontImagePreview" alt="Check Front" class="w-full h-64 object-cover">
                                <Button
                                    icon="pi pi-times"
                                    severity="danger"
                                    text
                                    class="absolute top-2 right-2"
                                    @click="removeFrontImage"
                                />
                            </div>
                        </div>

                        <!-- Back Image -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Check Back (Endorsement) <span class="text-red-500">*</span>
                            </label>

                            <div v-if="!backImagePreview" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer">
                                <FileUpload
                                    name="check_back"
                                    @select="handleBackImageSelect"
                                    accept="image/*"
                                    :auto="false"
                                    :show-upload-button="false"
                                    :show-cancel-button="false"
                                >
                                    <template #content>
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="text-4xl text-gray-400 pi pi-cloud-upload"></i>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Click to upload check back image</p>
                                        </div>
                                    </template>
                                </FileUpload>
                            </div>

                            <div v-else class="relative rounded-lg overflow-hidden">
                                <img :src="backImagePreview" alt="Check Back" class="w-full h-64 object-cover">
                                <Button
                                    icon="pi pi-times"
                                    severity="danger"
                                    text
                                    class="absolute top-2 right-2"
                                    @click="removeBackImage"
                                />
                            </div>
                        </div>

                        <div class="flex justify-between gap-3 pt-4">
                            <Button label="Back" icon="pi pi-arrow-left" outlined @click="prevStep" />
                            <Button
                                label="Review Deposit"
                                icon="pi pi-arrow-right"
                                :disabled="!validateStep()"
                                @click="nextStep"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 4: Review & Submit -->
            <Card v-if="currentStep === 3 && !depositComplete" class="shadow-lg">
                <template #title>
                    <div class="flex items-center gap-2 text-lg">
                        <i class="pi pi-check-circle text-primary-500"></i>
                        Review Check Deposit
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <p class="text-sm text-blue-900 dark:text-blue-200">
                                <i class="mr-2 pi pi-info-circle"></i>
                                Funds will be available on <strong>{{ holdUntilDate }}</strong> ({{ depositLimits.holdDays }}-day hold)
                            </p>
                        </div>

                        <div class="grid gap-4">
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Account</p>
                                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                                    {{ selectedAccount?.account_type?.name }}
                                </p>
                            </div>

                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Check #</p>
                                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                                    {{ form.check_number }}
                                </p>
                            </div>

                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Deposit Amount</p>
                                <p class="mt-1 text-lg font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(form.amount * 100, userCurrency) }}
                                </p>
                            </div>
                        </div>

                        <Message severity="warning" :closable="false">
                            <p class="text-sm">By submitting, you certify that the check images are clear and the deposit amount is correct.</p>
                        </Message>

                        <div class="flex justify-between gap-3 pt-4">
                            <Button label="Back" icon="pi pi-arrow-left" outlined @click="prevStep" />
                            <Button
                                label="Submit Deposit"
                                icon="pi pi-send"
                                :loading="isProcessing"
                                @click="submitDeposit"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step 5: Complete -->
            <Card v-if="depositComplete" class="shadow-lg">
                <template #content>
                    <div class="py-8 text-center">
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30">
                            <i class="text-4xl text-green-600 pi pi-check dark:text-green-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Check Deposit Submitted
                        </h3>
                        <p class="mt-2 text-gray-500">
                            Your check has been received and is pending verification
                        </p>

                        <div class="p-4 mt-6 text-left rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Check #</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ form.check_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Amount</span>
                                    <span class="font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(form.amount * 100, userCurrency) }}
                                    </span>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between">
                                    <span class="text-gray-500">Funds Available</span>
                                    <span class="font-semibold text-green-600 dark:text-green-400">{{ holdUntilDate }}</span>
                                </div>
                            </div>
                        </div>

                        <Message severity="info" :closable="false" class="mt-4">
                            <p class="text-sm">We'll verify your check images and notify you via email when processing is complete.</p>
                        </Message>

                        <div class="flex justify-center gap-3 mt-6">
                            <Button
                                label="New Deposit"
                                icon="pi pi-plus"
                                outlined
                                @click="startNewDeposit"
                            />
                            <Link href="/deposits">
                                <Button label="Back to Deposits" icon="pi pi-arrow-left" />
                            </Link>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
