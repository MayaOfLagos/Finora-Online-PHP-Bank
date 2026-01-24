<script setup>
/**
 * AmountInput Component
 * Currency-aware amount input with formatting
 */
import { ref, computed, watch, onMounted } from 'vue';
import InputNumber from 'primevue/inputnumber';

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: null,
    },
    currency: {
        type: String,
        default: 'USD',
    },
    min: {
        type: Number,
        default: 0,
    },
    max: {
        type: Number,
        default: null,
    },
    label: {
        type: String,
        default: 'Amount',
    },
    placeholder: {
        type: String,
        default: 'Enter amount',
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    showBalance: {
        type: Boolean,
        default: false,
    },
    availableBalance: {
        type: Number,
        default: 0,
    },
    quickAmounts: {
        type: Array,
        default: () => [100, 500, 1000, 5000],
    },
    showQuickAmounts: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'change', 'max-amount']);

// Local value (in dollars for display)
const localValue = ref(null);

// Currency symbol mapping
const currencySymbols = {
    USD: '$',
    EUR: '€',
    GBP: '£',
    NGN: '₦',
    GHS: '₵',
    KES: 'KSh',
};

const currencySymbol = computed(() => currencySymbols[props.currency] || props.currency);

// Convert cents to dollars for display
watch(() => props.modelValue, (newValue) => {
    if (newValue !== null && newValue !== undefined) {
        localValue.value = newValue / 100;
    } else {
        localValue.value = null;
    }
}, { immediate: true });

// Update parent value (convert dollars to cents)
const updateValue = (value) => {
    localValue.value = value;
    const centsValue = value !== null ? Math.round(value * 100) : null;
    emit('update:modelValue', centsValue);
    emit('change', centsValue);
};

// Set quick amount
const setQuickAmount = (amount) => {
    updateValue(amount);
};

// Set max amount
const setMaxAmount = () => {
    const maxInDollars = props.availableBalance / 100;
    updateValue(maxInDollars);
    emit('max-amount');
};

// Format display balance
const formattedBalance = computed(() => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency,
    }).format(props.availableBalance / 100);
});

// Check if amount exceeds balance
const exceedsBalance = computed(() => {
    if (!props.showBalance || localValue.value === null) return false;
    return (localValue.value * 100) > props.availableBalance;
});
</script>

<template>
    <div class="amount-input-wrapper">
        <!-- Label -->
        <div v-if="label || showBalance" class="flex items-center justify-between mb-2">
            <label v-if="label" class="text-sm font-medium text-gray-700">
                {{ label }}
            </label>
            <div v-if="showBalance" class="text-sm text-gray-500">
                Available:
                <span class="font-medium text-gray-700">{{ formattedBalance }}</span>
            </div>
        </div>

        <!-- Input Container -->
        <div
            :class="[
                'relative rounded-xl border-2 transition-all overflow-hidden',
                error || exceedsBalance ? 'border-red-300' : 'border-gray-200 focus-within:border-indigo-500',
                disabled ? 'bg-gray-100' : 'bg-white'
            ]"
        >
            <!-- Currency Symbol -->
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium text-lg">
                {{ currencySymbol }}
            </div>

            <!-- Input -->
            <InputNumber
                v-model="localValue"
                :min="min"
                :max="max ? max / 100 : null"
                :disabled="disabled"
                :placeholder="placeholder"
                mode="decimal"
                :minFractionDigits="2"
                :maxFractionDigits="2"
                :pt="{
                    root: { class: 'w-full' },
                    input: {
                        class: 'w-full pl-12 pr-20 py-4 text-xl font-semibold text-gray-900 border-0 focus:ring-0 bg-transparent',
                    }
                }"
                @update:modelValue="updateValue"
            />

            <!-- Max Button -->
            <button
                v-if="showBalance"
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                @click="setMaxAmount"
            >
                MAX
            </button>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-red-600">
            {{ error }}
        </p>
        <p v-else-if="exceedsBalance" class="mt-2 text-sm text-red-600">
            Amount exceeds available balance
        </p>

        <!-- Quick Amounts -->
        <div v-if="showQuickAmounts && quickAmounts.length > 0" class="mt-4">
            <p class="text-xs text-gray-500 mb-2">Quick amounts</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="amount in quickAmounts"
                    :key="amount"
                    type="button"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-lg border transition-all',
                        localValue === amount
                            ? 'bg-indigo-50 border-indigo-300 text-indigo-700'
                            : 'bg-white border-gray-200 text-gray-700 hover:border-gray-300'
                    ]"
                    @click="setQuickAmount(amount)"
                >
                    {{ currencySymbol }}{{ amount.toLocaleString() }}
                </button>
            </div>
        </div>
    </div>
</template>
