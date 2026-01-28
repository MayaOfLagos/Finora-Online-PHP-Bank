<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Message from 'primevue/message';
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import StepItem from 'primevue/stepitem';
import Step from 'primevue/step';
import StepPanel from 'primevue/steppanel';

const props = defineProps({
    program: Object,
    accounts: Array,
});

const form = useForm({
    loan_type_id: props.program.id,
    bank_account_id: null,
    amount: null,
    term_months: 12,
    purpose: '',
});

const selectedAccount = ref(null);
const calculatedPayment = ref(null);

// Calculate monthly payment
const calculatePayment = () => {
    // Clear any lingering error messages when user corrects input
    if (form.errors.amount && form.amount) {
        form.clearErrors('amount');
    }

    // Validate amount is within range (both are in dollars from API)
    if (!form.amount || !form.term_months) {
        calculatedPayment.value = null;
        return;
    }

    // form.amount is in dollars, compare directly with min/max from API (also in dollars)
    if (form.amount < props.program.min_amount || form.amount > props.program.max_amount) {
        calculatedPayment.value = null;
        return;
    }

    const principal = form.amount;
    const monthlyRate = props.program.interest_rate / 100 / 12;
    const n = form.term_months;

    const payment = principal * (monthlyRate * Math.pow(1 + monthlyRate, n)) / (Math.pow(1 + monthlyRate, n) - 1);
    const totalPayable = payment * n;

    calculatedPayment.value = {
        monthly: payment,
        total: totalPayable,
        totalInterest: totalPayable - principal,
    };
};

watch([() => form.amount, () => form.term_months], calculatePayment);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const termOptions = computed(() => {
    const options = [];
    for (let i = props.program.min_term_months; i <= props.program.max_term_months; i += 12) {
        options.push({ label: `${i} months (${i / 12} ${i / 12 === 1 ? 'year' : 'years'})`, value: i });
    }
    return options;
});

const submitApplication = () => {
    form.transform((data) => ({
        ...data,
        amount: Math.round((data.amount || 0) * 100), // Convert dollars to cents
    })).post(route('loans.store'), {
        onSuccess: () => {
            router.visit('/loans/applications');
        },
    });
};

const onAccountChange = (event) => {
    selectedAccount.value = props.accounts.find(acc => acc.id === event.value);
};
</script>

<template>
    <DashboardLayout title="Apply for Loan">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Program Info -->
            <Card class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <template #content>
                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold">{{ program.name }}</h2>
                        <p class="opacity-90">{{ program.description }}</p>
                        <div class="flex flex-wrap gap-6 mt-4 pt-4 border-t border-white/20">
                            <div>
                                <p class="text-sm opacity-80">Amount Range</p>
                                <p class="font-semibold">{{ formatCurrency(program.min_amount) }} - {{ formatCurrency(program.max_amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm opacity-80">Term Range</p>
                                <p class="font-semibold">{{ program.min_term_months }} - {{ program.max_term_months }} months</p>
                            </div>
                            <div>
                                <p class="text-sm opacity-80">Interest Rate</p>
                                <p class="font-bold text-xl">{{ program.interest_rate }}%</p>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Application Form -->
            <Card>
                <template #title>Loan Application</template>
                <template #content>
                    <form @submit.prevent="submitApplication" class="space-y-6">
                        <!-- Step 1: Account Selection -->
                        <div>
                            <label for="bank_account" class="block text-sm font-medium text-gray-700 mb-2">
                                Disbursement Account *
                            </label>
                            <Dropdown
                                id="bank_account"
                                v-model="form.bank_account_id"
                                :options="accounts"
                                optionLabel="account_number"
                                optionValue="id"
                                placeholder="Select account for loan disbursement"
                                class="w-full"
                                @change="onAccountChange"
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center gap-2">
                                        <span>{{ accounts.find(a => a.id === slotProps.value)?.account_number }}</span>
                                        <span class="text-gray-500">- {{ accounts.find(a => a.id === slotProps.value)?.account_name }}</span>
                                    </div>
                                    <span v-else>{{ slotProps.placeholder }}</span>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-medium">{{ slotProps.option.account_number }}</div>
                                            <div class="text-sm text-gray-500">{{ slotProps.option.account_name }}</div>
                                        </div>
                                        <div class="text-sm font-semibold">{{ formatCurrency(slotProps.option.balance) }}</div>
                                    </div>
                                </template>
                            </Dropdown>
                            <small v-if="form.errors.bank_account_id" class="text-red-500">{{ form.errors.bank_account_id }}</small>
                        </div>

                        <!-- Step 2: Loan Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Loan Amount * ({{ formatCurrency(program.min_amount) }} - {{ formatCurrency(program.max_amount) }})
                            </label>
                            <InputNumber
                                id="amount"
                                v-model="form.amount"
                                mode="currency"
                                currency="USD"
                                :min="program.min_amount"
                                :max="program.max_amount"
                                :minFractionDigits="2"
                                class="w-full"
                            />
                            <small v-if="form.errors.amount" class="text-red-500">{{ form.errors.amount }}</small>
                        </div>

                        <!-- Step 3: Loan Term -->
                        <div>
                            <label for="term" class="block text-sm font-medium text-gray-700 mb-2">
                                Loan Term *
                            </label>
                            <Dropdown
                                id="term"
                                v-model="form.term_months"
                                :options="termOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Select loan term"
                                class="w-full"
                            />
                            <small v-if="form.errors.term_months" class="text-red-500">{{ form.errors.term_months }}</small>
                        </div>

                        <!-- Calculation Preview -->
                        <div v-if="calculatedPayment" class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
                            <h4 class="font-semibold text-blue-900">Loan Calculation</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-blue-700">Monthly Payment</p>
                                    <p class="text-xl font-bold text-blue-900">{{ formatCurrency(calculatedPayment.monthly) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-blue-700">Total Interest</p>
                                    <p class="text-xl font-bold text-orange-600">{{ formatCurrency(calculatedPayment.totalInterest) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-blue-700">Total Payable</p>
                                    <p class="text-xl font-bold text-blue-900">{{ formatCurrency(calculatedPayment.total) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Purpose -->
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                                Purpose of Loan *
                            </label>
                            <Textarea
                                id="purpose"
                                v-model="form.purpose"
                                rows="4"
                                placeholder="Please describe what you will use this loan for..."
                                class="w-full"
                                :maxlength="1000"
                            />
                            <small class="text-gray-500">{{ form.purpose?.length || 0 }} / 1000 characters</small>
                            <br>
                            <small v-if="form.errors.purpose" class="text-red-500">{{ form.errors.purpose }}</small>
                        </div>

                        <!-- Terms & Conditions -->
                        <Message severity="warn">
                            <p class="text-sm">
                                By submitting this application, you agree to our loan terms and conditions. 
                                Your application will be reviewed by our team, and you will be notified of the decision within 1-3 business days.
                            </p>
                        </Message>

                        <!-- Submit Button -->
                        <div class="flex gap-3">
                            <Button 
                                type="submit"
                                label="Submit Application" 
                                icon="pi pi-check"
                                :loading="form.processing"
                                :disabled="!form.bank_account_id || !form.amount || !form.term_months || !form.purpose"
                            />
                            <Button 
                                type="button"
                                label="Cancel" 
                                icon="pi pi-times"
                                severity="secondary"
                                outlined
                                @click="router.visit('/loans/programs')"
                            />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
