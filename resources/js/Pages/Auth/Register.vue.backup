<script setup>
/**
 * Register Page
 * New user registration
 */
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';

// Form state
const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

// Submit handler
const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

// Password strength calculation
const passwordStrength = computed(() => {
    const password = form.password;
    if (!password) return { score: 0, label: '', color: '' };

    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score <= 2) return { score, label: 'Weak', color: 'bg-red-500' };
    if (score <= 4) return { score, label: 'Medium', color: 'bg-yellow-500' };
    return { score, label: 'Strong', color: 'bg-green-500' };
});

// Show password
const showPassword = ref(false);
const showConfirmPassword = ref(false);
</script>

<template>
    <AuthLayout
        title="Create Account"
        subtitle="Join Finora Bank and start banking smarter"
    >
        <!-- Register Form -->
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Full Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Full Name
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="pi pi-user"></i>
                    </span>
                    <InputText
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="John Doe"
                        class="w-full pl-10"
                        :class="{ 'p-invalid': form.errors.name }"
                        autocomplete="name"
                        required
                        autofocus
                    />
                </div>
                <small v-if="form.errors.name" class="text-red-500">
                    {{ form.errors.name }}
                </small>
            </div>

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
                    />
                </div>
                <small v-if="form.errors.email" class="text-red-500">
                    {{ form.errors.email }}
                </small>
            </div>

            <!-- Phone (Optional) -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Phone Number <span class="text-gray-400 dark:text-gray-500">(Optional)</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="pi pi-phone"></i>
                    </span>
                    <InputText
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        placeholder="+1 (555) 123-4567"
                        class="w-full pl-10"
                        :class="{ 'p-invalid': form.errors.phone }"
                        autocomplete="tel"
                    />
                </div>
                <small v-if="form.errors.phone" class="text-red-500">
                    {{ form.errors.phone }}
                </small>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Password
                </label>
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
                        autocomplete="new-password"
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

                <!-- Password Strength Indicator -->
                <div v-if="form.password" class="mt-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div
                                :class="[
                                    'h-full transition-all duration-300',
                                    passwordStrength.color
                                ]"
                                :style="{ width: `${(passwordStrength.score / 6) * 100}%` }"
                            ></div>
                        </div>
                        <span class="text-xs font-medium" :class="{
                            'text-red-500': passwordStrength.label === 'Weak',
                            'text-yellow-600': passwordStrength.label === 'Medium',
                            'text-green-600': passwordStrength.label === 'Strong',
                        }">
                            {{ passwordStrength.label }}
                        </span>
                    </div>
                </div>

                <small v-if="form.errors.password" class="text-red-500">
                    {{ form.errors.password }}
                </small>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Confirm Password
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="pi pi-lock"></i>
                    </span>
                    <InputText
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-10"
                        :class="{ 'p-invalid': form.errors.password_confirmation }"
                        autocomplete="new-password"
                        required
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        @click="showConfirmPassword = !showConfirmPassword"
                    >
                        <i :class="showConfirmPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
                    </button>
                </div>
                <small v-if="form.errors.password_confirmation" class="text-red-500">
                    {{ form.errors.password_confirmation }}
                </small>
            </div>

            <!-- Terms -->
            <div class="flex items-start gap-2">
                <Checkbox
                    v-model="form.terms"
                    inputId="terms"
                    :binary="true"
                    :class="{ 'p-invalid': form.errors.terms }"
                />
                <label for="terms" class="text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                    I agree to the
                    <a href="/terms" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Terms of Service</a>
                    and
                    <a href="/privacy" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Privacy Policy</a>
                </label>
            </div>
            <small v-if="form.errors.terms" class="text-red-500 block -mt-3">
                {{ form.errors.terms }}
            </small>

            <!-- Submit Button -->
            <Button
                type="submit"
                label="Create Account"
                icon="pi pi-user-plus"
                class="w-full"
                size="large"
                :loading="form.processing"
                :disabled="form.processing || !form.terms"
            />

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400">Already have an account?</span>
                </div>
            </div>

            <!-- Login Link -->
            <Link :href="route('login')">
                <Button
                    label="Sign In Instead"
                    class="w-full"
                    severity="secondary"
                    outlined
                />
            </Link>
        </form>
    </AuthLayout>
</template>
