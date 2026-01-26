<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import Divider from 'primevue/divider';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    vouchers: { type: Object, default: () => ({ data: [] }) },
    stats: { type: Object, default: () => ({}) },
    bankAccounts: { type: Array, default: () => [] },
});

const page = usePage();
const flash = computed(() => page.props.flash);
const toast = useToast();

// Show flash success message
if (flash.value?.success) {
    toast.add({ severity: 'success', summary: 'Success', detail: flash.value.success, life: 4000 });
}

const form = useForm({
    voucher_code: '',
    bank_account_id: props.bankAccounts?.[0]?.id ?? null,
    pin: '',
});

// Modal states
const showConfirmModal = ref(false);
const showPinModal = ref(false);
const isVerifying = ref(false);

const formatAmount = (value, currency = 'USD') => new Intl.NumberFormat('en-US', { style: 'currency', currency }).format((value || 0) / 100);

const statusMeta = {
    active: { label: 'Active', severity: 'success' },
    used: { label: 'Used', severity: 'secondary' },
    expired: { label: 'Expired', severity: 'danger' },
};

// Get selected account details
const selectedAccount = computed(() => {
    return props.bankAccounts.find(acc => acc.id === form.bank_account_id);
});

// Step 1: Show confirmation modal
const initiateRedeem = () => {
    if (!form.voucher_code || !form.bank_account_id) {
        if (!form.voucher_code) {
            form.setError('voucher_code', 'Please enter a voucher code');
        }
        if (!form.bank_account_id) {
            form.setError('bank_account_id', 'Please select an account');
        }
        return;
    }
    form.clearErrors();
    showConfirmModal.value = true;
};

// Step 2: User confirms, show PIN modal
const proceedToPinVerification = () => {
    showConfirmModal.value = false;
    form.pin = '';
    showPinModal.value = true;
};

// Step 3: Verify PIN and submit
const submitWithPin = () => {
    if (form.pin.length !== 6) {
        form.setError('pin', 'PIN must be 6 digits');
        return;
    }
    
    isVerifying.value = true;
    form.post('/vouchers/redeem', {
        preserveScroll: true,
        onSuccess: () => {
            showPinModal.value = false;
            form.reset('voucher_code', 'pin');
            toast.add({ severity: 'success', summary: 'Voucher Redeemed!', detail: 'Check your email for confirmation.', life: 4000 });
        },
        onError: (errors) => {
            if (errors.pin) {
                // Keep PIN modal open for PIN errors
            } else {
                showPinModal.value = false;
            }
        },
        onFinish: () => {
            isVerifying.value = false;
        },
    });
};

const cancelRedeem = () => {
    showConfirmModal.value = false;
    showPinModal.value = false;
    form.pin = '';
};

const statsList = computed(() => ([
    { label: 'Active', value: props.stats.active ?? 0 },
    { label: 'Used', value: props.stats.used ?? 0 },
    { label: 'Expired', value: props.stats.expired ?? 0 },
    { label: 'Total Value Used', value: formatAmount((props.stats.total_value ?? 0) * 100) },
]));
</script>

