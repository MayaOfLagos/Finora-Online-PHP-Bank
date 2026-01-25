<script setup>
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import Message from 'primevue/message';

const props = defineProps({
    application: {
        type: Object,
        required: true,
    },
});

const statusSeverityMap = {
    'Pending': 'warning',
    'Under Review': 'info',
    'Approved': 'success',
    'Rejected': 'danger',
    'Disbursed': 'success',
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const formatFileSize = (bytes) => {
    if (bytes === null || bytes === undefined) return '—';
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const goBack = () => {
    router.visit('/grants/applications');
};

const events = [
    { status: 'Pending', date: props.application.created_at, icon: 'pi pi-file' },
    ...(props.application.status === 'Under Review' || ['Approved', 'Rejected', 'Disbursed'].includes(props.application.status) ? [{ status: 'Under Review', date: 'In Progress', icon: 'pi pi-clock' }] : []),
    ...(props.application.status === 'Approved' || props.application.status === 'Disbursed' ? [{ status: 'Approved', date: props.application.approved_at, icon: 'pi pi-check' }] : []),
    ...(props.application.status === 'Rejected' ? [{ status: 'Rejected', date: props.application.approved_at, icon: 'pi pi-times' }] : []),
    ...(props.application.status === 'Disbursed' ? [{ status: 'Disbursed', date: props.application.disbursement?.disbursement_date, icon: 'pi pi-money-bill' }] : []),
];
</script>

<template>
    <DashboardLayout title="Application Details">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Application Details</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Reference: {{ application.reference_number }}</p>
                </div>
                <Button 
                    label="Back to Applications" 
                    severity="secondary"
                    icon="pi pi-arrow-left"
                    @click="goBack"
                />
            </div>

            <!-- Status Message -->
            <Message v-if="application.status === 'Rejected'" severity="error">
                <p class="text-sm text-gray-900 dark:text-gray-100">
                    <strong>Application Rejected</strong>
                    <br>
                    {{ application.rejection_reason || 'No reason provided' }}
                </p>
            </Message>

            <Message v-else-if="application.status === 'Approved'" severity="success">
                <p class="text-sm text-gray-900 dark:text-gray-100">
                    <strong>Congratulations!</strong> Your application has been approved. The grant amount will be disbursed to your account.
                </p>
            </Message>

            <Message v-else-if="application.status === 'Disbursed'" severity="success">
                <p class="text-sm text-gray-900 dark:text-gray-100">
                    <strong>Funds Disbursed</strong> on {{ application.disbursement?.disbursement_date }}
                </p>
            </Message>

            <Message v-else severity="info">
                <p class="text-sm text-gray-900 dark:text-gray-100">
                    <strong>Application Status:</strong> {{ application.status }}. We are reviewing your application and will notify you of the outcome.
                </p>
            </Message>

            <!-- Application Info -->
            <Card>
                <template #header>
                    <div class="bg-gradient-to-r from-indigo-500 to-blue-600 p-6 text-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-xl font-bold">{{ application.grant_program.name }}</h3>
                                <p class="text-indigo-100 mt-1">{{ application.grant_program.description }}</p>
                            </div>
                            <Tag 
                                :value="application.status_label" 
                                :severity="statusSeverityMap[application.status]"
                                class="text-sm"
                            />
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-400">Grant Amount</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                                {{ formatCurrency(application.grant_program.amount_display) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-400">Application Date</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-2">{{ application.created_at }}</p>
                        </div>
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-400">Decision Date</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-2">
                                {{ application.approved_at || 'Pending' }}
                            </p>
                        </div>
                        <div v-if="application.disbursement" class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30">
                            <p class="text-xs text-green-600 dark:text-green-400">Disbursed Amount</p>
                            <p class="text-2xl font-bold text-green-900 dark:text-green-200 mt-2">
                                {{ formatCurrency(application.disbursement.amount_display) }}
                            </p>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Timeline -->
            <Card>
                <template #content>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4">Application Timeline</h4>
                    <Timeline :value="events" layout="vertical" align="left" class="custom-timeline">
                        <template #content="slotProps">
                            <Card class="mb-3" style="width: 100%">
                                <template #content>
                                    <div class="flex items-center gap-2">
                                        <i :class="slotProps.item.icon"></i>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ slotProps.item.status }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ slotProps.item.date }}</p>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </template>
                    </Timeline>
                </template>
            </Card>

            <!-- Documents -->
            <Card v-if="application.documents && application.documents.length > 0">
                <template #content>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4">Uploaded Documents</h4>
                    <div class="space-y-2">
                        <div
                            v-for="document in application.documents"
                            :key="document.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600"
                        >
                            <div class="flex items-center gap-3 flex-1">
                                <i class="pi pi-file text-blue-500"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ document.file_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatFileSize(document.file_size) }}</p>
                                </div>
                            </div>
                            <a
                                :href="`/storage/${document.file_path}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center justify-center w-10 h-10 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                title="Download document"
                            >
                                <i class="pi pi-download text-lg"></i>
                            </a>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Empty State for Documents -->
            <div v-else class="p-8 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <i class="pi pi-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                <p class="text-gray-600 dark:text-gray-400">No documents uploaded</p>
            </div>
        </div>
    </DashboardLayout>
</template>
