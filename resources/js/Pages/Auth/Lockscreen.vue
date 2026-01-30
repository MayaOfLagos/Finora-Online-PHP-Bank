<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Skeleton from 'primevue/skeleton';
import SeoHead from '@/Components/Common/SeoHead.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';
import PagePreloader from '@/Components/Common/PagePreloader.vue';

const props = defineProps({
    user: Object,
    lockedAt: String,
});

const page = usePage();
const toast = useToast();
const siteName = computed(() => page.props.settings?.general?.site_name || page.props.settings?.general?.app_name || 'Finora Bank');
const siteLogo = computed(() => page.props.settings?.branding?.logo_light);
const siteLogoDark = computed(() => page.props.settings?.branding?.logo_dark);

// Page loading state
const isPageLoading = ref(true);
const showContent = ref(false);

const handlePreloaderComplete = () => {
    setTimeout(() => {
        showContent.value = true;
    }, 100);
};

// Dark mode toggle
const isDarkMode = ref(false);

onMounted(() => {
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

const pinCode = ref('');
const isProcessing = ref(false);
const maxPinLength = 6;

const pinDisplay = computed(() => {
    return '•'.repeat(pinCode.value.length).padEnd(maxPinLength, '·');
});

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

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

const unlock = () => {
    if (pinCode.value.length < 4) {
        toast.warn('PIN must be at least 4 digits.', 'Invalid PIN');
        return;
    }

    isProcessing.value = true;
    
    router.post(route('lockscreen.unlock'), {
        pin: pinCode.value
    }, {
        onSuccess: () => {
            toast.success('Welcome back!', 'Unlocked');
        },
        onError: (errors) => {
            toast.error(errors.pin || 'Invalid PIN code. Please try again.', 'Unlock Failed');
            pinCode.value = '';
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const logout = () => {
    router.post(route('lockscreen.logout'));
};

const numberPad = [
    ['1', '2', '3'],
    ['4', '5', '6'],
    ['7', '8', '9'],
    ['clear', '0', 'backspace'],
];
</script>

<template>
    <SeoHead :title="'Locked'" :no-index="true" />
    <PagePreloader :min-load-time="1200" @complete="handlePreloaderComplete" />
    
    <Transition
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
    >
    <div v-show="showContent" class="min-h-screen bg-gradient-to-br from-slate-900 via-gray-900 to-slate-950 flex flex-col transition-colors duration-300">
        
        <!-- Top Bar with Theme Toggle -->
        <div class="w-full py-4 px-6">
            <div class="max-w-md mx-auto flex justify-between items-center">
                <div class="text-gray-400 text-sm flex items-center gap-2">
                    <i class="pi pi-lock"></i>
                    <span>Session Locked</span>
                </div>
                <button 
                    @click="toggleDarkMode"
                    class="p-2.5 rounded-xl bg-gray-800/80 backdrop-blur-sm border border-gray-700 text-gray-300 hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow-md"
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
                    <!-- User Info Skeleton -->
                    <div class="text-center mb-8">
                        <div class="mb-6 relative inline-block">
                            <Skeleton width="6rem" height="6rem" class="rounded-full" />
                        </div>
                        <Skeleton width="60%" height="2rem" class="mx-auto mb-1" />
                        <Skeleton width="50%" height="1rem" class="mx-auto mb-2" />
                        <Skeleton width="40%" height="0.75rem" class="mx-auto" />
                    </div>
                    
                    <!-- Card Skeleton -->
                    <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-8 space-y-6">
                        <div class="text-center">
                            <Skeleton width="60%" height="1.25rem" class="mx-auto mb-1" />
                            <Skeleton width="80%" height="1rem" class="mx-auto" />
                        </div>
                        <!-- PIN display skeleton -->
                        <div class="bg-gray-900/50 rounded-xl p-6 border border-gray-700">
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
                    <!-- User Info Section -->
                    <div class="text-center mb-8">
                    <!-- Lock Icon Animation -->
                    <div class="mb-6 relative inline-block">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-2xl shadow-primary-500/30 ring-4 ring-primary-500/20">
                            <Avatar 
                                v-if="user?.avatar_url"
                                :image="user.avatar_url"
                                shape="circle"
                                size="xlarge"
                                class="w-20 h-20"
                            />
                            <span v-else class="text-3xl font-bold text-white">
                                {{ getInitials(user?.name) }}
                            </span>
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center border-4 border-gray-900">
                            <i class="pi pi-lock text-amber-400 text-lg"></i>
                        </div>
                    </div>
                    
                    <h1 class="text-2xl font-bold text-white mb-1">
                        Welcome back, {{ user?.name?.split(' ')[0] }}
                    </h1>
                    <p class="text-gray-400 text-sm">
                        {{ user?.email }}
                    </p>
                    <p class="text-gray-500 text-xs mt-2">
                        <i class="pi pi-clock mr-1"></i>
                        Locked {{ lockedAt }}
                    </p>
                </div>
                
                <!-- PIN Entry Card -->
                <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-700/50 p-8 transition-colors duration-300">
                    
                    <div class="text-center mb-6">
                        <h2 class="text-lg font-semibold text-white mb-1">
                            Enter PIN to Unlock
                        </h2>
                        <p class="text-gray-400 text-sm">
                            Your session was locked due to inactivity
                        </p>
                    </div>
                    
                    <form @submit.prevent="unlock" class="space-y-6">
                        <!-- PIN Display -->
                        <div class="bg-gray-900/50 rounded-xl p-6 border border-gray-700">
                            <div class="text-center">
                                <div class="text-4xl font-mono tracking-[0.5em] text-white min-h-[3rem] flex items-center justify-center select-none">
                                    {{ pinDisplay }}
                                </div>
                                <div class="text-xs text-gray-500 mt-3">
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
                                        class="aspect-square rounded-xl bg-gray-700 hover:bg-gray-600 active:scale-95 transition-all border border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                    >
                                        <span class="text-2xl font-semibold text-white">{{ button }}</span>
                                    </button>

                                    <!-- Clear Button -->
                                    <button
                                        v-else-if="button === 'clear'"
                                        type="button"
                                        @click="clearPin"
                                        :disabled="isProcessing || pinCode.length === 0"
                                        class="aspect-square rounded-xl bg-red-900/30 hover:bg-red-900/50 active:scale-95 transition-all border border-red-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                    >
                                        <span class="text-sm font-medium text-red-400">Clear</span>
                                    </button>

                                    <!-- Backspace Button -->
                                    <button
                                        v-else-if="button === 'backspace'"
                                        type="button"
                                        @click="backspace"
                                        :disabled="isProcessing || pinCode.length === 0"
                                        class="aspect-square rounded-xl bg-gray-700 hover:bg-gray-600 active:scale-95 transition-all border border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center shadow-sm"
                                    >
                                        <i class="pi pi-arrow-left text-xl text-gray-200"></i>
                                    </button>
                                </template>
                            </template>
                        </div>

                        <!-- Unlock Button -->
                        <Button
                            type="submit"
                            :loading="isProcessing"
                            :disabled="isProcessing || pinCode.length < 4"
                            class="w-full justify-center"
                            size="large"
                        >
                            <template #default>
                                <i v-if="!isProcessing" class="pi pi-unlock mr-2"></i>
                                <span>Unlock</span>
                            </template>
                        </Button>

                        <!-- Sign Out Button -->
                        <div class="text-center">
                            <button 
                                type="button"
                                @click="logout"
                                class="text-gray-400 hover:text-white text-sm transition-colors inline-flex items-center gap-2"
                            >
                                <i class="pi pi-sign-out"></i>
                                <span>Sign in with a different account</span>
                            </button>
                        </div>
                    </form>
                </div>
                </div>
                
                <!-- Footer -->
                <div class="mt-8 text-center">
                    <CopyrightText textClass="text-sm text-gray-500" />
                </div>
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

/* Subtle animation for lock icon */
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
    }
    50% {
        box-shadow: 0 0 40px rgba(245, 158, 11, 0.5);
    }
}

.pi-lock {
    animation: pulse-glow 2s ease-in-out infinite;
}
</style>
