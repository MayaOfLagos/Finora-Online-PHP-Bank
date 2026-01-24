<script setup>
/**
 * StatCard Component
 * Displays a statistic with optional trend indicator
 */
import { computed } from 'vue';
import CurrencyDisplay from '@/Components/Common/CurrencyDisplay.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    isCurrency: {
        type: Boolean,
        default: false,
    },
    currency: {
        type: String,
        default: 'USD',
    },
    icon: {
        type: String,
        default: '',
    },
    trend: {
        type: Number,
        default: null, // Percentage change
    },
    trendLabel: {
        type: String,
        default: 'vs last month',
    },
    color: {
        type: String,
        default: 'indigo', // indigo, emerald, amber, rose
    },
});

const colorClasses = {
    indigo: {
        icon: 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400',
        trend: 'text-indigo-600 dark:text-indigo-400',
    },
    emerald: {
        icon: 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400',
        trend: 'text-emerald-600 dark:text-emerald-400',
    },
    amber: {
        icon: 'bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400',
        trend: 'text-amber-600 dark:text-amber-400',
    },
    rose: {
        icon: 'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400',
        trend: 'text-rose-600 dark:text-rose-400',
    },
};

const trendColor = computed(() => {
    if (props.trend === null) return '';
    return props.trend >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
});

const trendIcon = computed(() => {
    if (props.trend === null) return '';
    return props.trend >= 0 ? 'pi pi-arrow-up' : 'pi pi-arrow-down';
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-start justify-between">
            <!-- Content -->
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ title }}</p>

                <div class="mt-2">
                    <CurrencyDisplay
                        v-if="isCurrency"
                        :amount="value"
                        :currency="currency"
                        size="xl"
                        class="text-gray-900 dark:text-white"
                    />
                    <p v-else class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ value }}
                    </p>
                </div>

                <!-- Trend -->
                <div v-if="trend !== null" class="mt-2 flex items-center gap-1">
                    <i :class="[trendIcon, trendColor, 'text-xs']"></i>
                    <span :class="[trendColor, 'text-sm font-medium']">
                        {{ Math.abs(trend) }}%
                    </span>
                    <span class="text-gray-400 dark:text-gray-500 text-sm">{{ trendLabel }}</span>
                </div>
            </div>

            <!-- Icon -->
            <div
                v-if="icon"
                :class="[colorClasses[color].icon, 'w-12 h-12 rounded-xl flex items-center justify-center']"
            >
                <i :class="['pi', icon, 'text-xl']"></i>
            </div>
        </div>
    </div>
</template>
