<script setup>
import { computed, ref } from 'vue';
import { useForm, router as inertiaRouter } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Timeline from 'primevue/timeline';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    sentRequests: { type: Object, default: () => ({ data: [] }) },
    receivedRequests: { type: Object, default: () => ({ data: [] }) },
});

const toast = useToast();

const currencyOptions = [
    { label: 'USD', value: 'USD' },
    { label: 'EUR', value: 'EUR' },
    { label: 'GBP', value: 'GBP' },
    { label: 'NGN', value: 'NGN' },
];

const form = useForm({
    responder_email: '',
    amount: null,
    currency: 'USD',
    reason: '',
    type: 'personal',
    expires_at: '',
});

const requestTypes = [
    { label: 'Personal', value: 'personal' },
    { label: 'Business', value: 'business' },
    { label: 'Other', value: 'other' },
];

const formatAmount = (value, currency = 'USD') => {
    if (value === undefined || value === null) return `${currency} 0.00`;
    return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value / 100);
};

const formatStatus = (status) => {
    const map = {
        pending: { label: 'Pending', severity: 'warning' },
        accepted: { label: 'Accepted', severity: 'success' },
        rejected: { label: 'Rejected', severity: 'danger' },
        cancelled: { label: 'Cancelled', severity: 'secondary' },
    };
    return map[status] || { label: status, severity: 'info' };
};

const submitRequest = () => {
    form.transform((data) => ({
        ...data,
        amount: Math.round((data.amount || 0) * 100), // Convert dollars to cents
    })).post('/money-requests', {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Sent', detail: 'Money request sent.', life: 2500 });
            form.reset('responder_email', 'amount', 'reason');
        },
    });
};

const handleAction = (id, action, payload = {}) => {
    const endpoints = {
        accept: `/money-requests/${id}/accept`,
        reject: `/money-requests/${id}/reject`,
        cancel: `/money-requests/${id}`,
    };

    const method = action === 'cancel' ? 'delete' : 'post';

    if (action === 'reject' && !payload.rejection_reason) {
        const reason = window.prompt('Why are you rejecting this request?');
        if (!reason) return;
        payload.rejection_reason = reason;
    }

    inertiaRouter[method](endpoints[action], payload, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Updated', detail: 'Request updated.', life: 2500 }),
    });
};

const timelineEvents = computed(() => {
    const combined = [...(props.sentRequests.data || []), ...(props.receivedRequests.data || [])];
    return combined.slice(0, 6).map((item) => ({
        status: item.status,
        date: item.created_at,
        detail: `${item.type || 'personal'} · ${formatAmount(item.amount, item.currency)}`,
    }));
});
</script>

<template>
    <DashboardLayout title="Money Requests">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Money Requests</h1>
                    <p class="text-gray-600 dark:text-gray-400">Send or respond to payment requests quickly.</p>
                </div>
                <div class="flex gap-2">
                    <Button icon="pi pi-history" label="History" severity="secondary" text />
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <template #title>Request Money</template>
                    <template #content>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Recipient Email</label>
                                <InputText v-model="form.responder_email" placeholder="customer@example.com" class="w-full" />
                                <p v-if="form.errors.responder_email" class="text-sm text-red-500">{{ form.errors.responder_email }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Amount</label>
                                <InputNumber v-model="form.amount" mode="decimal" :min="1" :maxFractionDigits="2" class="w-full" placeholder="0.00" />
                                <p v-if="form.errors.amount" class="text-sm text-red-500">{{ form.errors.amount }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Currency</label>
                                <Dropdown v-model="form.currency" :options="currencyOptions" optionLabel="label" optionValue="value" class="w-full" />
                                <p v-if="form.errors.currency" class="text-sm text-red-500">{{ form.errors.currency }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Request Type</label>
                                <Dropdown v-model="form.type" :options="requestTypes" optionLabel="label" optionValue="value" class="w-full" />
                            </div>
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Reason</label>
                                <Textarea v-model="form.reason" rows="3" placeholder="What is this for?" class="w-full" />
                                <p v-if="form.errors.reason" class="text-sm text-red-500">{{ form.errors.reason }}</p>
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <Button label="Send Request" icon="pi pi-send" :loading="form.processing" @click="submitRequest" />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Recent Activity</template>
                    <template #content>
                        <Timeline :value="timelineEvents" align="left" class="w-full">
                            <template #marker="slotProps">
                                <span class="flex items-center justify-center w-6 h-6 rounded-full" :class="{
                                    'bg-amber-100 text-amber-600': slotProps.item.status === 'pending',
                                    'bg-emerald-100 text-emerald-600': slotProps.item.status === 'accepted',
                                    'bg-rose-100 text-rose-600': slotProps.item.status === 'rejected',
                                }">
                                    <i class="pi pi-circle-fill text-xs" />
                                </span>
                            </template>
                            <template #content="slotProps">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white capitalize">{{ slotProps.item.status }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ slotProps.item.detail }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">{{ slotProps.item.date }}</p>
                                </div>
                            </template>
                        </Timeline>
                        <p v-if="!timelineEvents.length" class="text-sm text-gray-600 dark:text-gray-400">No activity yet.</p>
                    </template>
                </Card>
            </div>

            <Card>
                <template #content>
                    <TabView>
                        <TabPanel header="Received Requests">
                            <div class="space-y-3">
                                <div v-for="request in receivedRequests.data" :key="request.id" class="flex items-start justify-between p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800">
                                    <div class="space-y-1">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">From {{ request.requester?.email || 'Unknown' }}</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatAmount(request.amount, request.currency) }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ request.reason }}</p>
                                        <div class="flex gap-2">
                                            <Tag :value="formatStatus(request.status).label" :severity="formatStatus(request.status).severity" />
                                            <Tag :value="request.type || 'personal'" severity="secondary" />
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Button v-if="request.status === 'pending'" label="Approve" icon="pi pi-check" size="small" @click="handleAction(request.id, 'accept')" />
                                        <Button v-if="request.status === 'pending'" label="Reject" icon="pi pi-times" size="small" severity="secondary" outlined @click="handleAction(request.id, 'reject')" />
                                    </div>
                                </div>
                                <p v-if="!receivedRequests.data.length" class="text-sm text-gray-600 dark:text-gray-400">No incoming requests.</p>
                            </div>
                        </TabPanel>
                        <TabPanel header="Sent Requests">
                            <div class="space-y-3">
                                <div v-for="request in sentRequests.data" :key="request.id" class="flex items-start justify-between p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800">
                                    <div class="space-y-1">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">To {{ request.responder?.email || 'Recipient' }}</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatAmount(request.amount, request.currency) }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ request.reason }}</p>
                                        <div class="flex gap-2">
                                            <Tag :value="formatStatus(request.status).label" :severity="formatStatus(request.status).severity" />
                                            <Tag :value="request.type || 'personal'" severity="secondary" />
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Button v-if="request.status === 'pending'" label="Cancel" icon="pi pi-ban" size="small" severity="secondary" outlined @click="handleAction(request.id, 'cancel')" />
                                    </div>
                                </div>
                                <p v-if="!sentRequests.data.length" class="text-sm text-gray-600 dark:text-gray-400">No sent requests.</p>
                            </div>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
