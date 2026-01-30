<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import Button from 'primevue/button';
import Skeleton from 'primevue/skeleton';
import AppLogo from '@/Components/Common/AppLogo.vue';
import SeoHead from '@/Components/Common/SeoHead.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';
import PagePreloader from '@/Components/Common/PagePreloader.vue';

const props = defineProps({
    status: {
        type: String,
    },
});

const toast = useToast();
const form = useForm({});

// Page loading state
const isPageLoading = ref(true);
const showContent = ref(false);

const handlePreloaderComplete = () => {
    setTimeout(() => {
        showContent.value = true;
    }, 100);
};

onMounted(() => {
    // Page loading is now handled by PagePreloader
});

const submit = () => {
    form.post(route('verification.send'), {
        onSuccess: () => {
            toast.success('Verification link has been sent to your email.', 'Email Sent');
        },
        onError: () => {
            toast.error('Failed to send verification email. Please try again.', 'Error');
        },
    });
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <SeoHead title="Email Verification" />
    <PagePreloader :min-load-time="1200" @complete="handlePreloaderComplete" />
    
    <Transition
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
    >
    <div v-show="showContent" class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-gray-900 dark:via-slate-900 dark:to-gray-800 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <!-- Skeleton Loading State -->
            <div v-if="isPageLoading" class="animate-pulse">
                <div class="text-center mb-8">
                    <Skeleton width="4rem" height="4rem" class="mx-auto rounded-xl" />
                </div>
                
                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-xl space-y-6">
                    <div class="text-center">
                        <Skeleton width="4rem" height="4rem" class="mx-auto mb-4 rounded-full" />
                        <Skeleton width="60%" height="1.5rem" class="mx-auto mb-2" />
                        <Skeleton width="80%" height="1rem" class="mx-auto" />
                        <Skeleton width="70%" height="1rem" class="mx-auto mt-1" />
                    </div>
                    <Skeleton height="3rem" class="w-full" />
                    <div class="flex justify-between">
                        <Skeleton width="5rem" height="1rem" />
                        <Skeleton width="4rem" height="1rem" />
                    </div>
                </div>
            </div>
            
            <!-- Actual Content -->
            <div v-else class="animate-fade-in">
                <div class="text-center mb-8">
                    <AppLogo :show-text="false" size="lg" />
                </div>
            
                <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-xl">
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-amber-100 dark:bg-amber-900/30 rounded-full">
                        <i class="pi pi-envelope text-3xl text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Verify Your Email
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Thanks for signing up! Please verify your email address by clicking the link we sent you.
                    </p>
                </div>

                <div
                    v-if="verificationLinkSent"
                    class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl"
                >
                    <div class="flex items-center gap-3">
                        <i class="pi pi-check-circle text-green-600 dark:text-green-400"></i>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            A new verification link has been sent to your email address.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <Button
                        type="submit"
                        label="Resend Verification Email"
                        icon="pi pi-send"
                        class="w-full"
                        :loading="form.processing"
                    />
                </form>

                <div class="mt-6 flex items-center justify-between text-sm">
                    <Link
                        :href="route('dashboard')"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                    >
                        Skip for now
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-primary-600 hover:text-primary-700 font-medium"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
            </div>
            
            <div class="mt-6 text-center">
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
