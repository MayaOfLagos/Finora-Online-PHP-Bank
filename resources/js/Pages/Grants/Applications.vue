<script setup>
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Message from 'primevue/message';

const props = defineProps({
    applications: {
        type: Array,
        default: () => [],
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

const viewDetails = (application) => {
    router.visit(`/grants/applications/${application.uuid}`);
};

const browseMeReGrants = () => {
    router.visit('/grants');
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <DashboardLayout title="My Grant Applications">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">My Applications</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Track the status of your grant applications</p>
                </div>
                <Button 
                    label="Browse Grants" 
                    icon="pi pi-arrow-left"
                    severity="secondary"
                    @click="browseMeReGrants"
                />
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card class="p-0">
                    <template #content>
                        <div class="flex items-center gap-4 p-6">
                            <div class="p-4 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <i class="pi pi-file text-2xl text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Applications</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ applications.length }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="p-0">
                    <template #content>
                        <div class="flex items-center gap-4 p-6">
                            <div class="p-4 rounded-lg bg-yellow-100 dark:bg-yellow-900/30">
                                <i class="pi pi-hourglass text-2xl text-yellow-600 dark:text-yellow-400"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Pending Review</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ applications.filter(a => a.status_label === 'Pending' || a.status_label === 'Under Review').length }}
                                </p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="p-0">
                    <template #content>
                        <div class="flex items-center gap-4 p-6">
                            <div class="p-4 rounded-lg bg-green-100 dark:bg-green-900/30">
                                <i class="pi pi-check-circle text-2xl text-green-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Approved</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ applications.filter(a => a.status_label === 'Approved' || a.status_label === 'Disbursed').length }}
                                </p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="p-0">
                    <template #content>
                        <div class="flex items-center gap-4 p-6">
                            <div class="p-4 rounded-lg bg-red-100 dark:bg-red-900/30">
                                <i class="pi pi-times-circle text-2xl text-red-600 dark:text-red-400"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Rejected</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ applications.filter(a => a.status_label === 'Rejected').length }}
                                </p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Applications Table -->
            <Card v-if="applications.length > 0">
                <template #content>
                    <DataTable
                        :value="applications"
                        stripedRows
                        responsiveLayout="scroll"
                        class="text-sm"
                    >
                        <Column field="grant_program.name" header="Grant Program" class="min-w-48">
                            <template #body="{ data }">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ data.grant_program.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ data.id }}</p>
                                </div>
                            </template>
                        </Column>

                        <Column field="status" header="Status" class="min-w-32">
                            <template #body="{ data }">
                                <Tag 
                                    :value="data.status_label" 
                                    :severity="statusSeverityMap[data.status_label] || 'info'"
                                    class="text-xs"
                                />
                            </template>
                        </Column>

                        <Column field="grant_program.amount_display" header="Grant Amount" class="min-w-32">
                            <template #body="{ data }">
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(data.grant_program.amount_display) }}
                                </span>
                            </template>
                        </Column>

                        <Column field="created_at" header="Applied On" class="min-w-28">
                            <template #body="{ data }">
                                {{ formatDate(data.created_at) }}
                            </template>
                        </Column>

                        <Column field="approved_at" header="Decision Date" class="min-w-28">
                            <template #body="{ data }">
                                {{ formatDate(data.approved_at) }}
                            </template>
                        </Column>

                        <Column header="Action" class="min-w-24 text-center">
                            <template #body="{ data }">
                                <Button
                                    label="View"
                                    icon="pi pi-eye"
                                    severity="secondary"
                                    text
                                    @click="viewDetails(data)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <!-- Empty State -->
            <div v-else class="p-12 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <i class="pi pi-inbox text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">No Applications Yet</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-2 mb-6">Browse available grants and apply to get started</p>
                <Button 
                    label="Browse Grants" 
                    icon="pi pi-arrow-right"
                    @click="browseMeReGrants"
                />
            </div>
        </div>
    </DashboardLayout>
</template>
