<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';
import Timeline from 'primevue/timeline';
import Dialog from 'primevue/dialog';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    verifications: Array,
    templates: Array,
    overallStatus: Object,
    isVerified: Boolean,
    kycLevel: Number,
});

const toast = useToast();
const page = usePage();
const showTemplateDialog = ref(false);
const selectedVerification = ref(null);
const showDetailsDialog = ref(false);

// Check for flash messages
const flashSuccess = computed(() => page.props.flash?.success);
const flashWarning = computed(() => page.props.flash?.warning);
const flashError = computed(() => page.props.flash?.error);

// Get status badge severity
const getStatusSeverity = (color) => {
    const severityMap = {
        success: 'success',
        warning: 'warn',
        danger: 'danger',
        secondary: 'secondary',
        info: 'info',
    };
    return severityMap[color] || 'info';
};

// Get status icon
const getStatusIcon = (status) => {
    const iconMap = {
        approved: 'pi pi-check-circle',
        pending: 'pi pi-clock',
        rejected: 'pi pi-times-circle',
        not_started: 'pi pi-exclamation-circle',
    };
    return iconMap[status] || 'pi pi-info-circle';
};

// Check if template has pending verification
const hasPendingVerification = (templateId) => {
    return props.verifications?.some(v => v.template?.id === templateId && v.status === 'pending');
};

// Check if template has approved verification
const hasApprovedVerification = (templateId) => {
    return props.verifications?.some(v => v.template?.id === templateId && v.status === 'approved');
};

// Navigate to submit form
const submitDocument = (template) => {
    router.visit(route('kyc.create', template.id));
};

// Show verification details
const viewDetails = (verification) => {
    selectedVerification.value = verification;
    showDetailsDialog.value = true;
};

// Open template selection dialog
const openTemplateSelection = () => {
    showTemplateDialog.value = true;
};
</script>

