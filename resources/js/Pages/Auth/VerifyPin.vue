<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import Skeleton from 'primevue/skeleton';
import AppLogo from '@/Components/Common/AppLogo.vue';
import SeoHead from '@/Components/Common/SeoHead.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';

const page = usePage();
const toast = useToast();
const siteName = computed(() => page.props.settings?.general?.site_name || page.props.settings?.general?.app_name || 'Finora Bank');
const siteLogo = computed(() => page.props.settings?.branding?.logo_light);
const siteLogoDark = computed(() => page.props.settings?.branding?.logo_dark);

// Page loading state
const isPageLoading = ref(true);

// Dark mode toggle
const isDarkMode = ref(false);

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }
    
    // Simulate brief loading for skeleton effect
    setTimeout(() => {
        isPageLoading.value = false;
    }, 300);
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

const pinCode = ref('');
const isProcessing = ref(false);
const maxPinLength = 6;

const pinDisplay = computed(() => {
    return '•'.repeat(pinCode.value.length).padEnd(maxPinLength, '·');
});

const addDigit = (digit) => {
    if (pinCode.value.length < maxPinLength) {
        pinCode.value += digit;
    }
};

const clearPin = () => {
    pinCode.value = '';
};

const backspace = () => {
    if (pinCode.value.length > 0) {
        pinCode.value = pinCode.value.slice(0, -1);
    }
};

const submit = () => {
    if (pinCode.value.length < 4) {
        toast.add({
            severity: 'warn',
            summary: 'Invalid PIN',
            detail: 'PIN must be at least 4 digits.',
            life: 3000,
        });
        return;
    }

    isProcessing.value = true;
    
    router.post(route('verify-pin.verify'), {
        pin: pinCode.value
    }, {
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Verification Failed',
                detail: errors.pin || 'Invalid PIN code. Please try again.',
                life: 4000,
            });
            pinCode.value = '';
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const cancel = () => {
    pinCode.value = '';
    toast.add({
        severity: 'info',
        summary: 'Cancelled',
        detail: 'PIN entry cancelled.',
        life: 2000,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const numberPad = [
    ['1', '2', '3'],
    ['4', '5', '6'],
    ['7', '8', '9'],
    ['clear', '0', 'backspace'],
];
</script>

<template>
    <SeoHead :title="'Verify PIN'" />
    
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
                
                <!-- Skeleton Loading State -->
                <div v-if="isPageLoading" class="animate-pulse">
                    <!-- Logo Skeleton -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center mb-6">
                            <Skeleton width="3.5rem" height="3.5rem" class="rounded-xl" />
                        </div>
                        <Skeleton width="50%" height="2rem" class="mx-auto mb-2" />
                        <Skeleton width="70%" height="1rem" class="mx-auto" />
                    </div>
                    
                    <!-- Card Skeleton -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 space-y-6">
                        <!-- PIN display skeleton -->
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                            <Skeleton width="70%" height="3rem" class="mx-auto" />
                            <Skeleton width="30%" height="0.75rem" class="mx-auto mt-3" />
                        </div>
                        <!-- Number pad skeleton -->
                        <div class="grid grid-cols-3 gap-3">
                            <Skeleton v-for="i in 12" :key="i" class="aspect-square rounded-xl" />
                        </div>
                        <Skeleton height="3rem" class="w-full" />
                    </div>
                </div>
                
                <!-- Actual Content -->
                <div v-else class="animate-fade-in">
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
                        Enter Your PIN
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400">
                        Please enter your transaction PIN to continue
                    </p>
                </div>
                
                <!-- PIN Verification Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 p-8 transition-colors duration-300">
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- PIN Display -->
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                            <div class="text-center">
                                <div class="text-4xl font-mono tracking-[0.5em] text-gray-900 dark:text-white min-h-[3rem] flex items-center justify-center select-none">
                                    {{ pinDisplay }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                                    {{ pinCode.length }} / {{ maxPinLength }} digits
                                </div>
                            </div>
                        </div>

                        <!-- Number Pad -->
                        <div class="grid grid-cols-3 gap-3">
                            <template v-for="(row, rowIndex) in numberPad" :key="rowIndex">
                                <template v-for="(button, colIndex) in row" :key="colIndex">
                                    <!-- Number Buttons -->
                                    <button
                                        v-if="!isNaN(button)"
                                        type="button"
                                        @click="addDigit(button)"
                                        :disabled="isProcessing"
                                        class="aspect-square rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 active:scale-95 transition-all border border-gray-200 dark:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                    >
                                        <span class="text-2xl font-semibold text-gray-900 dark:text-white">{{ button }}</span>
                                    </button>

                                    <!-- Clear Button -->
                                    <button
                                        v-else-if="button === 'clear'"
                                        type="button"
                                        @click="clearPin"
                                        :disabled="isProcessing || pinCode.length === 0"
                                        class="aspect-square rounded-xl bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 active:scale-95 transition-all border border-red-200 dark:border-red-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                    >
                                        <span class="text-sm font-medium text-red-700 dark:text-red-400">Clear</span>
                                    </button>

                                    <!-- Backspace Button -->
                                    <button
                                        v-else-if="button === 'backspace'"
                                        type="button"
                                        @click="backspace"
                                        :disabled="isProcessing || pinCode.length === 0"
                                        class="aspect-square rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 active:scale-95 transition-all border border-gray-200 dark:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center shadow-sm"
                                    >
                                        <i class="pi pi-arrow-left text-xl text-gray-700 dark:text-gray-200"></i>
                                    </button>
                                </template>
                            </template>
                        </div>

                        <!-- Verify Button -->
                        <Button
                            type="submit"
                            :loading="isProcessing"
                            :disabled="isProcessing || pinCode.length < 4"
                            class="w-full justify-center"
                            size="large"
                        >
                            <template #default>
                                <i v-if="!isProcessing" class="pi pi-check mr-2"></i>
                                <span>Verify PIN</span>
                            </template>
                        </Button>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3">
                            <Button 
                                type="button"
                                @click="cancel"
                                :disabled="isProcessing"
                                severity="secondary"
                                outlined
                                class="w-full justify-center"
                            >
                                <i class="pi pi-times mr-2"></i>
                                <span>Cancel</span>
                            </Button>

                            <Button 
                                type="button"
                                @click="logout"
                                severity="danger"
                                outlined
                                class="w-full justify-center"
                            >
                                <i class="pi pi-sign-out mr-2"></i>
                                <span>Sign Out</span>
                            </Button>
                        </div>
                    </form>

                    <!-- Security Notice -->
                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                            <i class="pi pi-exclamation-triangle text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                            <div class="text-sm text-amber-800 dark:text-amber-300">
                                <strong>Security Notice:</strong> Your transaction PIN is required to access your dashboard. If you've forgotten your PIN, please contact support.
                            </div>
                        </div>
                    </div>
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
