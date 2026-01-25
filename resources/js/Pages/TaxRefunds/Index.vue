<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import Message from 'primevue/message';

const props = defineProps({
    refunds: { type: Array, default: () => [] },
});

const sampleRefunds = props.refunds.length ? props.refunds : [
    { id: 1, title: 'IRS Refund 2024', status: 'processing', amount: 1245.75, submitted_at: '2026-01-10', notes: 'Identity verified via ID.me' },
    { id: 2, title: 'State Refund 2024', status: 'pending', amount: 420.00, submitted_at: '2026-01-08', notes: 'Waiting on employer W-2 upload' },
];

const statusMeta = {
    pending: { label: 'Pending', severity: 'warning' },
    processing: { label: 'Processing', severity: 'info' },
    completed: { label: 'Completed', severity: 'success' },
    rejected: { label: 'Rejected', severity: 'danger' },
};
</script>

<template>
    <DashboardLayout title="Tax Refunds">
        <div class="space-y-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tax Refunds</h1>
                <p class="text-gray-600 dark:text-gray-400">Track refund status and identity verification in one place.</p>
            </div>

            <Card>
                <template #title>Verification</template>
                <template #content>
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-gray-700 dark:text-gray-300">Verify your identity with ID.me to speed up refunds.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">We will guide you through a secure verification flow.</p>
                        </div>
                        <div class="flex gap-2">
                            <Button label="Start ID.me" icon="pi pi-shield" />
                            <Button label="Upload Docs" icon="pi pi-upload" severity="secondary" outlined />
                        </div>
                    </div>
                </template>
            </Card>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <template #title>Refund Status</template>
                    <template #content>
                        <div class="space-y-3">
                            <div v-for="refund in sampleRefunds" :key="refund.id" class="p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800">
                                <div class="flex items-start justify-between">
                                    <div class="space-y-1">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ refund.submitted_at }}</p>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ refund.title }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ refund.notes }}</p>
                                    </div>
                                    <div class="text-right space-y-2">
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">${{ refund.amount.toFixed(2) }}</p>
                                        <Tag :value="statusMeta[refund.status]?.label || refund.status" :severity="statusMeta[refund.status]?.severity || 'info'" />
                                    </div>
                                </div>
                            </div>
                            <p v-if="!sampleRefunds.length" class="text-sm text-gray-600 dark:text-gray-400">No refunds yet.</p>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Timeline</template>
                    <template #content>
                        <Timeline :value="sampleRefunds" align="left">
                            <template #marker="slotProps">
                                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600">
                                    <i class="pi pi-clock text-xs" />
                                </span>
                            </template>
                            <template #content="slotProps">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ slotProps.item.title }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ slotProps.item.submitted_at }}</p>
                                    <p class="text-xs text-gray-500">Status: {{ statusMeta[slotProps.item.status]?.label || slotProps.item.status }}</p>
                                </div>
                            </template>
                        </Timeline>
                        <Message severity="secondary" :closable="false" class="mt-3">Detailed IRS sync coming soon.</Message>
                    </template>
                </Card>
            </div>
        </div>
    </DashboardLayout>
</template>
