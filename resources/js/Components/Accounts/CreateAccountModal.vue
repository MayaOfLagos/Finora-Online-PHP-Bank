<script setup>
/**
 * CreateAccountModal Component
 * Modal for creating a new bank account
 */
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import PinInput from '@/Components/Forms/PinInput.vue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    accountTypes: {
        type: Array,
        default: () => []
    },
    currencies: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:visible', 'account-created']);

// Form state
const form = useForm({
    account_type_id: null,
    account_name: '',
    currency: 'USD',
    terms_accepted: false,
    pin: ''
});

const currentStep = ref(1);
const showPinInput = ref(false);

// Currency options
const currencyOptions = computed(() => {
    return props.currencies.map(currency => ({
        label: `${currency.code} - ${currency.name}`,
        value: currency.code
    }));
});

// Account type options
const accountTypeOptions = computed(() => {
    return props.accountTypes.map(type => ({
        label: type.name,
        value: type.id,
        description: type.description
    }));
});

// Validation
const isStep1Valid = computed(() => {
    return form.account_type_id !== null;
});

const isStep2Valid = computed(() => {
    return form.currency && form.terms_accepted;
});

// Step navigation
const nextStep = () => {
    if (currentStep.value === 1 && isStep1Valid.value) {
        currentStep.value = 2;
    } else if (currentStep.value === 2 && isStep2Valid.value) {
        showPinInput.value = true;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

// PIN verification and submission
const handlePinComplete = (pin) => {
    form.pin = pin;
    submitForm();
};

const submitForm = () => {
    form.post(route('accounts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            emit('account-created');
            emit('update:visible', false);
        },
        onError: () => {
            showPinInput.value = false;
        }
    });
};

// Reset form
const resetForm = () => {
    form.reset();
    currentStep.value = 1;
    showPinInput.value = false;
};

// Close modal
const handleClose = () => {
    if (!form.processing) {
        resetForm();
        emit('update:visible', false);
    }
};
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :closable="!form.processing"
        :draggable="false"
        class="w-full max-w-2xl"
        @update:visible="handleClose"
    >
        <template #header>
            <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Open New Account
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Step {{ currentStep }} of 2
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Step 1: Account Type -->
            <div v-show="currentStep === 1" class="space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select Account Type <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button
                            v-for="type in accountTypeOptions"
                            :key="type.value"
                            type="button"
                            @click="form.account_type_id = type.value"
                            :class="[
                                'p-4 text-left border-2 rounded-xl transition-all',
                                form.account_type_id === type.value
                                    ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300'
                            ]"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    :class="[
                                        'w-4 h-4 rounded-full border-2 mt-1 flex-shrink-0',
                                        form.account_type_id === type.value
                                            ? 'border-indigo-500 bg-indigo-500'
                                            : 'border-gray-300'
                                    ]"
                                >
                                    <div
                                        v-if="form.account_type_id === type.value"
                                        class="w-full h-full bg-white rounded-full scale-50"
                                    ></div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ type.label }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ type.description }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </div>
                    <Message v-if="form.errors.account_type_id" severity="error" :closable="false" class="mt-2">
                        {{ form.errors.account_type_id }}
                    </Message>
                </div>
            </div>

            <!-- Step 2: Account Details -->
            <div v-show="currentStep === 2" class="space-y-4">
                <!-- Account Name (Optional) -->
                <div>
                    <label for="account_name" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Account Nickname (Optional)
                    </label>
                    <InputText
                        id="account_name"
                        v-model="form.account_name"
                        placeholder="e.g., Emergency Savings"
                        class="w-full"
                        :disabled="form.processing"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Give your account a friendly name for easy identification
                    </p>
                </div>

                <!-- Currency -->
                <div>
                    <label for="currency" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Currency <span class="text-red-500">*</span>
                    </label>
                    <Dropdown
                        id="currency"
                        v-model="form.currency"
                        :options="currencyOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Currency"
                        class="w-full"
                        :disabled="form.processing"
                    />
                    <Message v-if="form.errors.currency" severity="error" :closable="false" class="mt-2">
                        {{ form.errors.currency }}
                    </Message>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start gap-3">
                    <Checkbox
                        v-model="form.terms_accepted"
                        :binary="true"
                        inputId="terms"
                        :disabled="form.processing"
                    />
                    <label for="terms" class="text-sm text-gray-700 cursor-pointer dark:text-gray-300">
                        I accept the <a href="/terms" target="_blank" class="text-indigo-600 hover:underline">Terms and Conditions</a>
                        <span class="text-red-500">*</span>
                    </label>
                </div>
                <Message v-if="form.errors.terms_accepted" severity="error" :closable="false">
                    {{ form.errors.terms_accepted }}
                </Message>
            </div>

            <!-- PIN Input -->
            <div v-if="showPinInput" class="space-y-4">
                <Message severity="info" :closable="false">
                    <p class="text-sm">Enter your 6-digit transaction PIN to create the account</p>
                </Message>

                <PinInput
                    :length="6"
                    :masked="true"
                    @complete="handlePinComplete"
                    :disabled="form.processing"
                />

                <Message v-if="form.errors.pin" severity="error" :closable="false">
                    {{ form.errors.pin }}
                </Message>
            </div>

            <!-- General Error -->
            <Message v-if="form.errors.general" severity="error" :closable="false">
                {{ form.errors.general }}
            </Message>
        </div>

        <template #footer>
            <div class="flex justify-between gap-3">
                <!-- Back Button -->
                <Button
                    v-if="currentStep > 1 && !showPinInput"
                    label="Back"
                    icon="pi pi-arrow-left"
                    outlined
                    @click="prevStep"
                    :disabled="form.processing"
                />
                <div v-else></div>

                <!-- Next/Submit Button -->
                <div class="flex gap-3">
                    <Button
                        label="Cancel"
                        outlined
                        @click="handleClose"
                        :disabled="form.processing"
                    />
                    <Button
                        v-if="!showPinInput"
                        :label="currentStep === 2 ? 'Create Account' : 'Next'"
                        :icon="currentStep === 2 ? 'pi pi-check' : 'pi pi-arrow-right'"
                        iconPos="right"
                        @click="nextStep"
                        :disabled="currentStep === 1 ? !isStep1Valid : !isStep2Valid"
                        :loading="form.processing"
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>
