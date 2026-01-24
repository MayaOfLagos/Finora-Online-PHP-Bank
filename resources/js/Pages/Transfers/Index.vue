<script setup>
/**
 * Transfers Index Page
 * Hub for all transfer types
 */
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';

import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    recentTransfers: {
        type: Array,
        default: () => []
    }
});

const { formatCurrency } = useCurrency();

// Transfer types
const transferTypes = [
    {
        id: 'internal',
        name: 'Internal Transfer',
        description: 'Transfer funds to another Finora Bank user',
        icon: 'pi-users',
        color: 'from-blue-500 to-blue-600',
        route: '/transfers/internal',
        features: ['Instant transfer', 'No fees', 'Same-day processing']
    },
    {
        id: 'account',
        name: 'Account to Account',
        description: 'Transfer between your own accounts',
        icon: 'pi-sync',
        color: 'from-green-500 to-green-600',
        route: '/transfers/account-to-account',
        features: ['Instant transfer', 'No fees', 'Between your accounts']
    },
    {
        id: 'domestic',
        name: 'Domestic Transfer',
        description: 'Transfer to other local banks',
        icon: 'pi-building',
        color: 'from-purple-500 to-purple-600',
        route: '/transfers/domestic',
        features: ['1-3 business days', 'Low fees', 'Local banks only']
    },
    {
        id: 'wire',
        name: 'Wire Transfer',
        description: 'International wire transfers',
        icon: 'pi-globe',
        color: 'from-orange-500 to-orange-600',
        route: '/transfers/wire',
        features: ['International', 'SWIFT network', '3-5 business days']
    }
];

const navigateToTransfer = (route) => {
    router.visit(route);
};

// Get primary account balance
const primaryBalance = computed(() => {
    const primary = props.accounts.find(a => a.is_primary);
    return primary ? primary.balance : 0;
});

const primaryCurrency = computed(() => {
    const primary = props.accounts.find(a => a.is_primary);
    return primary ? primary.currency : 'USD';
});
</script>

<template>
    <Head title="Transfers" />

    <DashboardLayout title="Transfers">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Transfers
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Choose a transfer type to send money
            </p>
        </div>

        <!-- Quick Balance Overview -->
        <Card class="mb-6 bg-gradient-to-r from-indigo-500 to-indigo-600">
            <template #content>
                <div class="flex items-center justify-between text-white">
                    <div>
                        <p class="text-sm opacity-80">Available Balance (Primary)</p>
                        <p class="mt-1 text-3xl font-bold">
                            {{ formatCurrency(primaryBalance, primaryCurrency) }}
                        </p>
                    </div>
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-white/20">
                        <i class="text-3xl pi pi-wallet"></i>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Transfer Types Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Card
                v-for="type in transferTypes"
                :key="type.id"
                class="overflow-hidden transition-all duration-300 cursor-pointer hover:shadow-lg hover:-translate-y-1"
                @click="navigateToTransfer(type.route)"
            >
                <template #content>
                    <div class="flex items-start gap-4">
                        <div :class="[
                            'w-14 h-14 rounded-xl flex items-center justify-center bg-gradient-to-br',
                            type.color
                        ]">
                            <i :class="['pi', type.icon, 'text-white text-2xl']"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ type.name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ type.description }}
                            </p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <span
                                    v-for="feature in type.features"
                                    :key="feature"
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    {{ feature }}
                                </span>
                            </div>
                        </div>
                        <i class="text-gray-400 pi pi-chevron-right dark:text-gray-500"></i>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Recent Transfers -->
        <div v-if="recentTransfers.length > 0" class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Recent Transfers
                </h2>
                <Link
                    href="/transactions"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                >
                    View All
                </Link>
            </div>

            <Card>
                <template #content>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="transfer in recentTransfers.slice(0, 5)"
                            :key="transfer.id"
                            class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700">
                                    <i class="text-gray-600 pi pi-send dark:text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ transfer.description }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ transfer.created_at }}
                                    </p>
                                </div>
                            </div>
                            <span class="font-semibold text-red-600 dark:text-red-400">
                                -{{ formatCurrency(transfer.amount, transfer.currency) }}
                            </span>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- No Accounts Warning -->
        <div v-if="accounts.length === 0" class="p-6 mt-6 text-center border-2 border-dashed rounded-xl border-gray-300 dark:border-gray-600">
            <i class="mb-4 text-4xl text-gray-400 pi pi-wallet"></i>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                No Accounts Available
            </h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">
                You need at least one active account to make transfers.
            </p>
            <Link href="/accounts">
                <Button
                    label="Open an Account"
                    icon="pi pi-plus"
                    class="mt-4"
                />
            </Link>
        </div>
    </DashboardLayout>
</template>
