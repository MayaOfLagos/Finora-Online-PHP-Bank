<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import InputOtp from 'primevue/inputotp';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import AppLogo from '@/Components/Common/AppLogo.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';

const props = defineProps({
    email: String,
});

const page = usePage();
const toast = useToast();
const siteName = computed(() => page.props.settings?.general?.site_name || 'Finora Bank');
const siteLogo = computed(() => page.props.settings?.branding?.site_logo);
const siteLogoDark = computed(() => page.props.settings?.branding?.site_logo_dark);

// Dark mode toggle
const isDarkMode = ref(false);

// Track if OTP has been sent to prevent auto-resend on error
const hasInitialOtpSent = ref(false);

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }
    
    // Only auto-send OTP once on initial page load
    if (!hasInitialOtpSent.value) {
        hasInitialOtpSent.value = true;
        sendOtp();
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

const otpCode = ref('');
const isProcessing = ref(false);
const isResending = ref(false);
const countdown = ref(60);
const canResend = ref(false);

let countdownInterval = null;

const startCountdown = () => {
    canResend.value = false;
    countdown.value = 60;
    
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
    
    countdownInterval = setInterval(() => {
        countdown.value--;
        
        if (countdown.value <= 0) {
            clearInterval(countdownInterval);
            canResend.value = true;
        }
    }, 1000);
};

onUnmounted(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});

const sendOtp = () => {
    if (isResending.value || (!canResend.value && countdown.value > 0 && countdown.value < 60)) return;
    
    isResending.value = true;
    
    router.post(route('verify-email-otp.send'), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Code Sent',
                detail: 'Verification code has been sent to your email.',
                life: 4000,
            });
            startCountdown();
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: errors.otp || 'Failed to send OTP. Please try again.',
                life: 4000,
            });
        },
        onFinish: () => {
            isResending.value = false;
        },
    });
};

const submit = () => {
    if (otpCode.value.length !== 6) {
        toast.add({
            severity: 'warn',
            summary: 'Invalid Code',
            detail: 'Please enter the complete 6-digit code.',
            life: 3000,
        });
        return;
    }

    isProcessing.value = true;
    
    router.post(route('verify-email-otp.verify'), {
        otp: otpCode.value
    }, {
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Verification Failed',
                detail: errors.otp || 'Invalid or expired code. Please try again.',
                life: 4000,
            });
            otpCode.value = '';
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <Head :title="'Verify Email - ' + siteName" />
    
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
                        Email Verification
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400">
                        Enter the 6-digit code we sent to
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 font-semibold mt-1">
                        {{ email }}
                    </p>
                </div>
                
                <!-- Verification Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 p-8 transition-colors duration-300">
                    
                    <!-- OTP Input -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="flex flex-col items-center">
                            <InputOtp 
                                v-model="otpCode" 
                                :length="6" 
                                :integerOnly="true"
                                autofocus
                            />
                        </div>

                        <!-- Verify Button -->
                        <Button
                            type="submit"
                            :loading="isProcessing"
                            :disabled="isProcessing || otpCode.length !== 6"
                            class="w-full justify-center"
                            size="large"
                        >
                            <template #default>
                                <i v-if="!isProcessing" class="pi pi-check mr-2"></i>
                                <span>Verify Code</span>
                            </template>
                        </Button>

                        <!-- Resend Section -->
                        <div class="text-center space-y-3">
                            <div v-if="!canResend" class="text-sm text-gray-600 dark:text-gray-400">
                                Resend code in <span class="font-semibold">{{ countdown }}s</span>
                            </div>
                            
                            <Button 
                                v-else
                                type="button"
                                :loading="isResending" 
                                @click="sendOtp"
                                severity="secondary"
                                outlined
                                class="w-full justify-center"
                            >
                                <template #default>
                                    <i v-if="!isResending" class="pi pi-refresh mr-2"></i>
                                    <span>Resend Code</span>
                                </template>
                            </Button>
                        </div>
                    </form>
                    
                    <!-- Security Notice -->
                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                            <i class="pi pi-info-circle text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0"></i>
                            <div class="text-sm text-blue-800 dark:text-blue-300">
                                <strong>Security Tip:</strong> Never share your verification code with anyone. We will never ask for your code via phone.
                            </div>
                        </div>
                    </div>

                    <!-- Logout Link -->
                    <div class="mt-6 text-center">
                        <button 
                            @click="logout"
                            class="text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                        >
                            <i class="pi pi-sign-out mr-2"></i>
                            Sign Out
                        </button>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="mt-8 text-center">
                    <CopyrightText textClass="text-sm text-gray-500 dark:text-gray-400" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.p-inputotp) {
    gap: 0.5rem;
}

:deep(.p-inputotp-input) {
    width: 3rem;
    height: 3.5rem;
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
    border-radius: 0.75rem;
}

@media (max-width: 640px) {
    :deep(.p-inputotp-input) {
        width: 2.5rem;
        height: 3rem;
        font-size: 1.25rem;
    }
}
</style>
