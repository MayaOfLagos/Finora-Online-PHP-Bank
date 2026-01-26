<script setup>
/**
 * Dashboard Page
 * Main overview of user's banking information
 */
import { computed, ref, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from 'primevue/button';
import Carousel from 'primevue/carousel';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Toast from 'primevue/toast';

// Components
import StatCard from '@/Components/Cards/StatCard.vue';
import AccountCard from '@/Components/Cards/AccountCard.vue';
import TransactionCard from '@/Components/Cards/TransactionCard.vue';
import DigitalCard from '@/Components/Cards/DigitalCard.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import IncomeExpenseChart from '@/Components/Charts/IncomeExpenseChart.vue';
import PendingItemsWidget from '@/Components/Widgets/PendingItemsWidget.vue';

const page = usePage();
const toast = useToast();
const user = computed(() => page.props.auth?.user);
const userCurrency = computed(() => page.props.auth?.currency || page.props.userCurrency || 'USD');

// Show welcome toast on first dashboard load after login
onMounted(() => {
    // Check if this is a fresh login (within last 10 seconds)
    const lastLoginAt = user.value?.last_login_at;
    if (lastLoginAt) {
        const lastLogin = new Date(lastLoginAt);
        const now = new Date();
        const secondsSinceLogin = (now - lastLogin) / 1000;
        
        // If logged in within last 10 seconds, show welcome message
        if (secondsSinceLogin < 10) {
            toast.add({
                severity: 'success',
                summary: 'Welcome Back!',
                detail: `Good to see you, ${user.value?.first_name || user.value?.name?.split(' ')[0] || 'User'}! 👋`,
                life: 4000,
            });
        }
    }
});

// Get data from props
const accounts = computed(() => page.props.accounts || []);
const cards = computed(() => page.props.cards || []);
const recentTransactions = computed(() => page.props.recentTransactions || []);
const stats = computed(() => page.props.stats || {
    totalBalance: 0,
    monthlyIncome: 0,
    monthlyExpenses: 0,
    pendingTransfers: 0,
    incomeTrend: 0,
    expensesTrend: 0,
});
const chartData = computed(() => page.props.chartData || {
    labels: [],
    income: [],
    expenses: [],
});
const pendingItems = computed(() => page.props.pendingItems || {
    deposits: 0,
    loans: 0,
    tickets: 0,
    cardRequests: 0,
});
const cardStats = computed(() => page.props.cardStats || {
    total: 0,
    active: 0,
    virtual: 0,
    physical: 0,
});

// Active cards for carousel
const activeCards = computed(() =>
    cards.value.filter(card => card.status === 'active')
);

// Chart type toggle
const chartType = ref('line');

// Carousel responsive options
const carouselResponsiveOptions = [
    {
        breakpoint: '1024px',
        numVisible: 1,
        numScroll: 1,
    },
];

// Quick action items
const quickActions = [
    {
        name: 'Wire Transfer',
        href: '/transfers/wire',
        icon: 'pi pi-globe',
        color: 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400',
        description: 'International',
    },
    {
        name: 'Internal Transfer',
        href: '/transfers/internal',
        icon: 'pi pi-arrow-right-arrow-left',
        color: 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400',
        description: 'To Finora users',
    },
    {
        name: 'Deposit',
        href: '/deposits',
        icon: 'pi pi-download',
        color: 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400',
        description: 'Add funds',
    },
    {
        name: 'Cards',
        href: '/cards',
        icon: 'pi pi-credit-card',
        color: 'bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400',
        description: 'Manage cards',
    },
    {
        name: 'Loans',
        href: '/loans',
        icon: 'pi pi-building',
        color: 'bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400',
        description: 'Apply now',
    },
    {
        name: 'Support',
        href: '/support',
        icon: 'pi pi-headphones',
        color: 'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400',
        description: '24/7 help',
    },
];

const handleFreeze = (card) => {
    router.post(route('cards.freeze', card.uuid), {}, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['cards', 'cardStats', 'pendingItems'] }),
    });
};

const viewCardDetails = (card) => {
    router.visit(`/cards/${card.uuid}`);
};
</script>

