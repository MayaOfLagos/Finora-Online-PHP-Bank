<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    vouchers: { type: Object, default: () => ({ data: [] }) },
    stats: { type: Object, default: () => ({}) },
    bankAccounts: { type: Array, default: () => [] },
});

const toast = useToast();

const form = useForm({
    voucher_code: '',
    bank_account_id: props.bankAccounts?.[0]?.id ?? null,
});

const formatAmount = (value, currency = 'USD') => new Intl.NumberFormat('en-US', { style: 'currency', currency }).format((value || 0) / 100);

const statusMeta = {
    active: { label: 'Active', severity: 'success' },
    used: { label: 'Used', severity: 'secondary' },
    expired: { label: 'Expired', severity: 'danger' },
};

const submitRedeem = () => {
    form.post('/vouchers/redeem', {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Redeemed', detail: 'Voucher applied.', life: 2500 });
            form.reset('voucher_code');
        },
    });
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
                                <label class="text-sm text-gray-700 dark:text-gray-300">Voucher Code</label>
                                <InputText v-model="form.voucher_code" placeholder="ENTER-CODE" class="w-full mt-1" />
                                <p v-if="form.errors.voucher_code" class="text-sm text-red-500">{{ form.errors.voucher_code }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Deposit To</label>
                                <Dropdown v-model="form.bank_account_id" :options="bankAccounts" optionLabel="account_number" optionValue="id" class="w-full mt-1" placeholder="Select account" />
                                <p v-if="form.errors.bank_account_id" class="text-sm text-red-500">{{ form.errors.bank_account_id }}</p>
                                <Message v-if="!bankAccounts.length" severity="warn" :closable="false" class="mt-2">No bank accounts found. Add an account first.</Message>
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <Button label="Redeem" icon="pi pi-ticket" :loading="form.processing" @click="submitRedeem" />
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
    </DashboardLayout>
</template>
