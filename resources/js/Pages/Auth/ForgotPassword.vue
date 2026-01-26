<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Toast from 'primevue/toast';import AppLogo from '@/Components/Common/AppLogo.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';
const props = defineProps({
    status: String,
});

const toast = useToast();
const form = useForm({
    email: '',
});

const isSubmitting = ref(false);

const submit = () => {
    isSubmitting.value = true;
    form.post(route('password.email'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Email Sent',
                detail: 'Check your email for password reset instructions',
                life: 4000,
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to send reset link. Please try again.',
                life: 4000,
            });
        },
        onFinish: () => {
            isSubmitting.value = false;
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
                    <AppLogo :show-text="true" size="lg" />
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
                </div>

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
