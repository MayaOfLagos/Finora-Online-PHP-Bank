<script setup>
/**
 * AccountStats Component
 * Display account statistics and overview
 */
import { computed } from 'vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    selectedCurrency: {
        type: String,
        default: 'USD'
    }
});

const { formatCurrency } = useCurrency();

// Calculate statistics
const statistics = computed(() => {
    const active = props.accounts.filter(a => a.status === 'active');
    // Balance is stored in cents, so we need to sum it properly
    const totalBalance = active.reduce((sum, a) => sum + (parseInt(a.balance) || 0), 0);

    return {
        totalBalance,
        activeAccounts: active.length,
        totalAccounts: props.accounts.length,
        frozenAccounts: props.accounts.filter(a => a.status === 'frozen').length
    };
});

const statCards = computed(() => [
    {
        title: 'Total Balance',
        value: formatCurrency(statistics.value.totalBalance, props.selectedCurrency),
        icon: 'pi-wallet',
        color: 'indigo',
        trend: null
    },
    {
        title: 'Active Accounts',
        value: statistics.value.activeAccounts,
        icon: 'pi-check-circle',
        color: 'green',
        subtitle: `of ${statistics.value.totalAccounts} total`
    },
    {
        title: 'Frozen Accounts',
        value: statistics.value.frozenAccounts,
        icon: 'pi-lock',
        color: 'blue',
        subtitle: statistics.value.frozenAccounts > 0 ? 'Requires attention' : 'All clear'
    }
]);
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div
            v-for="(stat, index) in statCards"
            :key="index"
            class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 shadow-sm border-l-4"
            :class="{
                'border-l-indigo-500': stat.color === 'indigo',
                'border-l-green-500': stat.color === 'green',
                'border-l-blue-500': stat.color === 'blue'
            }"
        >
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ stat.title }}
                    </p>
                    <p class="mb-1 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ stat.value }}
                    </p>
                    <p v-if="stat.subtitle" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ stat.subtitle }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center w-12 h-12 rounded-full"
                    :class="{
                        'bg-indigo-100 dark:bg-indigo-900/30': stat.color === 'indigo',
                        'bg-green-100 dark:bg-green-900/30': stat.color === 'green',
                        'bg-blue-100 dark:bg-blue-900/30': stat.color === 'blue'
                    }"
                >
                    <i
                        class="pi text-xl"
                        :class="[
                            stat.icon,
                            {
                                'text-indigo-600 dark:text-indigo-400': stat.color === 'indigo',
                                'text-green-600 dark:text-green-400': stat.color === 'green',
                                'text-blue-600 dark:text-blue-400': stat.color === 'blue'
                            }
                        ]"
                    ></i>
                </div>
            </div>
        </div>
    </div>
</template>
