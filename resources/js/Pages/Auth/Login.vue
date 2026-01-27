<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Toast from 'primevue/toast';
import AppLogo from '@/Components/Common/AppLogo.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';
import ReCaptcha from '@/Components/Common/ReCaptcha.vue';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    recaptcha: {
        type: Object,
        default: () => ({ enabled: false, siteKey: '', version: 'v2' }),
    },
});

const page = usePage();
const toast = useToast();
const siteName = computed(() => page.props.settings?.general?.site_name || 'Finora Bank');
const siteLogo = computed(() => page.props.settings?.branding?.site_logo);
const siteLogoDark = computed(() => page.props.settings?.branding?.site_logo_dark);

// Dark mode toggle
const isDarkMode = ref(false);

onMounted(() => {
    // Check for saved preference or system preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }
});

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const form = useForm({
    email: '',
    password: '',
    remember: false,
    recaptcha_token: '',
});

const isLoading = ref(false);
const recaptchaRef = ref(null);

const submit = async () => {
    isLoading.value = true;
    
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
            isLoading.value = false;
            return;
        }
    }
    
    form.post(route('login'), {
        onError: (errors) => {
            // Reset reCAPTCHA on error
            if (props.recaptcha?.enabled && props.recaptcha?.version === 'v2' && recaptchaRef.value) {
                recaptchaRef.value.reset();
            }
            
            const errorMessage = errors.recaptcha_token || errors.email || 'Invalid email or password. Please try again.';
            toast.add({
                severity: 'error',
                summary: 'Login Failed',
                detail: errorMessage,
                life: 3000,
            });
        },
        onFinish: () => {
            isLoading.value = false;
            form.reset('password');
            form.recaptcha_token = '';
        },
    });
};

const goToRegister = () => {
    router.visit(route('register'));
};
</script>

<template>
    <Head :title="'Sign In - ' + siteName" />
    
    <Toast />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-gray-950 dark:via-gray-900 dark:to-slate-950 flex flex-col transition-colors duration-300">
        
        <!-- Top Bar with Theme Toggle -->
        <div class="w-full py-4 px-6">
            <div class="max-w-md mx-auto flex justify-end">
                <button 
                    @click="toggleDarkMode"
                    class="p-2.5 rounded-xl bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow-md"
                    :title="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                >
                    <i :class="isDarkMode ? 'pi pi-sun' : 'pi pi-moon'" class="text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex items-center justify-center px-4 pb-8">
            <div class="w-full max-w-md">
                
                <!-- Logo Section -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center mb-6">
                        <!-- Light Mode Logo -->
                        <img 
                            v-if="siteLogo" 
                            :src="siteLogo" 
                            :alt="siteName"
                            class="h-14 w-auto dark:hidden"
                        />
                        <!-- Dark Mode Logo -->
                        <img 
                            v-if="siteLogoDark" 
                            :src="siteLogoDark" 
                            :alt="siteName"
                            class="h-14 w-auto hidden dark:block"
                        />
                        <!-- Fallback Logo -->
                        <div v-if="!siteLogo && !siteLogoDark" class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-primary-600 to-primary-700 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/25">
                                <span class="text-2xl font-bold text-white">F</span>
                            </div>
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ siteName }}
                            </span>
                        </div>
                    </div>
                    
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Sign in to your account
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400">
                        Enter your credentials to access your dashboard
                    </p>
                </div>
                
                <!-- Login Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 p-8 transition-colors duration-300">
                    
                    <!-- Login Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email Address
                            </label>
                            <IconField>
                                <InputIcon class="pi pi-envelope" />
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="name@example.com"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.email }"
                                    required
                                    autofocus
                                />
                            </IconField>
                            <small v-if="form.errors.email" class="text-red-500 mt-1.5 block text-sm">
                                {{ form.errors.email }}
                            </small>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 z-10 pointer-events-none">
                                    <i class="pi pi-lock"></i>
                                </span>
                                <Password
                                    id="password"
                                    v-model="form.password"
                                    placeholder="Enter your password"
                                    toggleMask
                                    :feedback="false"
                                    class="w-full"
                                    inputClass="w-full"
                                    :class="{ 'p-invalid': form.errors.password }"
                                    required
                                />
                            </div>
                            <small v-if="form.errors.password" class="text-red-500 mt-1.5 block text-sm">
                                {{ form.errors.password }}
                            </small>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    v-model="form.remember"
                                    inputId="remember"
                                    :binary="true"
                                />
                                <label for="remember" class="text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                                    Remember me
                                </label>
                            </div>
                            
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                            >
                                Forgot password?
                            </Link>
                        </div>

                        <!-- reCAPTCHA -->
                        <div v-if="recaptcha?.enabled" class="flex justify-center">
                            <ReCaptcha
                                ref="recaptchaRef"
                                :site-key="recaptcha.siteKey"
                                :version="recaptcha.version"
                                :theme="isDarkMode ? 'dark' : 'light'"
                                action="login"
                                @verify="(token) => form.recaptcha_token = token"
                                @expire="() => form.recaptcha_token = ''"
                            />
                        </div>
                        <small v-if="form.errors.recaptcha_token" class="text-red-500 text-sm text-center block">
                            {{ form.errors.recaptcha_token }}
                        </small>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            :loading="isLoading || form.processing"
                            :disabled="isLoading || form.processing"
                            class="w-full justify-center"
                            size="large"
                        >
                            <template #default>
                                <i v-if="!isLoading && !form.processing" class="pi pi-sign-in mr-2"></i>
                                <span>Sign In</span>
                            </template>
                        </Button>
                    </form>
                    
                    <!-- Register Link -->
                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account?
                            <Link 
                                :href="route('register')" 
                                class="font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors ml-1"
                            >
                                Create account
                            </Link>
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="mt-8 text-center">
                    <CopyrightText text-class="text-sm text-gray-500 dark:text-gray-500" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.p-password) {
    width: 100%;
}

:deep(.p-password-input) {
    padding-left: 2.5rem !important;
    width: 100%;
}

:deep(.p-inputtext) {
    padding-left: 2.5rem;
}

:deep(.p-iconfield .p-inputtext) {
    padding-left: 2.5rem;
}
</style>