<template>
    <DashboardLayout title="Dashboard">
        <Toast position="top-right" :pt="{ root: { style: 'z-index: 9999' } }" />
        
        <!-- Welcome Message -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Welcome back, {{ user?.first_name || user?.name?.split(' ')[0] || 'User' }}! 👋
            </h2>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Here's what's happening with your accounts today.
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-3 mb-6 lg:grid-cols-4 sm:gap-4">
            <StatCard
                title="Total Balance"
                :value="stats.totalBalance"
                icon="pi-wallet"
                :is-currency="true"
                :currency="userCurrency"
                trend-label="across all accounts"
            />
            <StatCard
                title="Monthly Income"
                :value="stats.monthlyIncome"
                icon="pi-arrow-down-left"
                icon-color="text-green-500"
                :is-currency="true"
                :currency="userCurrency"
                :trend="stats.incomeTrend"
                trend-label="vs last month"
            />
            <StatCard
                title="Monthly Expenses"
                :value="stats.monthlyExpenses"
                icon="pi-arrow-up-right"
                icon-color="text-red-500"
                :is-currency="true"
                :currency="userCurrency"
                :trend="stats.expensesTrend"
                trend-label="vs last month"
            />
            <StatCard
                title="Pending"
                :value="stats.pendingTransfers"
                icon="pi-clock"
                icon-color="text-orange-500"
                trend-label="transfers awaiting"
            />
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-3">
            <!-- Left Column: Chart + Transactions -->
            <div class="space-y-6 xl:col-span-2">
                <!-- Income vs Expenses Chart -->
                <div class="p-6 bg-white border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Income vs Expenses</h3>
                        <div class="flex items-center gap-2">
                            <Button
                                :icon="chartType === 'line' ? 'pi pi-chart-line' : 'pi pi-chart-bar'"
                                severity="secondary"
                                text
                                size="small"
                                @click="chartType = chartType === 'line' ? 'bar' : 'line'"
                                v-tooltip.top="'Toggle chart type'"
                            />
                        </div>
                    </div>
                    <IncomeExpenseChart
                        :chart-data="chartData"
                        :chart-type="chartType"
                        height="280px"
                    />
                </div>
                <!-- Quick Actions -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-6 sm:gap-4">
                        <Link
                            v-for="action in quickActions"
                            :key="action.name"
                            :href="action.href"
                            class="flex flex-col items-center p-4 transition-all bg-white border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 sm:p-5 rounded-2xl hover:border-indigo-200 dark:hover:border-indigo-600 hover:shadow-md group"
                        >
                            <div
                                :class="[
                                    'w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-2 sm:mb-3 transition-transform group-hover:scale-110',
                                    action.color
                                ]"
                            >
                                <i :class="[action.icon, 'text-lg sm:text-xl']"></i>
                            </div>
                            <span class="text-xs font-medium text-center text-gray-900 dark:text-white sm:text-sm">
                                {{ action.name }}
                            </span>
                            <span class="hidden sm:block text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ action.description }}
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="overflow-hidden bg-white border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 rounded-2xl">
                    <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
                        <Link
                            href="/transactions"
                            class="flex items-center gap-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
                        >
                            View All
                            <i class="text-xs pi pi-arrow-right"></i>
                        </Link>
                    </div>

                    <div v-if="recentTransactions.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[400px] overflow-y-auto">
                        <TransactionCard
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            :transaction="transaction"
                        />
                    </div>
                    <EmptyState
                        v-else
                        icon="pi pi-inbox"
                        title="No transactions yet"
                        description="Your recent transactions will appear here"
                        class="py-12"
                    />
                </div>
            </div>

            <!-- Right Column: Accounts + Pending -->
            <div class="space-y-6">
                <!-- Your Accounts -->
                <div class="p-5 bg-white border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Your Accounts</h3>
                        <Link
                            href="/accounts"
                            class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
                        >
                            Manage
                        </Link>
                    </div>

                    <div v-if="accounts.length > 0" class="space-y-3">
                        <AccountCard
                            v-for="account in accounts"
                            :key="account.id"
                            :account="account"
                            class="!shadow-none !border dark:!border-gray-600"
                        />
                    </div>
                    <EmptyState
                        v-else
                        icon="pi pi-wallet"
                        title="No accounts"
                        description="Open an account to get started"
                        action-label="Open Account"
                        action-href="/accounts/open"
                        class="py-8"
                    />
                </div>

                <!-- Pending Items Widget -->
                <div class="p-5 bg-white border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 rounded-2xl">
                    <PendingItemsWidget :items="pendingItems" />
                </div>
            </div>
        </div>

        <!-- Digital Cards Section -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Your Cards</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ cardStats.active }} active · {{ cardStats.virtual }} virtual · {{ cardStats.physical }} physical
                    </p>
                </div>
                <Link href="/cards">
                    <Button
                        label="Manage Cards"
                        icon="pi pi-cog"
                        severity="secondary"
                        size="small"
                        outlined
                    />
                </Link>
            </div>

            <div v-if="cards.length > 0">
                <!-- Desktop: Grid -->
                <div class="hidden gap-6 md:grid md:grid-cols-2 lg:grid-cols-3">
                    <DigitalCard
                        v-for="card in cards.slice(0, 3)"
                        :key="card.id"
                        :card="card"
                        @freeze="handleFreeze"
                        @view-details="viewCardDetails"
                    />

                    <!-- Add Card CTA if less than 3 cards -->
                    <Link
                        v-if="cards.length < 3"
                        href="/cards"
                        class="aspect-[1.586/1] rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20 transition-all flex flex-col items-center justify-center gap-3 group"
                    >
                        <div class="flex items-center justify-center transition-colors bg-gray-100 rounded-full dark:bg-gray-700 w-14 h-14 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50">
                            <i class="text-xl text-gray-400 dark:text-gray-500 pi pi-plus group-hover:text-indigo-600 dark:group-hover:text-indigo-400"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Request New Card</span>
                    </Link>
                </div>

                <!-- Mobile: Carousel -->
                <div class="md:hidden">
                    <Carousel
                        :value="cards"
                        :numVisible="1"
                        :numScroll="1"
                        :showIndicators="cards.length > 1"
                        :showNavigators="false"
                        :pt="{
                            indicatorList: { class: 'gap-2 mt-4' },
                            indicator: { class: 'w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600' },
                        }"
                    >
                        <template #item="{ data }">
                            <div class="px-1">
                                <DigitalCard
                                    :card="data"
                                    @freeze="handleFreeze"
                                    @view-details="viewCardDetails"
                                />
                            </div>
                        </template>
                    </Carousel>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="p-8 bg-white border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 rounded-2xl">
                <EmptyState
                    icon="pi pi-credit-card"
                    title="No cards yet"
                    description="Request a virtual or physical card to make payments"
                    action-label="Request Card"
                    action-href="/cards"
                />
            </div>
        </div>

    </DashboardLayout>
</template>
