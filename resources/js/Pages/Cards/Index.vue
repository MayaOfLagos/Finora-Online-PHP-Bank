<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DigitalCard from '@/Components/Cards/DigitalCard.vue';
import StatCard from '@/Components/Cards/StatCard.vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Message from 'primevue/message';

const props = defineProps({
    cards: {
        type: Array,
        default: () => [],
    },
    cardTypes: {
        type: Array,
        default: () => [],
    },
    accounts: {
        type: Array,
        default: () => [],
    },
    cardStats: {
        type: Object,
        default: () => ({ total: 0, active: 0, virtual: 0, physical: 0 }),
    },
});

const requestDialog = ref(false);
const selectedType = ref(null);

const form = useForm({
    card_type_id: null,
    bank_account_id: null,
});

const primaryAccountId = computed(() => props.accounts.find((a) => a.is_primary)?.id ?? props.accounts[0]?.id ?? null);

const activeCardTypes = computed(() => props.cardTypes.filter((type) => type));

watch(requestDialog, (open) => {
    if (open) {
        form.card_type_id = selectedType.value?.id ?? activeCardTypes.value[0]?.id ?? null;
        form.bank_account_id = primaryAccountId.value;
    }
});

const formatLimit = (value) => {
    if (!value && value !== 0) return '—';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const submitRequest = () => {
    form.post(route('cards.store'), {
        preserveScroll: true,
        onSuccess: () => {
            requestDialog.value = false;
            form.reset();
            selectedType.value = null;
        },
    });
};
</script>

<template>
    <DashboardLayout title="My Cards">
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="text-2xl font-semibold text-gray-900">Cards</p>
                    <p class="text-gray-500">Manage your virtual and physical cards.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Tag :value="`${cardStats.active} Active`" severity="success" rounded />
                    <Button
                        icon="pi pi-plus-circle"
                        label="Request New Card"
                        @click="requestDialog = true"
                    />
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <StatCard title="Total Cards" :value="cardStats.total" icon="pi-credit-card" color="indigo" />
                <StatCard title="Active" :value="cardStats.active" icon="pi-check-circle" color="emerald" />
                <StatCard title="Virtual" :value="cardStats.virtual" icon="pi-desktop" color="amber" />
                <StatCard title="Physical" :value="cardStats.physical" icon="pi-id-card" color="rose" />
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <DigitalCard
                    v-for="card in cards"
                    :key="card.uuid"
                    :card="card"
                    :show-actions="false"
                />
            </div>

            <!-- Empty State -->
            <div v-if="!cards.length" class="bg-white border border-dashed border-gray-200 rounded-xl p-8 text-center">
                <div class="text-4xl mb-3">💳</div>
                <p class="text-lg font-semibold text-gray-800">No cards yet</p>
                <p class="text-gray-500 mb-4">Request a new virtual or physical card to get started.</p>
                <Button icon="pi pi-plus" label="Request Card" @click="requestDialog = true" />
            </div>

            <!-- Card Types -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Available Card Types</h3>
                    <p class="text-sm text-gray-500">Configured by admin in Filament</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <Card v-for="type in activeCardTypes" :key="type.id" class="h-full shadow-sm">
                        <template #title>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-gray-900">{{ type.name }}</span>
                                <Tag :value="type.code" severity="info" />
                            </div>
                        </template>
                        <template #content>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <i class="pi pi-wallet text-indigo-500"></i>
                                    <span>Default limit: {{ formatLimit(type.default_limit_display) }}</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <Tag v-if="type.is_virtual" value="Virtual" severity="warning" />
                                    <Tag v-else value="Physical" />
                                    <Tag v-if="type.is_credit" value="Credit" severity="danger" />
                                    <Tag v-else value="Debit" severity="success" />
                                </div>
                                <Button
                                    label="Choose"
                                    size="small"
                                    class="mt-2"
                                    @click="() => { selectedType = type; requestDialog = true; }"
                                />
                            </div>
                        </template>
                    </Card>
                </div>
                <Message v-if="!activeCardTypes.length" severity="warn" icon="pi pi-info-circle" :closable="false">
                    No active card types are configured by admin yet.
                </Message>
            </div>
        </div>

        <!-- Request Card Dialog -->
        <Dialog
            v-model:visible="requestDialog"
            modal
            header="Request a Card"
            :style="{ width: '30rem' }"
            :breakpoints="{ '960px': '85vw', '640px': '95vw' }"
        >
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm text-gray-700">Card Type</label>
                    <Dropdown
                        v-model="form.card_type_id"
                        :options="activeCardTypes"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select card type"
                        class="w-full"
                    />
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-700">Linked Account</label>
                    <Dropdown
                        v-model="form.bank_account_id"
                        :options="accounts"
                        optionLabel="number"
                        optionValue="id"
                        placeholder="Select bank account"
                        class="w-full"
                    >
                        <template #option="{ option }">
                            <div class="flex justify-between w-full">
                                <span class="font-medium">{{ option.number }}</span>
                                <span class="text-gray-500 text-sm">{{ option.currency }}</span>
                            </div>
                        </template>
                    </Dropdown>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Cancel" severity="secondary" outlined @click="requestDialog = false" />
                    <Button
                        label="Submit Request"
                        icon="pi pi-send"
                        :loading="form.processing"
                        :disabled="!form.card_type_id || !form.bank_account_id"
                        @click="submitRequest"
                    />
                </div>
            </div>
        </Dialog>
    </DashboardLayout>
</template>
