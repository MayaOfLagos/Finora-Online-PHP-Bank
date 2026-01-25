<script setup>
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

const props = defineProps({
    programs: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const applyForLoan = (program) => {
    router.visit(`/loans/apply/${program.id}`);
};
</script>

<template>
    <DashboardLayout title="Loan Programs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Available Loan Programs</h2>
                    <p class="text-gray-600 mt-1">Choose the perfect loan for your needs</p>
                </div>
                <Button 
                    label="My Applications" 
                    icon="pi pi-file"
                    severity="secondary"
                    @click="router.visit('/loans/applications')"
                />
            </div>

            <!-- Programs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="program in programs" :key="program.id" class="hover:shadow-lg transition-shadow">
                    <template #header>
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                            <h3 class="text-xl font-bold">{{ program.name }}</h3>
                            <p class="text-sm opacity-90 mt-1">{{ program.code }}</p>
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <p class="text-gray-600 text-sm">{{ program.description }}</p>

                            <div class="border-t pt-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Loan Amount</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ formatCurrency(program.min_amount) }} - {{ formatCurrency(program.max_amount) }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Loan Term</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ program.min_term_months }} - {{ program.max_term_months }} months
                                    </span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Interest Rate</span>
                                    <span class="font-bold text-blue-600 text-lg">
                                        {{ program.interest_rate }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #footer>
                        <Button 
                            label="Apply Now" 
                            icon="pi pi-arrow-right"
                            class="w-full"
                            @click="applyForLoan(program)"
                        />
                    </template>
                </Card>
            </div>

            <!-- Info Section -->
            <Card class="bg-blue-50 border-blue-200">
                <template #content>
                    <div class="flex items-start gap-3">
                        <i class="pi pi-info-circle text-blue-600 text-2xl"></i>
                        <div>
                            <h4 class="font-semibold text-blue-900 mb-2">Before You Apply</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Ensure you have a valid bank account with sufficient history</li>
                                <li>• Prepare documents showing proof of income</li>
                                <li>• Check your eligibility before applying</li>
                                <li>• Applications are typically reviewed within 1-3 business days</li>
                            </ul>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
