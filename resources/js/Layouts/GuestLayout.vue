<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ProgressSpinner from 'primevue/progressspinner';

defineProps({
    title: String,
});

const page = usePage();
const siteName = computed(() => page.props.settings?.general?.site_name || 'Finora Bank');
const siteLogo = computed(() => page.props.settings?.branding?.site_logo);
const siteLogoDark = computed(() => page.props.settings?.branding?.site_logo_dark);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-950 flex items-center justify-center p-4 relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-indigo-200/20 to-transparent dark:from-indigo-600/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-gradient-to-tl from-blue-200/20 to-transparent dark:from-blue-600/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <!-- Content Container -->
        <div class="w-full max-w-md relative z-10">
            <!-- Logo Section -->
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-block mb-4">
                    <img 
                        v-if="siteLogo" 
                        :src="siteLogo" 
                        :alt="siteName"
                        class="h-16 w-auto mx-auto dark:hidden"
                    />
                    <img 
                        v-if="siteLogoDark" 
                        :src="siteLogoDark" 
                        :alt="siteName"
                        class="h-16 w-auto mx-auto hidden dark:block"
                    />
                    <div v-if="!siteLogo && !siteLogoDark" class="flex items-center justify-center gap-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-600 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="pi pi-building text-2xl text-white"></i>
                        </div>
                        <span class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">
                            {{ siteName }}
                        </span>
                    </div>
                </div>
                <h1 v-if="title" class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ title }}
                </h1>
            </div>

            <!-- Main Card -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden animate-scale-in">
                <slot />
            </div>

            <!-- Footer Links -->
            <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400 animate-fade-in" style="animation-delay: 0.2s;">
                <slot name="footer">
                    <p>&copy; {{ new Date().getFullYear() }} {{ siteName }}. All rights reserved.</p>
                </slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scale-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.5s ease-out forwards;
}

.animate-scale-in {
    animation: scale-in 0.5s ease-out forwards;
}
</style>
