<script setup>
/**
 * ReferralCodeInput Component
 * Coupon-style referral code input with real-time validation
 * Expandable section with toggle
 */
import { ref, computed, watch } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'validated', 'cleared']);

// State
const showInput = ref(false);
const code = ref(props.modelValue);
const isValidating = ref(false);
const validationResult = ref(null);
const debounceTimer = ref(null);

// Computed
const isValid = computed(() => validationResult.value?.valid === true);
const isInvalid = computed(() => validationResult.value?.valid === false);
const inviterInfo = computed(() => validationResult.value?.inviter || null);

// Format code to uppercase
const formatCode = (value) => {
    return value.toUpperCase().replace(/[^A-Z0-9]/g, '');
};

// Handle input change
const handleInput = (event) => {
    const formatted = formatCode(event.target.value);
    code.value = formatted;
    emit('update:modelValue', formatted);
    
    // Clear previous validation
    validationResult.value = null;
    
    // Debounce validation
    if (debounceTimer.value) clearTimeout(debounceTimer.value);
    
    if (formatted.length >= 6) {
        debounceTimer.value = setTimeout(() => {
            validateCode(formatted);
        }, 500);
    }
};

// Validate referral code
const validateCode = async (codeToValidate) => {
    if (!codeToValidate || codeToValidate.length < 6) return;
    
    isValidating.value = true;
    
    try {
        const response = await fetch(`/api/referral/validate/${codeToValidate}`);
        const data = await response.json();
        
        validationResult.value = data;
        
        if (data.valid) {
            emit('validated', data);
        }
    } catch (error) {
        console.error('Referral validation error:', error);
        validationResult.value = {
            valid: false,
            message: 'Unable to validate code. Please try again.',
        };
    } finally {
        isValidating.value = false;
    }
};

// Clear code
const clearCode = () => {
    code.value = '';
    validationResult.value = null;
    emit('update:modelValue', '');
    emit('cleared');
};

// Toggle visibility
const toggleInput = () => {
    showInput.value = !showInput.value;
    if (!showInput.value) {
        clearCode();
    }
};

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
    if (newVal !== code.value) {
        code.value = newVal;
        if (newVal && newVal.length >= 6) {
            showInput.value = true;
            validateCode(newVal);
        }
    }
}, { immediate: true });
</script>

<template>
    <div class="referral-code-input">
        <!-- Toggle Button -->
        <button
            type="button"
            :disabled="disabled"
            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl border-2 border-dashed transition-all duration-300"
            :class="[
                showInput 
                    ? 'border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20' 
                    : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
            ]"
            @click="toggleInput"
        >
            <svg 
                class="w-5 h-5 transition-colors duration-300"
                :class="showInput ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500'"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
            <span 
                class="text-sm font-medium transition-colors duration-300"
                :class="showInput ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400'"
            >
                {{ showInput ? 'Remove referral code' : 'I have a referral code' }}
            </span>
            <svg 
                class="w-4 h-4 transition-all duration-300"
                :class="[
                    showInput ? 'rotate-180 text-indigo-600 dark:text-indigo-400' : 'text-gray-400',
                ]"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <!-- Expandable Input Section -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2 max-h-0"
            enter-to-class="opacity-100 translate-y-0 max-h-48"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 max-h-48"
            leave-to-class="opacity-0 -translate-y-2 max-h-0"
        >
            <div v-show="showInput" class="mt-4 overflow-hidden">
                <!-- Input Field -->
                <div class="relative">
                    <InputText
                        :value="code"
                        :disabled="disabled || isValidating"
                        placeholder="Enter referral code"
                        class="w-full uppercase tracking-widest text-center font-mono text-lg"
                        :class="[
                            isValid ? '!border-green-500 !ring-green-500/20' : '',
                            isInvalid ? '!border-red-500 !ring-red-500/20' : '',
                        ]"
                        maxlength="20"
                        @input="handleInput"
                    />
                    
                    <!-- Validation Status Icons -->
                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                        <ProgressSpinner 
                            v-if="isValidating" 
                            style="width: 20px; height: 20px" 
                            strokeWidth="4"
                        />
                        <svg 
                            v-else-if="isValid" 
                            class="w-5 h-5 text-green-500" 
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <svg 
                            v-else-if="isInvalid" 
                            class="w-5 h-5 text-red-500" 
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Validation Result -->
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <!-- Valid Code - Inviter Preview -->
                    <div 
                        v-if="isValid && inviterInfo" 
                        class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-white">
                                    {{ inviterInfo.name?.charAt(0) || '?' }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200 truncate">
                                    Invited by {{ inviterInfo.name }}
                                </p>
                                <p class="text-xs text-green-600 dark:text-green-400">
                                    {{ validationResult.message || 'Valid referral code!' }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Invalid Code Message -->
                    <div 
                        v-else-if="isInvalid" 
                        class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl"
                    >
                        <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm">
                                {{ validationResult.message || 'Invalid referral code' }}
                            </span>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.15em;
}
</style>
