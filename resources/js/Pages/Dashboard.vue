<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Mock data for demonstration
const stats = [
    { name: 'Total Balance', value: '$24,567.89', change: '+2.5%', changeType: 'positive' },
    { name: 'Monthly Income', value: '$8,420.00', change: '+12.3%', changeType: 'positive' },
    { name: 'Monthly Expenses', value: '$3,842.50', change: '-4.1%', changeType: 'negative' },
    { name: 'Pending Transfers', value: '3', change: '', changeType: 'neutral' },
];

const recentTransactions = [
    { id: 1, type: 'credit', description: 'Salary Deposit', amount: '+$5,000.00', date: 'Today', status: 'completed' },
    { id: 2, type: 'debit', description: 'Wire Transfer to John', amount: '-$1,200.00', date: 'Yesterday', status: 'completed' },
    { id: 3, type: 'debit', description: 'Netflix Subscription', amount: '-$15.99', date: 'Jan 18', status: 'completed' },
    { id: 4, type: 'credit', description: 'Refund - Amazon', amount: '+$49.99', date: 'Jan 17', status: 'pending' },
    { id: 5, type: 'debit', description: 'Grocery Store', amount: '-$87.32', date: 'Jan 16', status: 'completed' },
];

const accounts = [
    { id: 1, name: 'Checking Account', number: '****4521', balance: '$18,432.50', type: 'checking' },
    { id: 2, name: 'Savings Account', number: '****7834', balance: '$6,135.39', type: 'savings' },
];
</script>

<template>
    <DashboardLayout title="Dashboard">
        <!-- Welcome message -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">
                Welcome back, {{ user?.name?.split(' ')[0] || 'User' }}! 👋
            </h2>
            <p class="text-gray-600 mt-1">Here's what's happening with your accounts today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div
                v-for="stat in stats"
                :key="stat.name"
                class="bg-white rounded-xl p-6 shadow-sm border border-gray-100"
            >
                <p class="text-sm font-medium text-gray-500">{{ stat.name }}</p>
                <div class="mt-2 flex items-baseline justify-between">
                    <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
                    <span
                        v-if="stat.change"
                        :class="[
                            'text-sm font-medium',
                            stat.changeType === 'positive' ? 'text-green-600' : 'text-red-600'
                        ]"
                    >
                        {{ stat.change }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Transactions -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="transaction in recentTransactions"
                        :key="transaction.id"
                        class="p-4 flex items-center justify-between hover:bg-gray-50"
                    >
                        <div class="flex items-center space-x-4">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center',
                                    transaction.type === 'credit' ? 'bg-green-100' : 'bg-red-100'
                                ]"
                            >
                                <svg
                                    class="w-5 h-5"
                                    :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        :d="transaction.type === 'credit' ? 'M12 4v16m8-8H4' : 'M20 12H4'"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ transaction.description }}</p>
                                <p class="text-sm text-gray-500">{{ transaction.date }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p
                                :class="[
                                    'font-semibold',
                                    transaction.type === 'credit' ? 'text-green-600' : 'text-gray-900'
                                ]"
                            >
                                {{ transaction.amount }}
                            </p>
                            <span
                                :class="[
                                    'text-xs px-2 py-1 rounded-full',
                                    transaction.status === 'completed'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700'
                                ]"
                            >
                                {{ transaction.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100">
                    <a href="/transactions" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        View all transactions →
                    </a>
                </div>
            </div>

            <!-- Accounts -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Your Accounts</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div
                        v-for="account in accounts"
                        :key="account.id"
                        class="p-4 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl text-white"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-white/80 text-sm">{{ account.name }}</p>
                                <p class="text-white/60 text-xs">{{ account.number }}</p>
                            </div>
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-xs">F</span>
                            </div>
                        </div>
                        <p class="text-2xl font-bold">{{ account.balance }}</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100">
                    <a href="/accounts" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Manage accounts →
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a
                    href="/transfers/wire"
                    class="flex flex-col items-center p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all"
                >
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Wire Transfer</span>
                </a>

                <a
                    href="/deposits"
                    class="flex flex-col items-center p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all"
                >
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Deposit</span>
                </a>

                <a
                    href="/cards"
                    class="flex flex-col items-center p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all"
                >
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Cards</span>
                </a>

                <a
                    href="/support"
                    class="flex flex-col items-center p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all"
                >
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Support</span>
                </a>
            </div>
        </div>
    </DashboardLayout>
</template>
