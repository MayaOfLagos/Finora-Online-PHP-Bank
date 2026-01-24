<script setup>
/**
 * OtpInput Component
 * OTP verification with countdown and resend functionality
 */
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import Button from 'primevue/button';

const props = defineProps({
    length: {
        type: Number,
        default: 6,
    },
    countdown: {
        type: Number,
        default: 300, // 5 minutes in seconds
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    autoFocus: {
        type: Boolean,
        default: true,
    },
    autoSubmit: {
        type: Boolean,
        default: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['complete', 'update:modelValue', 'resend', 'expired']);

// Refs
const inputRefs = ref([]);
const digits = ref(Array(props.length).fill(''));
const timeRemaining = ref(props.countdown);
const canResend = ref(false);
let timerInterval = null;

// Computed OTP value
const otpValue = computed(() => digits.value.join(''));

// Format time remaining
const formattedTime = computed(() => {
    const minutes = Math.floor(timeRemaining.value / 60);
    const seconds = timeRemaining.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

// Watch for changes and emit
watch(otpValue, (newValue) => {
    emit('update:modelValue', newValue);

    if (newValue.length === props.length && props.autoSubmit) {
        emit('complete', newValue);
    }
});

// Handle input
const handleInput = (index, event) => {
    const value = event.target.value;

    if (value.length > 1) {
        digits.value[index] = value.slice(-1);
    } else {
        digits.value[index] = value;
    }

    // Auto-focus next input
    if (value && index < props.length - 1) {
        nextTick(() => {
            inputRefs.value[index + 1]?.focus();
        });
    }
};

// Handle keydown
const handleKeydown = (index, event) => {
    if (event.key === 'Backspace') {
        if (!digits.value[index] && index > 0) {
            nextTick(() => {
                inputRefs.value[index - 1]?.focus();
            });
        }
        digits.value[index] = '';
    }

    if (event.key === 'ArrowLeft' && index > 0) {
        inputRefs.value[index - 1]?.focus();
    }
    if (event.key === 'ArrowRight' && index < props.length - 1) {
        inputRefs.value[index + 1]?.focus();
    }

    // Only allow numbers
    if (!/^\d$/.test(event.key) && !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
        event.preventDefault();
    }
};

// Handle paste
const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').slice(0, props.length);
    const pastedDigits = pastedData.replace(/\D/g, '').split('');

    pastedDigits.forEach((digit, index) => {
        if (index < props.length) {
            digits.value[index] = digit;
        }
    });

    const lastIndex = Math.min(pastedDigits.length, props.length) - 1;
    nextTick(() => {
        inputRefs.value[lastIndex]?.focus();
    });
};

// Start countdown timer
const startTimer = () => {
    canResend.value = false;
    timeRemaining.value = props.countdown;

    timerInterval = setInterval(() => {
        timeRemaining.value--;

        if (timeRemaining.value <= 0) {
            clearInterval(timerInterval);
            canResend.value = true;
            emit('expired');
        }
    }, 1000);
};

// Handle resend
const handleResend = () => {
    if (!canResend.value) return;

    clear();
    startTimer();
    emit('resend');
};

// Clear OTP
const clear = () => {
    digits.value = Array(props.length).fill('');
    nextTick(() => {
        inputRefs.value[0]?.focus();
    });
};

// Lifecycle
onMounted(() => {
    startTimer();

    if (props.autoFocus) {
        nextTick(() => {
            inputRefs.value[0]?.focus();
        });
    }
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

// Expose methods
defineExpose({ clear, startTimer });
</script>

<template>
    <div class="otp-input-wrapper">
        <!-- Title -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-envelope text-2xl text-indigo-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Verify Your Identity</h3>
            <p class="text-sm text-gray-600 mt-1">
                Enter the {{ length }}-digit code sent to your email
            </p>
        </div>

        <!-- OTP Inputs -->
        <div class="flex gap-2 sm:gap-3 justify-center">
            <input
                v-for="(_, index) in length"
                :key="index"
                :ref="el => inputRefs[index] = el"
                type="text"
                inputmode="numeric"
                maxlength="1"
                :value="digits[index]"
                :disabled="disabled || loading"
                :class="[
                    'w-11 h-14 sm:w-12 sm:h-16 text-center text-xl sm:text-2xl font-bold rounded-xl border-2 transition-all',
                    'focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                    error ? 'border-red-300 bg-red-50 animate-shake' : 'border-gray-200 bg-white',
                    (disabled || loading) ? 'opacity-50 cursor-not-allowed bg-gray-100' : 'hover:border-gray-300',
                    digits[index] ? 'border-indigo-300 bg-indigo-50' : ''
                ]"
                @input="handleInput(index, $event)"
                @keydown="handleKeydown(index, $event)"
                @paste="handlePaste"
            />
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-3 text-sm text-red-600 text-center">
            {{ error }}
        </p>

        <!-- Loading State -->
        <div v-if="loading" class="mt-4 flex items-center justify-center gap-2 text-gray-600">
            <i class="pi pi-spinner pi-spin"></i>
            <span class="text-sm">Verifying...</span>
        </div>

        <!-- Timer and Resend -->
        <div class="mt-6 text-center">
            <div v-if="!canResend" class="text-sm text-gray-600">
                <span>Code expires in </span>
                <span class="font-semibold text-indigo-600">{{ formattedTime }}</span>
            </div>

            <div class="mt-3">
                <Button
                    v-if="canResend"
                    label="Resend Code"
                    icon="pi pi-refresh"
                    text
                    class="text-indigo-600"
                    @click="handleResend"
                />
                <p v-else class="text-xs text-gray-500">
                    Didn't receive the code? Wait for the timer to resend
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.animate-shake {
    animation: shake 0.3s ease-in-out;
}
</style>
