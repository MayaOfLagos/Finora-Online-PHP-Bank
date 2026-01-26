<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import Message from 'primevue/message';

const props = defineProps({
    refunds: { type: Array, default: () => [] },
    pendingVerification: { type: Object, default: null },
});

const page = usePage();
const flash = computed(() => page.props.flash);

const refundsList = computed(() => {
    if (props.refunds.length) return props.refunds;
    // Sample data for display when no refunds exist
    return [];
});

const formatCurrency = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};

const statusMeta = {
    pending: { label: 'Pending', severity: 'warning' },
    processing: { label: 'Processing', severity: 'info' },
    approved: { label: 'Approved', severity: 'success' },
    completed: { label: 'Completed', severity: 'success' },
    rejected: { label: 'Rejected', severity: 'danger' },
};

const startIdMe = () => {
    router.visit(route('tax-refunds.idme'));
};

const uploadDocs = () => {
    router.visit(route('tax-refunds.documents'));
};
</script>

<template>
    <DashboardLayout title="Tax Refunds">
        <div class="space-y-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tax Refunds</h1>
                <p class="text-gray-600 dark:text-gray-400">Track refund status and identity verification in one place.</p>
            </div>

            <!-- Success Message -->
            <Message v-if="flash?.success" severity="success" :closable="true" class="mb-4">
                {{ flash.success }}
            </Message>

            <!-- Pending Verification Alert -->
            <Message v-if="pendingVerification" severity="info" :closable="false" class="mb-4">
                <div class="flex items-center justify-between">
                    <span>You have a pending verification for {{ pendingVerification.reference_number }}. Please wait for admin review.</span>
                </div>
            </Message>

            <Card>
                <template #title>Identity Verification</template>
                <template #content>
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-gray-700 dark:text-gray-300">Verify your identity to claim your tax refund.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Choose ID.me for faster verification, or upload documents manually.</p>
                        </div>
                        <div class="flex gap-2">
                            <Button 
                                label="Start ID.me" 
                                icon="pi pi-shield" 
                                @click="startIdMe"
                            />
                            <Button 
                                label="Upload Docs" 
                                icon="pi pi-upload" 
                                severity="secondary" 
                                outlined 
                                @click="uploadDocs"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <template #title>Refund Status</template>
                    <template #content>
                        <div class="space-y-3">
                            <div v-for="refund in refundsList" :key="refund.uuid" class="p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800">
                                <div class="flex items-start justify-between">
                                    <div class="space-y-1">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ refund.submitted_at }}</p>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ refund.filing_status?.replace(/_/g, ' ') || 'Tax Refund' }} - {{ refund.tax_year }}
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">Ref: {{ refund.reference_number }}</p>
                                        <p v-if="refund.idme_verified" class="text-xs text-green-600 dark:text-green-400">
                                            <i class="pi pi-check-circle mr-1"></i> ID Verified {{ refund.idme_verified_at }}
                                        </p>
                                        <p v-if="refund.rejection_reason" class="text-xs text-red-600 dark:text-red-400">
                                            Reason: {{ refund.rejection_reason }}
                                        </p>
                                    </div>
                                    <div class="text-right space-y-2">
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ formatCurrency(refund.refund_amount, refund.currency) }}
                                        </p>
                                        <Tag 
                                            :value="statusMeta[refund.status]?.label || refund.status_label || refund.status" 
                                            :severity="statusMeta[refund.status]?.severity || 'info'" 
                                        />
                                    </div>
                                </div>
                            </div>
                            <div v-if="!refundsList.length" class="text-center py-8">
                                <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-600 dark:text-gray-400">No tax refund claims yet.</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500">Start by verifying your identity above.</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Timeline</template>
                    <template #content>
                        <div v-if="refundsList.length">
                            <Timeline :value="refundsList" align="left">
                                <template #marker="slotProps">
                                    <span 
                                        class="flex items-center justify-center w-6 h-6 rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-600': slotProps.item.status === 'completed' || slotProps.item.status === 'approved',
                                            'bg-blue-100 text-blue-600': slotProps.item.status === 'processing',
                                            'bg-yellow-100 text-yellow-600': slotProps.item.status === 'pending',
                                            'bg-red-100 text-red-600': slotProps.item.status === 'rejected',
                                        }"
                                    >
                                        <i 
                                            class="text-xs"
                                            :class="{
                                                'pi pi-check': slotProps.item.status === 'completed' || slotProps.item.status === 'approved',
                                                'pi pi-spin pi-spinner': slotProps.item.status === 'processing',
                                                'pi pi-clock': slotProps.item.status === 'pending',
                                                'pi pi-times': slotProps.item.status === 'rejected',
                                            }"
                                        />
                                    </span>
                                </template>
                                <template #content="slotProps">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ formatCurrency(slotProps.item.refund_amount, slotProps.item.currency) }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ slotProps.item.tax_year }} Tax Year</p>
                                        <p class="text-xs text-gray-500">{{ slotProps.item.submitted_at }}</p>
                                    </div>
                                </template>
                            </Timeline>
                        </div>
                        <div v-else class="text-center py-6 text-gray-500">
                            <p class="text-sm">Timeline will appear once you submit a refund claim.</p>
                        </div>
                        <Message severity="secondary" :closable="false" class="mt-3">
                            <span class="text-xs">Refunds are typically processed within 21 days after identity verification.</span>
                        </Message>
                    </template>
                </Card>
            </div>
        </div>
    </DashboardLayout>
</template>
