<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-800">
        <!-- Navigation -->
        <nav class="absolute top-0 w-full p-6">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <span class="text-indigo-600 font-bold text-xl">F</span>
                    </div>
                    <span class="text-white text-xl font-semibold">Finora Bank</span>
                </div>

                <div class="flex items-center space-x-4">
                    <template v-if="canLogin">
                        <Link
                            href="/login"
                            class="text-white/80 hover:text-white transition-colors"
                        >
                            Sign In
                        </Link>
                    </template>
                    <template v-if="canRegister">
                        <Link
                            href="/register"
                            class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-medium hover:bg-indigo-50 transition-colors"
                        >
                            Get Started
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="flex items-center justify-center min-h-screen px-6">
            <div class="text-center max-w-4xl">
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6">
                    Banking Made
                    <span class="bg-gradient-to-r from-cyan-400 to-purple-400 text-transparent bg-clip-text">
                        Simple
                    </span>
                </h1>
                <p class="text-xl text-white/70 mb-10 max-w-2xl mx-auto">
                    Experience modern banking with Finora. Send money globally, manage your accounts,
                    and take control of your finances - all in one place.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        href="/register"
                        class="bg-white text-indigo-600 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-indigo-50 transition-all hover:shadow-lg hover:shadow-white/20"
                    >
                        Open Free Account
                    </Link>
                    <Link
                        href="/login"
                        class="border-2 border-white/30 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-white/10 transition-all"
                    >
                        Sign In
                    </Link>
                </div>

                <!-- Features -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-20">
                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-left">
                        <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">Wire Transfers</h3>
                        <p class="text-white/60">Send money internationally with competitive rates and secure verification.</p>
                    </div>

                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-left">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">Virtual Cards</h3>
                        <p class="text-white/60">Create virtual cards instantly for secure online payments.</p>
                    </div>

                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-left">
                        <div class="w-12 h-12 bg-pink-500/20 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">Bank-Grade Security</h3>
                        <p class="text-white/60">Multi-layer security with PIN, OTP, and verification codes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
