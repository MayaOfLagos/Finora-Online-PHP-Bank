<script setup>
import { ref, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Skeleton from 'primevue/skeleton';
import AppLogo from '@/Components/Common/AppLogo.vue';
import SeoHead from '@/Components/Common/SeoHead.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';
import ReCaptcha from '@/Components/Common/ReCaptcha.vue';
import PagePreloader from '@/Components/Common/PagePreloader.vue';

const props = defineProps({
    status: String,
    recaptcha: {
        type: Object,
        default: () => ({ enabled: false, siteKey: '', version: 'v2' }),
    },
});

const toast = useToast();
const recaptchaRef = ref(null);

// Page loading state
const isPageLoading = ref(true);
const showContent = ref(false);

const handlePreloaderComplete = () => {
    isPageLoading.value = false;
    setTimeout(() => {
        showContent.value = true;
    }, 100);
};

const form = useForm({
    email: '',
    recaptcha_token: '',
});

const isSubmitting = ref(false);

onMounted(() => {
    // Page loading is now handled by PagePreloader
});

const submit = async () => {
    isSubmitting.value = true;
    
    // Get reCAPTCHA token if enabled
    if (props.recaptcha?.enabled && recaptchaRef.value) {
        try {
            const token = await recaptchaRef.value.getToken();
            form.recaptcha_token = token;
        } catch (e) {
            toast.error('Please complete the security verification.', 'Verification Failed');
            isSubmitting.value = false;
            return;
        }
    }
    
    form.post(route('password.email'), {
        onSuccess: () => {
            toast.success('Check your email for password reset instructions', 'Email Sent');
        },
        onError: (errors) => {
            // Reset reCAPTCHA on error
            if (props.recaptcha?.enabled && props.recaptcha?.version === 'v2' && recaptchaRef.value) {
                recaptchaRef.value.reset();
            }
            
            toast.error(errors.recaptcha_token || errors.email || 'Failed to send reset link. Please try again.', 'Error');
        },
        onFinish: () => {
            isSubmitting.value = false;
            form.recaptcha_token = '';
        },
    });
};
</script>

<template>
    <SeoHead title="Forgot Password" />
    <PagePreloader :min-load-time="1200" @complete="handlePreloaderComplete" />

    <Transition
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
    >
    <div v-show="showContent" class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-primary-100/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl"></div>
        </div>

        <div class="w-full max-w-md">
            <!-- Skeleton Loading State -->
            <div v-if="isPageLoading" class="animate-pulse">
                <!-- Logo Skeleton -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center">
                        <Skeleton width="4rem" height="4rem" class="rounded-xl" />
                    </div>
                </div>
                
                <!-- Card Skeleton -->
                <div class="bg-white/90 backdrop-blur-xl border border-gray-200/50 rounded-3xl p-8 space-y-6">
                    <div class="mb-8">
                        <Skeleton width="60%" height="2rem" class="mb-2" />
                        <Skeleton width="85%" height="1rem" />
                        <Skeleton width="65%" height="1rem" class="mt-1" />
                    </div>
                    <div>
                        <Skeleton width="6rem" height="1rem" class="mb-2" />
                        <Skeleton height="3rem" class="w-full" />
                    </div>
                    <Skeleton height="3rem" class="w-full" />
                    <Skeleton width="60%" height="1rem" class="mx-auto" />
                </div>
            </div>
            
            <!-- Actual Content -->
            <div v-else class="animate-fade-in">
                <!-- Logo Section -->
                <div class="text-center mb-8">
                    <Link href="/" class="inline-flex items-center justify-center">
                        <AppLogo :show-text="false" size="lg" />
                    </Link>
                </div>

                <!-- Main Card -->
                <div class="relative bg-white/90 backdrop-blur-xl border border-gray-200/50 rounded-3xl shadow-2xl shadow-primary-100/40 p-8">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Reset Your Password</h2>
                    <p class="text-gray-600">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                </div>

                <!-- Email Input -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">
                        Email Address
                    </label>
                    <IconField icon-position="left">
                        <InputIcon class="pi pi-envelope"></InputIcon>
                        <InputText
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="you@example.com"
                            class="w-full"
                            :class="{ 'ng-invalid ng-touched': form.errors.email }"
                            @keyup.enter="submit"
                        />
                    </IconField>
                    <small v-if="form.errors.email" class="text-red-500 mt-1 block">
                        {{ form.errors.email }}
                    </small>
                </div>

                <!-- reCAPTCHA -->
                <div v-if="recaptcha?.enabled" class="flex justify-center mb-4">
                    <ReCaptcha
                        ref="recaptchaRef"
                        :site-key="recaptcha.siteKey"
                        :version="recaptcha.version"
                        theme="light"
                        action="password_reset"
                        @verify="(token) => form.recaptcha_token = token"
                        @expire="() => form.recaptcha_token = ''"
                    />
                </div>
                <small v-if="form.errors.recaptcha_token" class="text-red-500 text-sm text-center block mb-4">
                    {{ form.errors.recaptcha_token }}
                </small>

                <!-- Submit Button -->
                <Button
                    @click="submit"
                    :loading="isSubmitting"
                    :disabled="!form.email || isSubmitting"
                    class="w-full mb-4"
                    severity="primary"
                    label="Send Reset Link"
                    icon="pi pi-send"
                />

                <!-- Back to Login -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Remember your password?
                        <Link href="/login" class="font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                            Sign in here
                        </Link>
                    </p>
                </div>
            </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <CopyrightText />
            </div>
        </div>
    </div>
    </Transition>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
