<script setup>
/**
 * Deposits Hub
 * Choose between mobile, check, and crypto deposits
 */
import { Head, Link, router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Message from 'primevue/message';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    depositMethods: {
        type: Array,
        default: () => []
    },
    enabledGateways: {
        type: Array,
        default: () => []
    },
    cryptocurrencies: {
        type: Array,
        default: () => []
    }
});

const navigateTo = (route) => {
    router.visit(route);
};
</script>

<template>
    <Head title="Deposits" />

    <DashboardLayout title="Deposits">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Deposits
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Choose a deposit method to add funds to your account
            </p>
        </div>

        <!-- Deposit Methods Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <Card
                v-for="method in depositMethods"
                :key="method.id"
                :class="[
                    'overflow-hidden transition-all duration-300',
                    method.enabled ? 'cursor-pointer hover:shadow-lg hover:-translate-y-1' : 'opacity-60 cursor-not-allowed'
                ]"
                @click="method.enabled && navigateTo(method.route)"
            >
                <template #content>
                    <div class="flex flex-col h-full">
                        <!-- Icon -->
                        <div :class="[
                            'w-14 h-14 rounded-xl flex items-center justify-center mb-4 bg-gradient-to-br',
                            method.color
                        ]">
                            <i :class="['pi', method.icon, 'text-white text-2xl']"></i>
                        </div>

                        <!-- Content -->
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ method.name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">
                            {{ method.description }}
                        </p>

                        <!-- Features -->
                        <div class="space-y-2 mb-4">
                            <div v-for="feature in method.features" :key="feature" class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <i class="pi pi-check text-green-600 dark:text-green-400"></i>
                                {{ feature }}
                            </div>
                        </div>

                        <!-- Action -->
                        <Button
                            :label="method.enabled ? 'Get Started' : 'Unavailable'"
                            :icon="method.enabled ? 'pi pi-arrow-right' : 'pi pi-lock'"
                            :disabled="!method.enabled"
                            class="w-full"
                            @click.stop="method.enabled && navigateTo(method.route)"
                        />
                    </div>
                </template>
            </Card>
        </div>

        <!-- Info Messages -->
        <div class="mt-8 space-y-4">
            <Message v-if="!enabledGateways.length" severity="warning" :closable="false">
                <p>No payment gateways are currently enabled. Contact support for mobile deposits.</p>
            </Message>

            <Message v-if="!cryptocurrencies.length" severity="warning" :closable="false">
                <p>No cryptocurrencies are currently available. Check back soon.</p>
            </Message>

            <Message severity="info" :closable="false">
                <p><strong>Deposit Limits:</strong> Daily limits apply to each deposit method. Check the specific deposit page for details.</p>
            </Message>
        </div>

        <!-- Recent Deposits Section -->
        <div class="mt-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                How Deposits Work
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Mobile Deposit -->
                <Card class="border-l-4 border-l-blue-500">
                    <template #content>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900">
                                    <i class="pi pi-credit-card text-blue-600 dark:text-blue-400"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Mobile Deposit</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pay via Stripe, PayPal, Paystack, or other gateways. Instant funding.</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Check Deposit -->
                <Card class="border-l-4 border-l-green-500">
                    <template #content>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 dark:bg-green-900">
                                    <i class="pi pi-image text-green-600 dark:text-green-400"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Check Deposit</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Upload check images. Verified by admin. Holds apply.</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Crypto Deposit -->
                <Card class="border-l-4 border-l-orange-500">
                    <template #content>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900">
                                    <i class="pi pi-bitcoin text-orange-600 dark:text-orange-400"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Crypto Deposit</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Send crypto to wallet. Verified by admin. Auto-credited.</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </DashboardLayout>
</template>
