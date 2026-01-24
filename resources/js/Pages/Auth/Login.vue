<script setup>
/**
 * Login Page
 * User authentication with email and password
 */
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
        default: '',
    },
});

// Form state
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Submit handler
const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// Show/hide password
const showPassword = ref(false);
</script>

<template>
    <AuthLayout
        title="Welcome Back"
        subtitle="Sign in to your account to continue"
    >
        <!-- Status Message -->
        <Message v-if="status" severity="success" :closable="false" class="mb-4">
            {{ status }}
        </Message>

        <!-- Login Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Address
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="pi pi-envelope"></i>
                    </span>
                    <InputText
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="name@example.com"
                        class="w-full pl-10"
                        :class="{ 'p-invalid': form.errors.email }"
                        autocomplete="username"
                        required
                        autofocus
                    />
                </div>
                <small v-if="form.errors.email" class="text-red-500">
                    {{ form.errors.email }}
                </small>
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
                    >
                        Forgot password?
                    </Link>
                </div>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="pi pi-lock"></i>
                    </span>
                    <InputText
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-10"
                        :class="{ 'p-invalid': form.errors.password }"
                        autocomplete="current-password"
                        required
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        @click="showPassword = !showPassword"
                    >
                        <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
                    </button>
                </div>
                <small v-if="form.errors.password" class="text-red-500">
                    {{ form.errors.password }}
                </small>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <Checkbox
                    v-model="form.remember"
                    inputId="remember"
                    :binary="true"
                />
                <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                    Remember me
                </label>
            </div>

            <!-- Submit Button -->
            <Button
                type="submit"
                label="Sign In"
                icon="pi pi-sign-in"
                class="w-full"
                size="large"
                :loading="form.processing"
                :disabled="form.processing"
            />

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400">New to Finora?</span>
                </div>
            </div>

            <!-- Register Link -->
            <Link :href="route('register')">
                <Button
                    label="Create an Account"
                    class="w-full"
                    severity="secondary"
                    outlined
                />
            </Link>
        </form>

        <!-- Security Notice -->
        <div class="mt-8 p-4 bg-gray-100 dark:bg-gray-800 rounded-xl">
            <div class="flex items-start gap-3">
                <i class="pi pi-shield text-green-600 dark:text-green-400 mt-0.5"></i>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    <p class="font-medium text-gray-700 dark:text-gray-300">Secure Connection</p>
                    <p class="mt-1">Your data is protected with bank-level encryption</p>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