<template>
    <DashboardLayout title="Vouchers">
        <div class="space-y-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Vouchers</h1>
                <p class="text-gray-600 dark:text-gray-400">Redeem vouchers to credit your accounts.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <template #title>Redeem Voucher</template>
                    <template #content>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Voucher Code</label>
                                <InputText 
                                    v-model="form.voucher_code" 
                                    placeholder="ENTER-CODE" 
                                    class="w-full mt-1 uppercase"
                                    @keyup.enter="initiateRedeem"
                                />
                                <p v-if="form.errors.voucher_code" class="text-sm text-red-500 mt-1">{{ form.errors.voucher_code }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Deposit To</label>
                                <Select 
                                    v-model="form.bank_account_id" 
                                    :options="bankAccounts" 
                                    optionLabel="account_number" 
                                    optionValue="id" 
                                    class="w-full mt-1" 
                                    placeholder="Select account"
                                >
                                    <template #option="{ option }">
                                        <div class="flex justify-between items-center w-full">
                                            <span>{{ option.account_name }}</span>
                                            <span class="text-gray-500 text-sm">****{{ option.account_number?.slice(-4) }}</span>
                                        </div>
                                    </template>
                                    <template #value="{ value }">
                                        <span v-if="selectedAccount">
                                            {{ selectedAccount.account_name }} - ****{{ selectedAccount.account_number?.slice(-4) }}
                                        </span>
                                        <span v-else class="text-gray-400">Select account</span>
                                    </template>
                                </Select>
                                <p v-if="form.errors.bank_account_id" class="text-sm text-red-500 mt-1">{{ form.errors.bank_account_id }}</p>
                                <Message v-if="!bankAccounts.length" severity="warn" :closable="false" class="mt-2">No bank accounts found. Add an account first.</Message>
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <Button 
                                    label="Redeem Voucher" 
                                    icon="pi pi-ticket" 
                                    :disabled="!form.voucher_code || !form.bank_account_id"
                                    @click="initiateRedeem" 
                                />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Stats</template>
                    <template #content>
                        <div class="space-y-3">
                            <div v-for="stat in statsList" :key="stat.label" class="flex items-center justify-between p-3 border rounded-xl dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ stat.label }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ stat.value }}</span>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #title>Your Vouchers</template>
                <template #content>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="voucher in vouchers.data" :key="voucher.id" class="p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ voucher.code }}</h3>
                                <Tag :value="statusMeta[voucher.status]?.label || voucher.status" :severity="statusMeta[voucher.status]?.severity || 'info'" />
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Value: {{ formatAmount(voucher.amount, voucher.currency || 'USD') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Expires: {{ voucher.expires_at || 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ voucher.description || 'Voucher credit' }}</p>
                        </div>
                    </div>
                    <p v-if="!vouchers.data.length" class="text-sm text-gray-600 dark:text-gray-400">No vouchers yet.</p>
                </template>
            </Card>
        </div>

        <!-- Confirmation Modal -->
        <Dialog 
            v-model:visible="showConfirmModal" 
            modal 
            header="Confirm Voucher Redemption" 
            :style="{ width: '450px' }"
            :closable="true"
        >
            <div class="space-y-4">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                        <i class="pi pi-ticket text-3xl text-green-600 dark:text-green-400"></i>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300">You are about to redeem the following voucher:</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Voucher Code:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ form.voucher_code.toUpperCase() }}</span>
                    </div>
                    <Divider />
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Deposit To:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ selectedAccount?.account_name || 'Selected Account' }}
                        </span>
                    </div>
                </div>

                <Message severity="info" :closable="false">
                    <span class="text-sm">You will need to enter your 6-digit PIN to confirm this transaction.</span>
                </Message>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Cancel" severity="secondary" outlined @click="cancelRedeem" />
                    <Button label="Continue" icon="pi pi-arrow-right" @click="proceedToPinVerification" />
                </div>
            </template>
        </Dialog>

        <!-- PIN Verification Modal -->
        <Dialog 
            v-model:visible="showPinModal" 
            modal 
            header="Enter Transaction PIN" 
            :style="{ width: '400px' }"
            :closable="!isVerifying"
        >
            <div class="space-y-4">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                        <i class="pi pi-lock text-3xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300">Enter your 6-digit transaction PIN to confirm redemption</p>
                </div>

                <div class="flex flex-col items-center gap-2">
                    <Password 
                        v-model="form.pin"
                        placeholder="Enter PIN"
                        :feedback="false"
                        toggleMask
                        inputClass="w-full text-center text-2xl tracking-[0.5em] font-mono"
                        :maxlength="6"
                        :disabled="isVerifying"
                        @keyup.enter="submitWithPin"
                    />
                    <p v-if="form.errors.pin" class="text-sm text-red-500">{{ form.errors.pin }}</p>
                </div>

                <Message severity="warn" :closable="false">
                    <span class="text-sm">Never share your PIN with anyone. Finora Bank will never ask for your PIN via email or phone.</span>
                </Message>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Cancel" severity="secondary" outlined :disabled="isVerifying" @click="cancelRedeem" />
                    <Button 
                        label="Confirm & Redeem" 
                        icon="pi pi-check" 
                        :loading="isVerifying"
                        :disabled="form.pin.length !== 6"
                        @click="submitWithPin" 
                    />
                </div>
            </template>
        </Dialog>
    </DashboardLayout>
</template>
