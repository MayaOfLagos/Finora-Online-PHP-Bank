<script setup>
import { ref, computed } from 'vue';
import { useForm, router, usePage, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    template: Object,
});

const toast = useToast();
const page = usePage();

// Form state
const form = useForm({
    template_id: props.template.id,
    document_number: '',
    document_front: null,
    document_back: null,
    selfie: null,
});

// File preview URLs
const documentFrontPreview = ref(null);
const documentBackPreview = ref(null);
const selfiePreview = ref(null);

// File size helper
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Handle file selection for front document
const handleFrontUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.document_front = file;
        documentFrontPreview.value = URL.createObjectURL(file);
    }
};

// Handle file selection for back document
const handleBackUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.document_back = file;
        documentBackPreview.value = URL.createObjectURL(file);
    }
};

// Handle file selection for selfie
const handleSelfieUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.selfie = file;
        selfiePreview.value = URL.createObjectURL(file);
    }
};

// Remove file
const removeFile = (type) => {
    if (type === 'front') {
        form.document_front = null;
        documentFrontPreview.value = null;
    } else if (type === 'back') {
        form.document_back = null;
        documentBackPreview.value = null;
    } else if (type === 'selfie') {
        form.selfie = null;
        selfiePreview.value = null;
    }
};

// Submit form
const submitForm = () => {
    form.post(route('kyc.store'), {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Your KYC verification has been submitted successfully.', 'Success');
        },
        onError: (errors) => {
            toast.error(Object.values(errors)[0] || 'Failed to submit verification.', 'Error');
        },
    });
};

// Check if form is valid
const isFormValid = computed(() => {
    let valid = true;
    
    if (props.template.requires_document_number && !form.document_number) {
        valid = false;
    }
    if (props.template.requires_front_image && !form.document_front) {
        valid = false;
    }
    if (props.template.requires_back_image && !form.document_back) {
        valid = false;
    }
    if (props.template.requires_selfie && !form.selfie) {
        valid = false;
    }
    
    return valid;
});

// Accepted file types string for input
const acceptedTypes = computed(() => {
    const formats = props.template.accepted_formats || ['jpg', 'jpeg', 'png', 'pdf'];
    return formats.map(f => `.${f}`).join(',');
});

const acceptedImageTypes = '.jpg,.jpeg,.png';
</script>

