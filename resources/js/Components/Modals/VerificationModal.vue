<script setup>
/**
 * VerificationModal Component
 * Multi-step verification for secure transactions
 */
import { ref, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Steps from 'primevue/steps';
import PinInput from '@/Components/Forms/PinInput.vue';
import OtpInput from '@/Components/Forms/OtpInput.vue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'standard', // 'standard' (PIN + OTP), 'wire' (PIN + IMF + Tax + COT + OTP)
    },
    title: {
        type: String,
        default: 'Verify Transaction',
    },
    transactionDetails: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:visible', 'complete', 'cancel']);

// Steps configuration based on type
const stepsConfig = computed(() => {
    if (props.type === 'wire') {
        return [
            { label: 'PIN', icon: 'pi pi-lock', key: 'pin' },
            { label: 'IMF', icon: 'pi pi-globe', key: 'imf' },
            { label: 'Tax', icon: 'pi pi-file', key: 'tax' },
            { label: 'COT', icon: 'pi pi-dollar', key: 'cot' },
            { label: 'OTP', icon: 'pi pi-envelope', key: 'otp' },
        ];
    }
    return [
        { label: 'PIN', icon: 'pi pi-lock', key: 'pin' },
        { label: 'OTP', icon: 'pi pi-envelope', key: 'otp' },
    ];
});

// Current step
const currentStep = ref(0);
const currentStepKey = computed(() => stepsConfig.value[currentStep.value]?.key);

// Verification data
const verificationData = ref({
    pin: '',
    imf: '',
    tax: '',
    cot: '',
    otp: '',
});

// Errors
const errors = ref({});

// Step refs
const pinRef = ref(null);
const otpRef = ref(null);

// Reset on modal open
watch(() => props.visible, (visible) => {
    if (visible) {
        currentStep.value = 0;
        verificationData.value = { pin: '', imf: '', tax: '', cot: '', otp: '' };
        errors.value = {};
    }
});

// Handle verification code complete
const handleCodeComplete = (key, code) => {
    verificationData.value[key] = code;
    errors.value[key] = '';

    // Auto-advance to next step
    setTimeout(() => {
        if (currentStep.value < stepsConfig.value.length - 1) {
            currentStep.value++;
        } else {
            // All steps complete - emit complete event
            emit('complete', verificationData.value);
        }
    }, 300);
};

// Handle code input (for non-PIN/OTP codes)
const handleCodeInput = (key, event) => {
    const value = event.target.value.replace(/\D/g, '').slice(0, 8);
    verificationData.value[key] = value;
    errors.value[key] = '';
};

// Submit current step (for code inputs)
const submitCurrentStep = () => {
    const key = currentStepKey.value;
    const code = verificationData.value[key];

    if (!code || code.length < 6) {
        errors.value[key] = 'Please enter a valid code';
        return;
    }

    if (currentStep.value < stepsConfig.value.length - 1) {
        currentStep.value++;
    } else {
        emit('complete', verificationData.value);
    }
};

// Close modal
const closeModal = () => {
    emit('update:visible', false);
    emit('cancel');
};

// Go back
const goBack = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    } else {
        closeModal();
    }
};

// Format currency
const formatCurrency = (cents) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.transactionDetails.currency || 'USD',
    }).format(cents / 100);
};
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :closable="!loading"
        :header="title"
        :style="{ width: '450px' }"
        :pt="{
            root: { class: 'rounded-2xl' },
            header: { class: 'p-6 pb-0' },
            content: { class: 'p-6' },
        }"
        @update:visible="$emit('update:visible', $event)"
    >
        <!-- Transaction Summary -->
        <div v-if="transactionDetails.amount" class="bg-gray-50 rounded-xl p-4 mb-6">
            <div class="text-center">
                <p class="text-sm text-gray-500">Amount</p>
                <p class="text-2xl font-bold text-gray-900">
                    {{ formatCurrency(transactionDetails.amount) }}
                </p>
                <p v-if="transactionDetails.recipient" class="text-sm text-gray-600 mt-1">
                    To: {{ transactionDetails.recipient }}
                </p>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <Steps
                :model="stepsConfig.map((s, i) => ({ label: s.label }))"
                :activeStep="currentStep"
                :pt="{
                    menu: { class: 'justify-center' },
                    menuitem: { class: 'flex-none' }
                }"
            />
        </div>

        <!-- PIN Step -->
        <div v-if="currentStepKey === 'pin'" class="text-center">
            <PinInput
                ref="pinRef"
                :length="6"
                :error="errors.pin"
                label="Enter your transaction PIN"
                :disabled="loading"
                @complete="handleCodeComplete('pin', $event)"
            />
        </div>

        <!-- OTP Step -->
        <div v-else-if="currentStepKey === 'otp'">
            <OtpInput
                ref="otpRef"
                :length="6"
                :error="errors.otp"
                :loading="loading"
                :disabled="loading"
                @complete="handleCodeComplete('otp', $event)"
                @resend="$emit('resend-otp')"
            />
        </div>

        <!-- Wire Transfer Codes (IMF, Tax, COT) -->
        <div v-else class="text-center">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i :class="['pi', stepsConfig[currentStep].icon, 'text-2xl text-amber-600']"></i>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                {{ currentStepKey === 'imf' ? 'IMF Verification Code' : '' }}
                {{ currentStepKey === 'tax' ? 'Tax Clearance Code' : '' }}
                {{ currentStepKey === 'cot' ? 'Cost of Transfer Code' : '' }}
            </h3>
            <p class="text-sm text-gray-600 mb-6">
                {{ currentStepKey === 'imf' ? 'Enter your IMF verification code to proceed' : '' }}
                {{ currentStepKey === 'tax' ? 'Enter your tax clearance code' : '' }}
                {{ currentStepKey === 'cot' ? 'Enter your COT code to complete the transfer' : '' }}
            </p>

            <input
                type="text"
                inputmode="numeric"
                maxlength="8"
                :value="verificationData[currentStepKey]"
                :class="[
                    'w-full px-4 py-4 text-center text-xl font-mono tracking-widest rounded-xl border-2 transition-all',
                    'focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                    errors[currentStepKey] ? 'border-red-300' : 'border-gray-200'
                ]"
                placeholder="Enter code"
                @input="handleCodeInput(currentStepKey, $event)"
                @keyup.enter="submitCurrentStep"
            />

            <p v-if="errors[currentStepKey]" class="mt-2 text-sm text-red-600">
                {{ errors[currentStepKey] }}
            </p>

            <p class="mt-3 text-xs text-gray-500">
                Contact support if you don't have this code
            </p>
        </div>

        <!-- Actions -->
        <div class="flex justify-between mt-8">
            <Button
                label="Back"
                icon="pi pi-arrow-left"
                text
                :disabled="loading"
                @click="goBack"
            />

            <Button
                v-if="currentStepKey !== 'pin' && currentStepKey !== 'otp'"
                label="Continue"
                icon="pi pi-arrow-right"
                iconPos="right"
                :loading="loading"
                @click="submitCurrentStep"
            />
        </div>
    </Dialog>
</template>
