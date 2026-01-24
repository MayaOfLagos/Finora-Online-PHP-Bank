<script setup>
/**
 * IncomeExpenseChart Component
 * Line/Bar chart showing income vs expenses over time
 */
import { computed, ref, watch } from 'vue';
import Chart from 'primevue/chart';
import { useDarkMode } from '@/Composables/useDarkMode';

const props = defineProps({
    chartData: {
        type: Object,
        default: () => ({
            labels: [],
            income: [],
            expenses: [],
        }),
    },
    chartType: {
        type: String,
        default: 'line', // 'line' or 'bar'
    },
    height: {
        type: String,
        default: '300px',
    },
});

const { isDark } = useDarkMode();

// Chart data configuration
const data = computed(() => ({
    labels: props.chartData.labels,
    datasets: [
        {
            label: 'Income',
            data: props.chartData.income,
            fill: props.chartType === 'line',
            borderColor: '#22c55e',
            backgroundColor: props.chartType === 'line' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(34, 197, 94, 0.8)',
            tension: 0.4,
            borderWidth: 2,
            pointBackgroundColor: '#22c55e',
            pointBorderColor: isDark.value ? '#1f2937' : '#fff',
            pointHoverBackgroundColor: isDark.value ? '#1f2937' : '#fff',
            pointHoverBorderColor: '#22c55e',
            pointRadius: 4,
            pointHoverRadius: 6,
        },
        {
            label: 'Expenses',
            data: props.chartData.expenses,
            fill: props.chartType === 'line',
            borderColor: '#ef4444',
            backgroundColor: props.chartType === 'line' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(239, 68, 68, 0.8)',
            tension: 0.4,
            borderWidth: 2,
            pointBackgroundColor: '#ef4444',
            pointBorderColor: isDark.value ? '#1f2937' : '#fff',
            pointHoverBackgroundColor: isDark.value ? '#1f2937' : '#fff',
            pointHoverBorderColor: '#ef4444',
            pointRadius: 4,
            pointHoverRadius: 6,
        },
    ],
}));

// Chart options - reactive to dark mode
const options = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            align: 'end',
            labels: {
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 20,
                color: isDark.value ? '#d1d5db' : '#374151',
                font: {
                    size: 12,
                    family: "'Inter', sans-serif",
                },
            },
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: isDark.value ? 'rgba(31, 41, 55, 0.95)' : 'rgba(17, 24, 39, 0.9)',
            titleColor: '#fff',
            bodyColor: '#d1d5db',
            titleFont: {
                size: 13,
                family: "'Inter', sans-serif",
            },
            bodyFont: {
                size: 12,
                family: "'Inter', sans-serif",
            },
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: (context) => {
                    const value = context.parsed.y;
                    return `${context.dataset.label}: $${value.toLocaleString()}`;
                },
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: {
                    size: 11,
                    family: "'Inter', sans-serif",
                },
                color: isDark.value ? '#9ca3af' : '#6b7280',
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: isDark.value ? 'rgba(75, 85, 99, 0.3)' : 'rgba(156, 163, 175, 0.1)',
            },
            ticks: {
                font: {
                    size: 11,
                    family: "'Inter', sans-serif",
                },
                color: isDark.value ? '#9ca3af' : '#6b7280',
                callback: (value) => `$${value.toLocaleString()}`,
            },
        },
    },
    interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false,
    },
}));
</script>

<template>
    <div class="income-expense-chart" :style="{ height }">
        <Chart :type="chartType" :data="data" :options="options" class="h-full" />
    </div>
</template>
