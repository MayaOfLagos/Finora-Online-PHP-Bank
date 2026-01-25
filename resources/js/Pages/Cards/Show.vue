<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DigitalCard from '@/Components/Cards/DigitalCard.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';

const props = defineProps({
    card: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const isFrozen = computed(() => props.card.status === 'frozen');
const isActive = computed(() => props.card.status === 'active');

const currency = computed(() => props.card.bank_account?.currency || 'USD');

const formatMoney = (value) => {
    if (value === null || value === undefined) return '—';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: currency.value }).format(value / 100);
};

const toggleFreeze = () => {
    router.post(route('cards.freeze', props.card.uuid), {}, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['card'] }),
    });
};
</script>

<template>
    <DashboardLayout title="Card Details">
        <div class="space-y-6 max-w-5xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ props.card.card_type }}</h1>
                    <p class="text-sm text-gray-500">{{ props.card.card_holder_name }}</p>
                </div>
                <Tag :value="props.card.status_label" :severity="props.card.status_color" />
            </div>

            <DigitalCard :card="props.card" />

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <template #title>
                        Card Information
                    </template>
                    <template #content>
                        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <div class="flex justify-between">
                                <span>Card Type</span>
                                <span class="font-semibold">{{ props.card.card_type }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Card Number</span>
                                <span class="font-mono">{{ props.card.card_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Expiry</span>
                                <span>{{ props.card.expiry || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Virtual</span>
                                <span>{{ props.card.is_virtual ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>
                        Linked Account
                    </template>
                    <template #content>
                        <div v-if="props.card.bank_account" class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <div class="flex justify-between">
                                <span>Account</span>
                                <span class="font-semibold">{{ props.card.bank_account.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Number</span>
                                <span class="font-mono">{{ props.card.bank_account.number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Currency</span>
                                <span>{{ props.card.bank_account.currency }}</span>
                            </div>
                        </div>
                        <Message v-else severity="warn" :closable="false">
                            No bank account linked.
                        </Message>
                    </template>
                </Card>
            </div>

            <Card>
                <template #title>
                    Limits
                </template>
                <template #content>
                    <div class="grid gap-3 md:grid-cols-2 text-sm text-gray-700 dark:text-gray-300">
                        <div class="flex justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <span>Spending Limit</span>
                            <span class="font-semibold">{{ formatMoney(props.card.spending_limit) }}</span>
                        </div>
                        <div class="flex justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <span>Daily Limit</span>
                            <span class="font-semibold">{{ formatMoney(props.card.daily_limit) }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <div class="flex gap-3">
                <Button
                    v-if="isActive || isFrozen"
                    :label="isFrozen ? 'Unfreeze Card' : 'Freeze Card'"
                    :icon="isFrozen ? 'pi pi-sun' : 'pi pi-snowflake'"
                    :severity="isFrozen ? 'success' : 'secondary'"
                    @click="toggleFreeze"
                />
                <Button
                    label="Back to Cards"
                    icon="pi pi-arrow-left"
                    outlined
                    @click="router.visit('/cards')"
                />
            </div>

            <Message v-if="flash.success" severity="success" :closable="false">
                {{ flash.success }}
            </Message>
            <Message v-if="flash.error" severity="error" :closable="false">
                {{ flash.error }}
            </Message>
        </div>
    </DashboardLayout>
</template>