<template>
    <DashboardLayout title="KYC Verification">
        <div class="space-y-6">
            <!-- Flash Messages -->
            <Message v-if="flashSuccess" severity="success" :closable="true">
                {{ flashSuccess }}
            </Message>
            <Message v-if="flashWarning" severity="warn" :closable="true">
                {{ flashWarning }}
            </Message>
            <Message v-if="flashError" severity="error" :closable="true">
                {{ flashError }}
            </Message>

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">KYC Verification</h1>
                    <p class="text-gray-600 dark:text-gray-400">Verify your identity to access all features.</p>
                </div>
                <Button 
                    v-if="!isVerified"
                    label="Submit Documents" 
                    icon="pi pi-upload" 
                    @click="openTemplateSelection"
                    class="w-full sm:w-auto"
                />
            </div>

            <!-- Overall Status Card -->
            <Card class="border-l-4" :class="{
                'border-l-green-500': overallStatus.status === 'approved',
                'border-l-yellow-500': overallStatus.status === 'pending',
                'border-l-red-500': overallStatus.status === 'rejected',
                'border-l-gray-400': overallStatus.status === 'not_started',
            }">
                <template #content>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-14 h-14 rounded-full" :class="{
                            'bg-green-100 dark:bg-green-900/30': overallStatus.status === 'approved',
                            'bg-yellow-100 dark:bg-yellow-900/30': overallStatus.status === 'pending',
                            'bg-red-100 dark:bg-red-900/30': overallStatus.status === 'rejected',
                            'bg-gray-100 dark:bg-gray-700': overallStatus.status === 'not_started',
                        }">
                            <i :class="[getStatusIcon(overallStatus.status), 'text-2xl', {
                                'text-green-500': overallStatus.status === 'approved',
                                'text-yellow-500': overallStatus.status === 'pending',
                                'text-red-500': overallStatus.status === 'rejected',
                                'text-gray-500': overallStatus.status === 'not_started',
                            }]"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Verification Status
                                </h2>
                                <Tag :value="overallStatus.label" :severity="getStatusSeverity(overallStatus.color)" />
                                <!-- Blinking indicator for pending -->
                                <span v-if="overallStatus.status === 'pending'" class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                                </span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">{{ overallStatus.message }}</p>
                        </div>
                        <div v-if="kycLevel > 0" class="text-right hidden sm:block">
                            <span class="text-sm text-gray-500 dark:text-gray-400">KYC Level</span>
                            <p class="text-2xl font-bold text-primary">{{ kycLevel }}</p>
                        </div>
                    </div>
                </template>
            </Card>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Verification History -->
                <Card class="lg:col-span-2">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-history text-primary"></i>
                            Verification History
                        </div>
                    </template>
                    <template #content>
                        <div v-if="verifications && verifications.length" class="space-y-3">
                            <div 
                                v-for="verification in verifications" 
                                :key="verification.id" 
                                @click="viewDetails(verification)"
                                class="p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="pi pi-id-card text-primary"></i>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ verification.document_type_name }}
                                            </h3>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span v-if="verification.document_number" class="font-mono">
                                                {{ verification.document_number }}
                                            </span>
                                            <span v-if="verification.document_number">•</span>
                                            <span>Submitted {{ verification.created_at_human }}</span>
                                        </div>
                                        <p v-if="verification.status === 'rejected' && verification.rejection_reason" 
                                           class="mt-2 text-sm text-red-600 dark:text-red-400">
                                            <i class="pi pi-exclamation-triangle mr-1"></i>
                                            {{ verification.rejection_reason }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <Tag :value="verification.status_label" :severity="getStatusSeverity(verification.status_color)" />
                                        <div class="flex items-center gap-1 text-xs text-gray-400">
                                            <i v-if="verification.has_document_front" class="pi pi-image" title="Front uploaded"></i>
                                            <i v-if="verification.has_document_back" class="pi pi-image" title="Back uploaded"></i>
                                            <i v-if="verification.has_selfie" class="pi pi-user" title="Selfie uploaded"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <i class="pi pi-id-card text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-gray-500 dark:text-gray-400">No verification submissions yet</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                                Submit your identity documents to get verified
                            </p>
                            <Button 
                                label="Get Started" 
                                icon="pi pi-arrow-right" 
                                class="mt-4"
                                @click="openTemplateSelection"
                            />
                        </div>
                    </template>
                </Card>

                <!-- Document Types / Quick Actions -->
                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-file text-primary"></i>
                            Document Types
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div 
                                v-for="template in templates" 
                                :key="template.id"
                                class="p-3 border rounded-lg dark:border-gray-700"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ template.name }}</h4>
                                    <Tag v-if="template.is_required" value="Required" severity="warn" />
                                </div>
                                <p v-if="template.description" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ template.description }}
                                </p>
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <span v-for="req in template.requirements_list" :key="req" 
                                          class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">
                                        {{ req }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-3">
                                    <span v-if="hasApprovedVerification(template.id)" class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                        <i class="pi pi-check-circle"></i> Verified
                                    </span>
                                    <span v-else-if="hasPendingVerification(template.id)" class="text-xs text-yellow-600 dark:text-yellow-400 flex items-center gap-1">
                                        <i class="pi pi-clock"></i> Pending
                                    </span>
                                    <span v-else class="text-xs text-gray-400"></span>
                                    <Button 
                                        v-if="!hasApprovedVerification(template.id) && !hasPendingVerification(template.id)"
                                        label="Submit" 
                                        icon="pi pi-upload"
                                        size="small"
                                        outlined
                                        @click="submitDocument(template)"
                                    />
                                    <Button 
                                        v-else-if="!hasApprovedVerification(template.id) && hasPendingVerification(template.id)"
                                        label="Pending" 
                                        icon="pi pi-clock"
                                        size="small"
                                        disabled
                                        outlined
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-if="!templates || !templates.length" class="text-center py-6">
                            <p class="text-gray-500 dark:text-gray-400">No document types available</p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Help Section -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-question-circle text-primary"></i>
                        Why KYC Verification?
                    </div>
                </template>
                <template #content>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="flex items-start gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex-shrink-0">
                                <i class="pi pi-shield text-blue-500"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Security</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Protect your account from unauthorized access</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex-shrink-0">
                                <i class="pi pi-check-circle text-green-500"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Higher Limits</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Access increased transaction and withdrawal limits</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex-shrink-0">
                                <i class="pi pi-star text-purple-500"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Full Features</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Unlock all banking features and services</p>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Template Selection Dialog -->
        <Dialog 
            v-model:visible="showTemplateDialog" 
            modal 
            header="Select Document Type" 
            :style="{ width: '500px' }"
            :breakpoints="{ '640px': '90vw' }"
        >
            <div class="space-y-3">
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Choose the type of document you want to submit for verification.
                </p>
                <div 
                    v-for="template in templates" 
                    :key="template.id"
                    @click="submitDocument(template)"
                    class="p-4 border rounded-lg dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    :class="{
                        'opacity-50 cursor-not-allowed': hasPendingVerification(template.id) || hasApprovedVerification(template.id),
                    }"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ template.name }}</h4>
                                <Tag v-if="template.is_required" value="Required" severity="warn" class="text-xs" />
                            </div>
                            <p v-if="template.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ template.description }}
                            </p>
                        </div>
                        <div>
                            <i v-if="hasApprovedVerification(template.id)" class="pi pi-check-circle text-green-500 text-xl"></i>
                            <i v-else-if="hasPendingVerification(template.id)" class="pi pi-clock text-yellow-500 text-xl"></i>
                            <i v-else class="pi pi-angle-right text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>

        <!-- Verification Details Dialog -->
        <Dialog 
            v-model:visible="showDetailsDialog" 
            modal 
            header="Verification Details" 
            :style="{ width: '500px' }"
            :breakpoints="{ '640px': '90vw' }"
        >
            <div v-if="selectedVerification" class="space-y-4">
                <div class="flex items-center justify-between pb-4 border-b dark:border-gray-700">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            {{ selectedVerification.document_type_name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            Submitted {{ selectedVerification.created_at }}
                        </p>
                    </div>
                    <Tag :value="selectedVerification.status_label" :severity="getStatusSeverity(selectedVerification.status_color)" />
                </div>

                <div v-if="selectedVerification.document_number" class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Document Number</span>
                    <span class="font-mono text-gray-900 dark:text-white">{{ selectedVerification.document_number }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Documents Uploaded</span>
                    <div class="flex gap-2">
                        <span v-if="selectedVerification.has_document_front" class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded">
                            Front
                        </span>
                        <span v-if="selectedVerification.has_document_back" class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded">
                            Back
                        </span>
                        <span v-if="selectedVerification.has_selfie" class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded">
                            Selfie
                        </span>
                    </div>
                </div>

                <div v-if="selectedVerification.verified_at" class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Reviewed On</span>
                    <span class="text-gray-900 dark:text-white">{{ selectedVerification.verified_at }}</span>
                </div>

                <div v-if="selectedVerification.rejection_reason" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm font-medium text-red-700 dark:text-red-400 mb-1">Rejection Reason</p>
                    <p class="text-sm text-red-600 dark:text-red-300">{{ selectedVerification.rejection_reason }}</p>
                </div>

                <div v-if="selectedVerification.status === 'rejected'" class="pt-4 border-t dark:border-gray-700">
                    <Button 
                        label="Submit New Documents" 
                        icon="pi pi-refresh" 
                        class="w-full"
                        @click="() => { showDetailsDialog = false; openTemplateSelection(); }"
                    />
                </div>
            </div>
        </Dialog>
    </DashboardLayout>
</template>
