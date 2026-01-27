<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Toast from 'primevue/toast';
import AppLogo from '@/Components/Common/AppLogo.vue';
import SeoHead from '@/Components/Common/SeoHead.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';

const props = defineProps({
    email: String,
    token: String,
});

const page = usePage();
const siteName = computed(() => page.props.settings?.general?.site_name || page.props.settings?.general?.app_name || 'Finora Bank');

const toast = useToast();
const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const isSubmitting = ref(false);
const passwordStrength = computed(() => {
    const pwd = form.password;
    if (!pwd) return 0;
    let strength = 0;
    if (pwd.length >= 8) strength++;
    if (pwd.match(/[a-z]/) && pwd.match(/[A-Z]/)) strength++;
    if (pwd.match(/[0-9]/)) strength++;
    if (pwd.match(/[^a-zA-Z0-9]/)) strength++;
    return strength;
});

const getStrengthLabel = computed(() => {
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    return labels[passwordStrength.value];
});

const getStrengthColor = computed(() => {
    const colors = ['', 'error', 'warning', 'info', 'success'];
    return colors[passwordStrength.value];
});

const submit = () => {
    isSubmitting.value = true;
    form.post(route('password.store'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Password Reset',
                detail: 'Your password has been successfully reset. Redirecting to login...',
                life: 3000,
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to reset password. Please try again.',
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
    <SeoHead title="Reset Password" />

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
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Create New Password</h2>
                    <p class="text-gray-600 text-sm">
                        Enter a strong password to secure your {{ siteName }} account.
                    </p>
                </div>

                <!-- Email Display (Read-only) -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">
                        Email Address
                    </label>
                    <IconField icon-position="left">
                        <InputIcon class="pi pi-envelope"></InputIcon>
                        <InputText
                            id="email"
                            :value="form.email"
                            type="email"
                            disabled
                            class="w-full"
                        />
                    </IconField>
                </div>

                <!-- Password Input -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">
                        New Password
                    </label>
                    <Password
                        id="password"
                        v-model="form.password"
                        toggle-mask
                        :feedback="false"
                        placeholder="Enter your new password"
                        class="w-full"
                        input-class="w-full"
                    />
                    <div v-if="form.password" class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-600">Password Strength</span>
                            <span class="text-xs font-semibold" :class="`text-${getStrengthColor}-600`">
                                {{ getStrengthLabel }}
                            </span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div
                                class="h-full transition-all duration-300"
                                :class="`bg-${getStrengthColor}-600 w-${passwordStrength * 25}%`"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password Input -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-900 mb-2">
                        Confirm Password
                    </label>
                    <Password
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        toggle-mask
                        :feedback="false"
                        placeholder="Confirm your new password"
                        class="w-full"
                        input-class="w-full"
                    />
                    <div
                        v-if="form.password_confirmation && form.password !== form.password_confirmation"
                        class="mt-2 text-sm text-red-600 flex items-center gap-2"
                    >
                        <i class="pi pi-exclamation-circle"></i>
                        Passwords do not match
                    </div>
                </div>

                <!-- Password Requirements -->
                <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Password Requirements:</p>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-center gap-2" :class="form.password.length >= 8 ? 'text-green-600' : ''">
                            <i :class="`pi ${form.password.length >= 8 ? 'pi-check-circle' : 'pi-circle'}`"></i>
                            At least 8 characters
                        </li>
                        <li class="flex items-center gap-2" :class="form.password.match(/[a-z]/) && form.password.match(/[A-Z]/) ? 'text-green-600' : ''">
                            <i :class="`pi ${form.password.match(/[a-z]/) && form.password.match(/[A-Z]/) ? 'pi-check-circle' : 'pi-circle'}`"></i>
                            Mixed case letters (A-Z, a-z)
                        </li>
                        <li class="flex items-center gap-2" :class="form.password.match(/[0-9]/) ? 'text-green-600' : ''">
                            <i :class="`pi ${form.password.match(/[0-9]/) ? 'pi-check-circle' : 'pi-circle'}`"></i>
                            At least one number (0-9)
                        </li>
                        <li class="flex items-center gap-2" :class="form.password.match(/[^a-zA-Z0-9]/) ? 'text-green-600' : ''">
                            <i :class="`pi ${form.password.match(/[^a-zA-Z0-9]/) ? 'pi-check-circle' : 'pi-circle'}`"></i>
                            At least one special character (!@#$%^&*)
                        </li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <Button
                    @click="submit"
                    :loading="isSubmitting"
                    :disabled="!form.password || form.password !== form.password_confirmation || passwordStrength < 3 || isSubmitting"
                    class="w-full mb-4"
                    severity="primary"
                    label="Reset Password"
                    icon="pi pi-shield-check"
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
