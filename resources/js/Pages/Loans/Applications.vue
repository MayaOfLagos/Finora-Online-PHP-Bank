<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';

const props = defineProps({
    applications: Object,
});

const selectedApplication = ref(null);
const detailsVisible = ref(false);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const getSeverity = (status) => {
    const severities = {
        pending: 'warning',
        under_review: 'info',
        approved: 'success',
        rejected: 'danger',
        disbursed: 'success',
        active: 'primary',
        closed: 'contrast',
        defaulted: 'danger',
    };
    return severities[status] || 'info';
};

const viewDetails = (application) => {
    selectedApplication.value = application;
    detailsVisible.value = true;
};

const browsePrograms = () => {
    router.visit('/loans/programs');
};
</script>

<template>
    <DashboardLayout title="My Loan Applications">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Loan Applications</h2>
                    <p class="text-gray-600 mt-1">Track your loan application status</p>
                </div>
                <Button 
                    label="Apply for New Loan" 
                    icon="pi pi-plus"
                    @click="browsePrograms"
                />
            </div>

            <!-- Applications Table -->
            <Card>
                <template #content>
                    <DataTable 
                        :value="applications.data" 
                        :paginator="true"
                        :rows="10"
                        dataKey="id"
                        :rowHover="true"
                        class="p-datatable-sm"
                    >
                        <template #empty>
                            <div class="text-center py-8 text-gray-500">
                                <i class="pi pi-inbox text-4xl mb-3"></i>
                                <p>No loan applications found</p>
                                <Button 
                                    label="Apply for a Loan" 
                                    icon="pi pi-plus"
                                    class="mt-4"
                                    @click="browsePrograms"
                                />
                            </div>
                        </template>

                        <Column field="reference_number" header="Reference" :sortable="true">
                            <template #body="slotProps">
                                <div class="font-mono text-sm font-semibold">
                                    {{ slotProps.data.reference_number }}
                                </div>
                            </template>
                        </Column>

                        <Column field="loan_type" header="Loan Type" :sortable="true">
                            <template #body="slotProps">
                                <div class="font-medium">{{ slotProps.data.loan_type }}</div>
                            </template>
                        </Column>

                        <Column field="amount" header="Amount" :sortable="true">
                            <template #body="slotProps">
                                <div class="font-semibold">
                                    {{ formatCurrency(slotProps.data.amount) }}
                                </div>
                            </template>
                        </Column>

                        <Column field="term_months" header="Term" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.term_months }} months
                            </template>
                        </Column>

                        <Column field="interest_rate" header="Rate" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.interest_rate }}%
                            </template>
                        </Column>

                        <Column field="monthly_payment" header="Monthly Payment" :sortable="true">
                            <template #body="slotProps">
                                {{ formatCurrency(slotProps.data.monthly_payment) }}
                            </template>
                        </Column>

                        <Column field="status" header="Status" :sortable="true">
                            <template #body="slotProps">
                                <Tag 
                                    :value="slotProps.data.status_label" 
                                    :severity="getSeverity(slotProps.data.status)"
                                />
                            </template>
                        </Column>

                        <Column field="created_at" header="Applied On" :sortable="true">
                            <template #body="slotProps">
                                <div class="text-sm">{{ slotProps.data.created_at }}</div>
                            </template>
                        </Column>

                        <Column header="Actions" :exportable="false">
                            <template #body="slotProps">
                                <Button 
                                    icon="pi pi-eye" 
                                    size="small"
                                    text
                                    rounded
                                    severity="info"
                                    @click="viewDetails(slotProps.data)"
                                    v-tooltip.top="'View Details'"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Application Details Dialog -->
        <Dialog 
            v-model:visible="detailsVisible" 
            modal 
            :header="`Application ${selectedApplication?.reference_number}`"
            :style="{ width: '50rem' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        >
            <div v-if="selectedApplication" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Status</label>
                        <div class="mt-1">
                            <Tag 
                                :value="selectedApplication.status_label" 
                                :severity="getSeverity(selectedApplication.status)"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Loan Type</label>
                        <p class="font-semibold mt-1">{{ selectedApplication.loan_type }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Loan Amount</label>
                        <p class="font-semibold text-lg mt-1">{{ formatCurrency(selectedApplication.amount) }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Term</label>
                        <p class="font-semibold mt-1">{{ selectedApplication.term_months }} months</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Interest Rate</label>
                        <p class="font-semibold text-blue-600 mt-1">{{ selectedApplication.interest_rate }}%</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Monthly Payment</label>
                        <p class="font-semibold mt-1">{{ formatCurrency(selectedApplication.monthly_payment) }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Total Payable</label>
                        <p class="font-semibold mt-1">{{ formatCurrency(selectedApplication.total_payable) }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Applied On</label>
                        <p class="font-semibold mt-1">{{ selectedApplication.created_at }}</p>
                    </div>
                </div>

                <div v-if="selectedApplication.purpose">
                    <label class="text-sm text-gray-600">Purpose</label>
                    <p class="mt-1 text-gray-800">{{ selectedApplication.purpose }}</p>
                </div>

                <div v-if="selectedApplication.approved_at">
                    <label class="text-sm text-gray-600">Approved On</label>
                    <p class="mt-1 text-green-600 font-semibold">{{ selectedApplication.approved_at }}</p>
                </div>

                <div v-if="selectedApplication.rejection_reason" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <label class="text-sm font-semibold text-red-800">Rejection Reason</label>
                    <p class="mt-1 text-red-700">{{ selectedApplication.rejection_reason }}</p>
                </div>

                <div v-if="selectedApplication.loan_id" class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-800">
                        <i class="pi pi-check-circle mr-2"></i>
                        Your loan has been disbursed and is now active.
                    </p>
                    <Button 
                        label="View Loan Details" 
                        icon="pi pi-arrow-right"
                        size="small"
                        class="mt-3"
                        @click="router.visit('/loans')"
                    />
                </div>
            </div>
        </Dialog>
    </DashboardLayout>
</template>
