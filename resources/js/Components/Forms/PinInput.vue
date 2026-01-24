<script setup>
/**
 * PinInput Component
 * Secure PIN entry with masked digits
 */
import { ref, computed, watch, onMounted, nextTick } from 'vue';

const props = defineProps({
    length: {
        type: Number,
        default: 6,
    },
    masked: {
        type: Boolean,
        default: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: 'Enter PIN',
    },
    autoFocus: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['complete', 'update:modelValue', 'change']);

// Create array for input refs
const inputRefs = ref([]);
const digits = ref(Array(props.length).fill(''));

// Computed PIN value
const pinValue = computed(() => digits.value.join(''));

// Watch for changes and emit
watch(pinValue, (newValue) => {
    emit('update:modelValue', newValue);
    emit('change', newValue);

    if (newValue.length === props.length) {
        emit('complete', newValue);
    }
});

// Handle input
const handleInput = (index, event) => {
    const value = event.target.value;

    // Only allow single digit
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
    // Backspace - go to previous input
    if (event.key === 'Backspace') {
        if (!digits.value[index] && index > 0) {
            nextTick(() => {
                inputRefs.value[index - 1]?.focus();
            });
        }
        digits.value[index] = '';
    }

    // Arrow keys navigation
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

    // Focus last filled input or first empty
    const lastIndex = Math.min(pastedDigits.length, props.length) - 1;
    nextTick(() => {
        inputRefs.value[lastIndex]?.focus();
    });
};

// Clear PIN
const clear = () => {
    digits.value = Array(props.length).fill('');
    nextTick(() => {
        inputRefs.value[0]?.focus();
    });
};

// Focus first input on mount
onMounted(() => {
    if (props.autoFocus) {
        nextTick(() => {
            inputRefs.value[0]?.focus();
        });
    }
});

// Expose clear method
defineExpose({ clear });
</script>

<template>
    <div class="pin-input-wrapper">
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-3">
            {{ label }}
        </label>

        <div class="flex gap-2 sm:gap-3 justify-center">
            <input
                v-for="(_, index) in length"
                :key="index"
                :ref="el => inputRefs[index] = el"
                :type="masked ? 'password' : 'text'"
                inputmode="numeric"
                maxlength="1"
                :value="digits[index]"
                :disabled="disabled"
                :class="[
                    'w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 transition-all',
                    'focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                    error ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white',
                    disabled ? 'opacity-50 cursor-not-allowed bg-gray-100' : 'hover:border-gray-300',
                    digits[index] ? 'border-indigo-300' : ''
                ]"
                @input="handleInput(index, $event)"
                @keydown="handleKeydown(index, $event)"
                @paste="handlePaste"
            />
        </div>

        <p v-if="error" class="mt-2 text-sm text-red-600 text-center">
            {{ error }}
        </p>

        <!-- Hint -->
        <p class="mt-3 text-xs text-gray-500 text-center">
            Enter your {{ length }}-digit PIN
        </p>
    </div>
</template>
