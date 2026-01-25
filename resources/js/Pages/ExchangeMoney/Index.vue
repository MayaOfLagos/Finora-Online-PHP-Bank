<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';
import Divider from 'primevue/divider';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    bankAccounts: { type: Array, default: () => [] },
    exchangeRates: { type: Object, default: () => ({}) },
});

const toast = useToast();

const currencyOptions = computed(() => Object.keys(props.exchangeRates).map((code) => ({ label: code, value: code })));

const form = useForm({
    bank_account_id: props.bankAccounts?.[0]?.id ?? null,
    from_currency: currencyOptions.value?.[0]?.value ?? 'USD',
    to_currency: currencyOptions.value?.[1]?.value ?? 'EUR',
    amount: 100,
});

const rateResult = ref(null);
const isFetchingRate = ref(false);

const fetchRate = async () => {
    if (!form.from_currency || !form.to_currency || !form.amount) return;
    isFetchingRate.value = true;
    try {
        const { data } = await axios.post('/exchange/rate', {
            from_currency: form.from_currency,
            to_currency: form.to_currency,
            amount: form.amount,
        });
        rateResult.value = data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Rate unavailable', detail: error.response?.data?.message || 'Could not fetch rate.', life: 3000 });
    } finally {
        isFetchingRate.value = false;
    }
};

watch(() => [form.from_currency, form.to_currency, form.amount], fetchRate, { immediate: true });

const submitExchange = () => {
    form.transform((data) => ({
        ...data,
        amount: Math.round((data.amount || 0) * 100), // Convert dollars to cents
    })).post('/exchange', {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Exchanged', detail: 'Currency converted.', life: 2500 }),
    });
};
</script>

<template>
    <DashboardLayout title="Exchange Money">
        <div class="space-y-6">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Exchange Money</h1>
                <p class="text-gray-600 dark:text-gray-400">Convert between currencies using your Finora accounts.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <template #title>Conversion</template>
                    <template #content>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">From Account</label>
                                <Dropdown v-model="form.bank_account_id" :options="bankAccounts" optionLabel="account_number" optionValue="id" class="w-full" placeholder="Select account" />
                                <p v-if="form.errors.bank_account_id" class="text-sm text-red-500">{{ form.errors.bank_account_id }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-gray-700 dark:text-gray-300">From</label>
                                <Dropdown v-model="form.from_currency" :options="currencyOptions" optionLabel="label" optionValue="value" class="w-full" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-gray-700 dark:text-gray-300">To</label>
                                <Dropdown v-model="form.to_currency" :options="currencyOptions" optionLabel="label" optionValue="value" class="w-full" />
                            </div>
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Amount</label>
                                <InputNumber v-model="form.amount" mode="decimal" :min="1" :maxFractionDigits="2" class="w-full" placeholder="0.00" />
                                <p v-if="form.errors.amount" class="text-sm text-red-500">{{ form.errors.amount }}</p>
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <Button label="Exchange" icon="pi pi-sync" :loading="form.processing" @click="submitExchange" />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Quote</template>
                    <template #content>
                        <div v-if="rateResult" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Rate</span>
                                <Tag severity="info" :value="`1 ${form.from_currency} = ${rateResult.rate} ${form.to_currency}`" />
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Converted</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ rateResult.converted_amount?.toFixed?.(2) ?? rateResult.converted_amount }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Fee (1%)</span>
                                <span class="text-gray-900 dark:text-white">{{ rateResult.fee?.toFixed?.(2) ?? rateResult.fee }}</span>
                            </div>
                            <Divider />
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-gray-900 dark:text-white">Total Received</span>
                                <span class="text-xl font-bold text-emerald-600">{{ rateResult.total?.toFixed?.(2) ?? rateResult.total }} {{ form.to_currency }}</span>
                            </div>
                        </div>
                        <div v-else class="space-y-2">
                            <Message severity="secondary" :closable="false">Enter an amount to see a live quote.</Message>
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #title>Available Rates</template>
                <template #content>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="(targets, base) in exchangeRates" :key="base" class="p-4 border rounded-xl dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Base: {{ base }}</p>
                            <div class="mt-2 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                                <div v-for="(rate, target) in targets" :key="target" class="flex justify-between">
                                    <span>{{ target }}</span>
                                    <span>{{ rate }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
