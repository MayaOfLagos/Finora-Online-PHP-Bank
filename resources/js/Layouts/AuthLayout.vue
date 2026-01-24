<script setup>
/**
 * AuthLayout
 * Layout for authentication pages (Login, Register, etc.)
 */
import { computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLogo from '@/Components/Common/AppLogo.vue';
import { useDarkMode } from '@/Composables/useDarkMode';

const props = defineProps({
    title: {
        type: String,
        default: 'Welcome',
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const { initDarkMode } = useDarkMode();

onMounted(() => {
    initDarkMode();
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <Head :title="title" />

        <!-- Background Pattern -->
        <div class="fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-indigo-100 dark:bg-indigo-900/30 rounded-full opacity-50 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-100 dark:bg-purple-900/30 rounded-full opacity-50 blur-3xl"></div>
        </div>

        <div class="flex min-h-screen">
            <!-- Left Side - Branding (Desktop Only) -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 to-purple-700 p-12 flex-col justify-between relative overflow-hidden">
                <!-- Pattern Overlay -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                </div>

                <!-- Logo -->
                <div class="relative">
                    <Link href="/" class="inline-flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                            <span class="text-white font-bold text-xl">F</span>
                        </div>
                        <span class="text-white text-2xl font-bold">Finora Bank</span>
                    </Link>
                </div>

                <!-- Hero Content -->
                <div class="relative">
                    <h1 class="text-4xl font-bold text-white mb-4">
                        Banking Made Simple
                    </h1>
                    <p class="text-white/80 text-lg max-w-md">
                        Experience seamless banking with Finora. Transfer funds, manage accounts, and track your finances all in one place.
                    </p>

                    <!-- Features List -->
                    <div class="mt-8 space-y-4">
                        <div class="flex items-center gap-3 text-white/90">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="pi pi-shield text-sm"></i>
                            </div>
                            <span>Secure & Encrypted Transactions</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/90">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="pi pi-globe text-sm"></i>
                            </div>
                            <span>Global Wire Transfers</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/90">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="pi pi-mobile text-sm"></i>
                            </div>
                            <span>24/7 Mobile Banking</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="relative text-white/60 text-sm">
                    © {{ new Date().getFullYear() }} Finora Bank. All rights reserved.
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="flex-1 flex flex-col">
                <!-- Mobile Logo -->
                <div class="lg:hidden p-6">
                    <Link href="/">
                        <AppLogo size="lg" />
                    </Link>
                </div>

                <!-- Form Container -->
                <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
                    <div class="w-full max-w-md">
                        <!-- Header -->
                        <div class="mb-8">
                            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                {{ title }}
                            </h2>
                            <p v-if="subtitle" class="text-gray-600 dark:text-gray-400 mt-2">
                                {{ subtitle }}
                            </p>
                        </div>

                        <!-- Form Slot -->
                        <slot />
                    </div>
                </div>

                <!-- Mobile Footer -->
                <div class="lg:hidden p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    © {{ new Date().getFullYear() }} Finora Bank
                </div>
            </div>
        </div>
    </div>
</template>
