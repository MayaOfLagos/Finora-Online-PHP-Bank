<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Message from 'primevue/message';

const props = defineProps({
    loans: Object,
    stats: Object,
});

const selectedLoan = ref(null);
const detailsVisible = ref(false);
const paymentVisible = ref(false);

const paymentForm = useForm({
    amount: null,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const formatDate = (dateString) => {
    return dateString;
};

const getSeverity = (status) => {
    const severities = {
        active: 'success',
        disbursed: 'info',
        closed: 'contrast',
        defaulted: 'danger',
    };
    return severities[status] || 'info';
};

const progressPercentage = computed(() => {
    if (!selectedLoan.value) return 0;
    const paid = selectedLoan.value.principal_amount - selectedLoan.value.outstanding_balance;
    return Math.round((paid / selectedLoan.value.principal_amount) * 100);
});

const viewDetails = (loan) => {
    selectedLoan.value = loan;
    detailsVisible.value = true;
};

const makePayment = (loan) => {
    selectedLoan.value = loan;
    paymentForm.amount = loan.monthly_payment;
    paymentVisible.value = true;
};

const submitPayment = () => {
    // Placeholder - would need backend route
    console.log('Payment submitted:', paymentForm.amount);
    paymentVisible.value = false;
    // In real implementation:
    // paymentForm.post(route('loans.repay', selectedLoan.value.uuid));
};
</script>

<template>
    <DashboardLayout title="My Loans">
        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">Active Loans</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total_loans }}</p>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">Total Outstanding</p>
                            <p class="text-3xl font-bold text-red-600">{{ formatCurrency(stats.total_outstanding) }}</p>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">Next Payment</p>
                            <p class="text-3xl font-bold text-blue-600">{{ formatCurrency(stats.next_payment) }}</p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <Button 
                    label="Browse Loan Programs" 
                    icon="pi pi-book"
                    @click="router.visit('/loans/programs')"
                />
                <Button 
                    label="My Applications" 
                    icon="pi pi-file"
                    severity="secondary"
                    @click="router.visit('/loans/applications')"
                />
            </div>

            <!-- Loans Table -->
            <Card>
                <template #title>
                    <div class="flex justify-between items-center">
                        <span>Active Loans</span>
                    </div>
                </template>
                <template #content>
                    <DataTable 
                        :value="loans.data" 
                        :paginator="true"
                        :rows="10"
                        dataKey="id"
                        :rowHover="true"
                        :loading="false"
                        class="p-datatable-sm"
                    >
                        <template #empty>
                            <div class="text-center py-8 text-gray-500">
                                <i class="pi pi-inbox text-4xl mb-3"></i>
                                <p>No active loans found</p>
                                <Button 
                                    label="Apply for a Loan" 
                                    icon="pi pi-plus"
                                    class="mt-4"
                                    @click="router.visit('/loans/programs')"
                                />
                            </div>
                        </template>

                        <Column field="loan_type" header="Loan Type" :sortable="true">
                            <template #body="slotProps">
                                <div class="font-medium">{{ slotProps.data.loan_type }}</div>
                                <div class="text-xs text-gray-500">{{ slotProps.data.account_number }}</div>
                            </template>
                        </Column>

                        <Column field="principal_amount" header="Principal" :sortable="true">
                            <template #body="slotProps">
                                {{ formatCurrency(slotProps.data.principal_amount) }}
                            </template>
                        </Column>

                        <Column field="outstanding_balance" header="Outstanding" :sortable="true">
                            <template #body="slotProps">
                                <div class="font-semibold text-red-600">
                                    {{ formatCurrency(slotProps.data.outstanding_balance) }}
                                </div>
                            </template>
                        </Column>

                        <Column field="monthly_payment" header="Monthly Payment" :sortable="true">
                            <template #body="slotProps">
                                {{ formatCurrency(slotProps.data.monthly_payment) }}
                            </template>
                        </Column>

                        <Column field="interest_rate" header="Rate" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.interest_rate }}%
                            </template>
                        </Column>

                        <Column field="next_payment_date" header="Next Payment" :sortable="true">
                            <template #body="slotProps">
                                <div class="text-sm">{{ slotProps.data.next_payment_date }}</div>
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

                        <Column header="Actions" :exportable="false">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button 
                                        icon="pi pi-eye" 
                                        size="small"
                                        text
                                        rounded
                                        severity="info"
                                        @click="viewDetails(slotProps.data)"
                                        v-tooltip.top="'View Details'"
                                    />
                                    <Button 
                                        icon="pi pi-dollar" 
                                        size="small"
                                        text
                                        rounded
                                        severity="success"
                                        @click="makePayment(slotProps.data)"
                                        v-tooltip.top="'Make Payment'"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Loan Details Dialog -->
        <Dialog 
            v-model:visible="detailsVisible" 
            modal 
            :header="`Loan Details`"
            :style="{ width: '50rem' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        >
            <div v-if="selectedLoan" class="space-y-6">
                <!-- Loan Progress -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Repayment Progress</span>
                        <span class="text-sm font-semibold text-blue-900">{{ progressPercentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div 
                            class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                            :style="{ width: progressPercentage + '%' }"
                        ></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-600">
                        <span>{{ formatCurrency(selectedLoan.principal_amount - selectedLoan.outstanding_balance) }} paid</span>
                        <span>{{ formatCurrency(selectedLoan.outstanding_balance) }} remaining</span>
                    </div>
                </div>

                <!-- Loan Information Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Loan Type</label>
                        <p class="font-semibold mt-1">{{ selectedLoan.loan_type }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Account</label>
                        <p class="font-semibold mt-1">{{ selectedLoan.account_number }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Principal Amount</label>
                        <p class="font-semibold text-lg mt-1">{{ formatCurrency(selectedLoan.principal_amount) }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Outstanding Balance</label>
                        <p class="font-semibold text-lg text-red-600 mt-1">{{ formatCurrency(selectedLoan.outstanding_balance) }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Interest Rate</label>
                        <p class="font-semibold text-blue-600 mt-1">{{ selectedLoan.interest_rate }}% APR</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Monthly Payment</label>
                        <p class="font-semibold mt-1">{{ formatCurrency(selectedLoan.monthly_payment) }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Next Payment Due</label>
                        <p class="font-semibold mt-1">{{ selectedLoan.next_payment_date }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Final Payment Date</label>
                        <p class="font-semibold mt-1">{{ selectedLoan.final_payment_date }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Disbursed On</label>
                        <p class="font-semibold mt-1">{{ selectedLoan.disbursed_at }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Status</label>
                        <div class="mt-1">
                            <Tag 
                                :value="selectedLoan.status_label" 
                                :severity="getSeverity(selectedLoan.status)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4 border-t">
                    <Button 
                        label="Make Payment" 
                        icon="pi pi-dollar"
                        @click="detailsVisible = false; makePayment(selectedLoan)"
                    />
                    <Button 
                        label="Close" 
                        severity="secondary"
                        outlined
                        @click="detailsVisible = false"
                    />
                </div>
            </div>
        </Dialog>

        <!-- Payment Dialog -->
        <Dialog 
            v-model:visible="paymentVisible" 
            modal 
            header="Make Loan Payment"
            :style="{ width: '35rem' }"
            :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        >
            <div v-if="selectedLoan" class="space-y-4">
                <Message severity="info" :closable="false">
                    <p class="text-sm">
                        Monthly Payment: <strong>{{ formatCurrency(selectedLoan.monthly_payment) }}</strong>
                    </p>
                    <p class="text-sm">
                        Outstanding Balance: <strong>{{ formatCurrency(selectedLoan.outstanding_balance) }}</strong>
                    </p>
                </Message>

                <div>
                    <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Amount
                    </label>
                    <InputNumber
                        id="payment_amount"
                        v-model="paymentForm.amount"
                        mode="currency"
                        currency="USD"
                        :min="selectedLoan.monthly_payment"
                        :max="selectedLoan.outstanding_balance"
                        class="w-full"
                    />
                    <small class="text-gray-500">
                        Minimum: {{ formatCurrency(selectedLoan.monthly_payment) }} | 
                        Maximum: {{ formatCurrency(selectedLoan.outstanding_balance) }}
                    </small>
                </div>

                <Message severity="warn" :closable="false">
                    <p class="text-sm">
                        Note: This is a demo feature. In production, this would integrate with your payment gateway.
                    </p>
                </Message>

                <div class="flex gap-3 pt-4">
                    <Button 
                        label="Submit Payment" 
                        icon="pi pi-check"
                        @click="submitPayment"
                        :disabled="!paymentForm.amount || paymentForm.amount < selectedLoan.monthly_payment"
                    />
                    <Button 
                        label="Cancel" 
                        severity="secondary"
                        outlined
                        @click="paymentVisible = false"
                    />
                </div>
            </div>
        </Dialog>
    </DashboardLayout>
</template>
