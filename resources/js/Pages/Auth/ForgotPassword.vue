<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Toast from 'primevue/toast';
import AppLogo from '@/Components/Common/AppLogo.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';
import ReCaptcha from '@/Components/Common/ReCaptcha.vue';

const props = defineProps({
    status: String,
    recaptcha: {
        type: Object,
        default: () => ({ enabled: false, siteKey: '', version: 'v2' }),
    },
});

const toast = useToast();
const recaptchaRef = ref(null);

const form = useForm({
    email: '',
    recaptcha_token: '',
});

const isSubmitting = ref(false);

const submit = async () => {
    isSubmitting.value = true;
    
    // Get reCAPTCHA token if enabled
    if (props.recaptcha?.enabled && recaptchaRef.value) {
        try {
            const token = await recaptchaRef.value.getToken();
            form.recaptcha_token = token;
        } catch (e) {
            toast.add({
                severity: 'error',
                summary: 'Verification Failed',
                detail: 'Please complete the security verification.',
                life: 3000,
            });
            isSubmitting.value = false;
            return;
        }
    }
    
    form.post(route('password.email'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Email Sent',
                detail: 'Check your email for password reset instructions',
                life: 4000,
            });
        },
        onError: (errors) => {
            // Reset reCAPTCHA on error
            if (props.recaptcha?.enabled && props.recaptcha?.version === 'v2' && recaptchaRef.value) {
                recaptchaRef.value.reset();
            }
            
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: errors.recaptcha_token || errors.email || 'Failed to send reset link. Please try again.',
                life: 4000,
            });
        },
        onFinish: () => {
            isSubmitting.value = false;
            form.recaptcha_token = '';
        },
    });
};
</script>

<template>
    <Head title="Forgot Password" />

    <Toast />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-primary-100/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl"></div>
        </div>

        <div class="w-full max-w-md">
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

            <!-- Footer -->
            <div class="mt-8 text-center">
                <CopyrightText />
            </div>
        </div>
    </div>
</template>
