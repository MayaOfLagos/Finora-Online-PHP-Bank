<script setup>
/**
 * ImpersonationBanner
 * Shows a banner when admin is impersonating a user
 * Provides a way to stop impersonation
 */
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Button from 'primevue/button';

const page = usePage();
const isImpersonating = computed(() => page.props.auth?.isImpersonating ?? false);
const user = computed(() => page.props.auth?.user);

const isLoading = ref(false);

const stopImpersonation = () => {
    isLoading.value = true;
    router.post('/admin/stop-impersonation', {}, {
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="-translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-full opacity-0"
    >
        <div
            v-if="isImpersonating"
            class="fixed top-0 left-0 right-0 z-[100] bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 text-white shadow-lg"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-12 sm:h-10">
                    <!-- Left: Icon and Message -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-7 h-7 bg-white/20 rounded-full">
                            <i class="pi pi-eye text-sm"></i>
                        </div>
                        <div class="flex items-center gap-2 text-sm font-medium">
                            <span class="hidden sm:inline">You are viewing as:</span>
                            <span class="sm:hidden">Viewing as:</span>
                            <span class="font-bold">{{ user?.full_name || user?.email }}</span>
                            <span class="hidden md:inline text-white/80">({{ user?.email }})</span>
                        </div>
                    </div>

                    <!-- Right: Stop Button -->
                    <Button
                        @click="stopImpersonation"
                        :loading="isLoading"
                        size="small"
                        severity="contrast"
                        class="!bg-white !text-orange-600 hover:!bg-orange-50 !border-0 !shadow-md font-semibold"
                    >
                        <template #icon>
                            <i class="pi pi-sign-out mr-2"></i>
                        </template>
                        <span class="hidden sm:inline">Stop Impersonation</span>
                        <span class="sm:hidden">Stop</span>
                    </Button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* Pulsing animation for the banner */
@keyframes pulse-subtle {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.95;
    }
}
</style>
