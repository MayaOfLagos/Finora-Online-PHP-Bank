<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import Message from 'primevue/message';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    program: {
        type: Object,
        required: true,
    },
});

const toast = useToast();

const form = ref({
    grant_program_id: props.program.id,
    cover_letter: '',
    documents: [],
});

const uploadedFiles = ref([]);
const isSubmitting = ref(false);
const currentStep = ref(1); // 1: Cover Letter, 2: Documents, 3: Review

const handleUpload = (event) => {
    const files = event.files;
    files.forEach(file => {
        uploadedFiles.value.push({
            name: file.name,
            size: file.size,
            file: file,
        });
        form.value.documents.push(file);
    });
    toast.add({ severity: 'info', summary: 'File Uploaded', detail: `${files.length} file(s) uploaded`, life: 3000 });
};

const removeFile = (index) => {
    uploadedFiles.value.splice(index, 1);
    form.value.documents.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const goBack = () => {
    router.visit('/grants');
};

const goToPreviousStep = () => {
    currentStep.value--;
};

const goToNextStep = () => {
    if (currentStep.value === 1) {
        if (!form.value.cover_letter.trim()) {
            toast.add({ severity: 'warn', summary: 'Missing Information', detail: 'Please fill in your cover letter', life: 3000 });
            return;
        }
    } else if (currentStep.value === 2) {
        if (form.value.documents.length === 0) {
            toast.add({ severity: 'warn', summary: 'Missing Documents', detail: 'Please upload at least one required document', life: 3000 });
            return;
        }
    }
    currentStep.value++;
};

const submitApplication = () => {
    if (!form.value.cover_letter.trim()) {
        toast.add({ severity: 'warn', summary: 'Missing Information', detail: 'Please fill in your cover letter', life: 3000 });
        currentStep.value = 1;
        return;
    }

    if (form.value.documents.length === 0) {
        toast.add({ severity: 'warn', summary: 'Missing Documents', detail: 'Please upload at least one required document', life: 3000 });
        currentStep.value = 2;
        return;
    }

    isSubmitting.value = true;
    
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('grant_program_id', form.value.grant_program_id);
    formData.append('cover_letter', form.value.cover_letter);
    
    form.value.documents.forEach((file, index) => {
        formData.append(`documents[${index}]`, file);
    });

    router.post('/grants/apply', formData, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Application submitted successfully', life: 3000 });
            setTimeout(() => {
                router.visit('/grants/applications');
            }, 1500);
        },
        onError: (errors) => {
            toast.add({ severity: 'error', summary: 'Error', detail: Object.values(errors)[0] || 'Failed to submit application', life: 3000 });
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <DashboardLayout title="Apply for Grant">
        <Toast />
        
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Apply for {{ program.name }}</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Complete the application form below</p>
                </div>
                <Button 
                    label="Back to Grants" 
                    severity="secondary"
                    icon="pi pi-arrow-left"
                    @click="goBack"
                />
            </div>

            <!-- Program Info Card -->
            <Card>
                <template #header>
                    <div class="bg-gradient-to-r from-indigo-500 to-blue-600 p-6 text-white">
                        <h3 class="text-xl font-bold">{{ program.name }}</h3>
                        <p class="text-indigo-100 mt-2">{{ program.description }}</p>
                    </div>
                </template>
                <template #content>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-400">Available Amount</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                                ${{ program.amount_display.toLocaleString() }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                            <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold">Eligibility Criteria</p>
                            <ul class="mt-2 space-y-1 text-sm text-blue-900 dark:text-blue-200">
                                <li v-for="(criterion, idx) in program.eligibility_criteria" :key="idx" class="flex items-center gap-2">
                                    <i class="pi pi-check text-xs"></i>
                                    {{ criterion }}
                                </li>
                            </ul>
                        </div>
                        <div class="p-4 rounded-lg bg-orange-50 dark:bg-orange-900/30">
                            <p class="text-xs text-orange-600 dark:text-orange-400 font-semibold">Required Documents</p>
                            <ul class="mt-2 space-y-1 text-sm text-orange-900 dark:text-orange-200">
                                <li v-for="(doc, idx) in program.required_documents" :key="idx" class="flex items-center gap-2">
                                    <i class="pi pi-file text-xs"></i>
                                    {{ doc }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Step Indicator -->
            <div class="flex items-center justify-between">
                <div class="flex gap-2">
                    <div
                        v-for="step in [1, 2, 3]"
                        :key="step"
                        class="flex items-center"
                    >
                        <button
                            :class="[
                                'w-10 h-10 rounded-full font-bold flex items-center justify-center transition-all',
                                currentStep === step
                                    ? 'bg-indigo-600 text-white'
                                    : currentStep > step
                                      ? 'bg-green-500 text-white'
                                      : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                            ]"
                            @click="currentStep = step"
                            :disabled="currentStep < step"
                        >
                            <i v-if="currentStep > step" class="pi pi-check text-lg"></i>
                            <span v-else>{{ step }}</span>
                        </button>
                        <div
                            v-if="step < 3"
                            :class="[
                                'w-12 h-1 mx-1',
                                currentStep > step ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700'
                            ]"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <Card>
                <template #content>
                    <!-- Step 1: Cover Letter -->
                    <div v-if="currentStep === 1" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Step 1: Cover Letter</h3>
                            <label for="cover-letter" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                Cover Letter <span class="text-red-500">*</span>
                            </label>
                            <Textarea
                                id="cover-letter"
                                v-model="form.cover_letter"
                                rows="10"
                                placeholder="Tell us why you are interested in this grant and how it will help you..."
                                class="w-full"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Minimum 50 characters recommended</p>
                        </div>

                        <Message severity="info">
                            <p class="text-sm text-gray-900 dark:text-gray-100">Be specific about your goals and how this grant will help you achieve them.</p>
                        </Message>

                        <div class="flex justify-end">
                            <Button
                                label="Next"
                                icon="pi pi-arrow-right"
                                @click="goToNextStep"
                                :disabled="!form.cover_letter.trim()"
                            />
                        </div>
                    </div>

                    <!-- Step 2: Document Upload -->
                    <div v-if="currentStep === 2" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Step 2: Upload Documents</h3>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                Upload Required Documents <span class="text-red-500">*</span>
                            </label>
                            <FileUpload
                                name="documents"
                                :customUpload="true"
                                :auto="true"
                                @uploader="handleUpload"
                                :multiple="true"
                                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                                chooseLabel="Select Files"
                                uploadLabel="Upload"
                                cancelLabel="Cancel"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Supported formats: PDF, Word, Excel, Images. Max size: 5MB per file
                            </p>
                        </div>

                        <!-- Uploaded Files List -->
                        <div v-if="uploadedFiles.length > 0">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Uploaded Documents</h4>
                            <div class="space-y-2">
                                <div
                                    v-for="(file, idx) in uploadedFiles"
                                    :key="idx"
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                >
                                    <div class="flex items-center gap-3 flex-1">
                                        <i class="pi pi-file text-blue-500"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ file.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                    </div>
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        text
                                        size="small"
                                        @click="removeFile(idx)"
                                    />
                                </div>
                            </div>
                        </div>

                        <Message v-if="uploadedFiles.length === 0" severity="warn">
                            <p class="text-sm text-gray-900 dark:text-gray-100">Please upload all required documents before proceeding.</p>
                        </Message>

                        <div class="flex justify-between">
                            <Button
                                label="Previous"
                                severity="secondary"
                                icon="pi pi-arrow-left"
                                @click="goToPreviousStep"
                            />
                            <Button
                                label="Next"
                                icon="pi pi-arrow-right"
                                @click="goToNextStep"
                                :disabled="uploadedFiles.length === 0"
                            />
                        </div>
                    </div>

                    <!-- Step 3: Review & Submit -->
                    <div v-if="currentStep === 3" class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Step 3: Review & Submit</h3>
                        
                        <Message severity="success">
                            <p class="text-sm text-gray-900 dark:text-gray-100">Review your application details and click Submit to send your application.</p>
                        </Message>

                        <div class="space-y-4">
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1 font-semibold">Cover Letter</p>
                                <p class="text-gray-900 dark:text-white whitespace-pre-wrap text-sm">{{ form.cover_letter }}</p>
                            </div>

                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 font-semibold">Documents ({{ uploadedFiles.length }} file(s))</p>
                                <ul class="space-y-1">
                                    <li v-for="file in uploadedFiles" :key="file.name" class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                        <i class="pi pi-file text-blue-500"></i>
                                        {{ file.name }} ({{ formatFileSize(file.size) }})
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <Button
                                label="Previous"
                                severity="secondary"
                                icon="pi pi-arrow-left"
                                @click="goToPreviousStep"
                            />
                            <Button
                                label="Submit Application"
                                icon="pi pi-send"
                                :loading="isSubmitting"
                                @click="submitApplication"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