<template>
    <DashboardLayout title="Submit KYC Document">
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Back Button -->
            <div>
                <Link 
                    :href="route('kyc.index')" 
                    class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                >
                    <i class="pi pi-arrow-left mr-2"></i>
                    Back to KYC Verification
                </Link>
            </div>

            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Submit {{ template.name }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Upload your {{ template.name.toLowerCase() }} for identity verification.
                </p>
            </div>

            <!-- Instructions Card -->
            <Card v-if="template.instructions" class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
                <template #content>
                    <div class="flex gap-3">
                        <i class="pi pi-info-circle text-blue-500 text-xl flex-shrink-0 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-1">Instructions</h4>
                            <p class="text-sm text-blue-800 dark:text-blue-200 whitespace-pre-line">{{ template.instructions }}</p>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Requirements Summary -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-list text-primary"></i>
                        Requirements
                    </div>
                </template>
                <template #content>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div v-if="template.requires_document_number" class="flex items-center gap-2 text-sm">
                            <i class="pi pi-check-circle text-green-500"></i>
                            <span>Document Number</span>
                        </div>
                        <div v-if="template.requires_front_image" class="flex items-center gap-2 text-sm">
                            <i class="pi pi-check-circle text-green-500"></i>
                            <span>Front Image</span>
                        </div>
                        <div v-if="template.requires_back_image" class="flex items-center gap-2 text-sm">
                            <i class="pi pi-check-circle text-green-500"></i>
                            <span>Back Image</span>
                        </div>
                        <div v-if="template.requires_selfie" class="flex items-center gap-2 text-sm">
                            <i class="pi pi-check-circle text-green-500"></i>
                            <span>Selfie with Document</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t dark:border-gray-700 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            <i class="pi pi-file mr-1"></i>
                            Formats: {{ template.accepted_formats_list }}
                        </span>
                        <span>
                            <i class="pi pi-cloud-upload mr-1"></i>
                            Max size: {{ template.max_file_size_human }}
                        </span>
                    </div>
                </template>
            </Card>

            <!-- Form Card -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-upload text-primary"></i>
                        Upload Documents
                    </div>
                </template>
                <template #content>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Document Number -->
                        <div v-if="template.requires_document_number">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Document Number <span class="text-red-500">*</span>
                            </label>
                            <InputText 
                                v-model="form.document_number"
                                :class="{ 'p-invalid': form.errors.document_number }"
                                placeholder="Enter your document number"
                                class="w-full"
                            />
                            <small v-if="form.errors.document_number" class="text-red-500">
                                {{ form.errors.document_number }}
                            </small>
                        </div>

                        <!-- Document Front -->
                        <div v-if="template.requires_front_image">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Front of Document <span class="text-red-500">*</span>
                            </label>
                            <div 
                                v-if="!documentFrontPreview"
                                class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-primary transition-colors cursor-pointer"
                                @click="$refs.frontInput.click()"
                            >
                                <i class="pi pi-image text-4xl text-gray-400 dark:text-gray-500 mb-2"></i>
                                <p class="text-gray-600 dark:text-gray-400">Click to upload front of document</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ template.accepted_formats_list }} up to {{ template.max_file_size_human }}
                                </p>
                            </div>
                            <div v-else class="relative">
                                <img :src="documentFrontPreview" alt="Document Front" class="w-full h-48 object-contain rounded-lg bg-gray-100 dark:bg-gray-700" />
                                <button 
                                    type="button"
                                    @click="removeFile('front')"
                                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                                >
                                    <i class="pi pi-times text-xs"></i>
                                </button>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ form.document_front?.name }} ({{ formatFileSize(form.document_front?.size || 0) }})
                                </p>
                            </div>
                            <input 
                                ref="frontInput"
                                type="file" 
                                :accept="acceptedTypes" 
                                class="hidden" 
                                @change="handleFrontUpload"
                            />
                            <small v-if="form.errors.document_front" class="text-red-500">
                                {{ form.errors.document_front }}
                            </small>
                        </div>

                        <!-- Document Back -->
                        <div v-if="template.requires_back_image">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Back of Document <span class="text-red-500">*</span>
                            </label>
                            <div 
                                v-if="!documentBackPreview"
                                class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-primary transition-colors cursor-pointer"
                                @click="$refs.backInput.click()"
                            >
                                <i class="pi pi-image text-4xl text-gray-400 dark:text-gray-500 mb-2"></i>
                                <p class="text-gray-600 dark:text-gray-400">Click to upload back of document</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ template.accepted_formats_list }} up to {{ template.max_file_size_human }}
                                </p>
                            </div>
                            <div v-else class="relative">
                                <img :src="documentBackPreview" alt="Document Back" class="w-full h-48 object-contain rounded-lg bg-gray-100 dark:bg-gray-700" />
                                <button 
                                    type="button"
                                    @click="removeFile('back')"
                                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                                >
                                    <i class="pi pi-times text-xs"></i>
                                </button>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ form.document_back?.name }} ({{ formatFileSize(form.document_back?.size || 0) }})
                                </p>
                            </div>
                            <input 
                                ref="backInput"
                                type="file" 
                                :accept="acceptedTypes" 
                                class="hidden" 
                                @change="handleBackUpload"
                            />
                            <small v-if="form.errors.document_back" class="text-red-500">
                                {{ form.errors.document_back }}
                            </small>
                        </div>

                        <!-- Selfie -->
                        <div v-if="template.requires_selfie">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Selfie with Document <span class="text-red-500">*</span>
                            </label>
                            <Message severity="info" :closable="false" class="mb-3">
                                <small>Take a clear photo of yourself holding your document next to your face. Make sure both your face and the document are clearly visible.</small>
                            </Message>
                            <div 
                                v-if="!selfiePreview"
                                class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-primary transition-colors cursor-pointer"
                                @click="$refs.selfieInput.click()"
                            >
                                <i class="pi pi-user text-4xl text-gray-400 dark:text-gray-500 mb-2"></i>
                                <p class="text-gray-600 dark:text-gray-400">Click to upload selfie</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    JPG, PNG up to {{ template.max_file_size_human }}
                                </p>
                            </div>
                            <div v-else class="relative">
                                <img :src="selfiePreview" alt="Selfie" class="w-full h-48 object-contain rounded-lg bg-gray-100 dark:bg-gray-700" />
                                <button 
                                    type="button"
                                    @click="removeFile('selfie')"
                                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                                >
                                    <i class="pi pi-times text-xs"></i>
                                </button>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ form.selfie?.name }} ({{ formatFileSize(form.selfie?.size || 0) }})
                                </p>
                            </div>
                            <input 
                                ref="selfieInput"
                                type="file" 
                                :accept="acceptedImageTypes" 
                                class="hidden" 
                                @change="handleSelfieUpload"
                            />
                            <small v-if="form.errors.selfie" class="text-red-500">
                                {{ form.errors.selfie }}
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t dark:border-gray-700">
                            <Button 
                                type="submit"
                                label="Submit for Verification"
                                icon="pi pi-check"
                                :loading="form.processing"
                                :disabled="!isFormValid || form.processing"
                                class="flex-1"
                            />
                            <Link :href="route('kyc.index')">
                                <Button 
                                    type="button"
                                    label="Cancel"
                                    severity="secondary"
                                    outlined
                                    class="w-full sm:w-auto"
                                />
                            </Link>
                        </div>
                    </form>
                </template>
            </Card>

            <!-- Privacy Notice -->
            <Card class="bg-gray-50 dark:bg-gray-800/50">
                <template #content>
                    <div class="flex gap-3">
                        <i class="pi pi-lock text-gray-400 text-xl flex-shrink-0"></i>
                        <div>
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-1">Your Privacy Matters</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Your documents are securely stored and only used for identity verification purposes. 
                                We follow strict data protection guidelines to ensure your information remains safe.
                            </p>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
