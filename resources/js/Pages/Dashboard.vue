<script setup>
/**
 * Dashboard Page
 * Main overview of user's banking information
 */
import { computed, ref, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from 'primevue/button';
import Carousel from 'primevue/carousel';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';

// Components
import StatCard from '@/Components/Cards/StatCard.vue';
import AccountCard from '@/Components/Cards/AccountCard.vue';
import TransactionCard from '@/Components/Cards/TransactionCard.vue';
import DigitalCard from '@/Components/Cards/DigitalCard.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import IncomeExpenseChart from '@/Components/Charts/IncomeExpenseChart.vue';
import PendingItemsWidget from '@/Components/Widgets/PendingItemsWidget.vue';
import { useCurrency } from '@/Composables/useCurrency';

const page = usePage();
const toast = useToast();
const { formatCurrency } = useCurrency();
const user = computed(() => page.props.auth?.user);
const userCurrency = computed(() => page.props.auth?.currency || page.props.userCurrency || 'USD');

// Mobile wallet slider state
const currentCard = ref(0);
const balanceVisible = ref(true);
const isDragging = ref(false);
const startX = ref(0);
const currentX = ref(0);

// Quick Actions Modal state
const showQuickActionsModal = ref(false);

// Menu items for quick actions modal
const menuItems = {
    main: [
        { name: 'Dashboard', href: '/dashboard', icon: 'pi pi-home', color: 'bg-indigo-100 dark:bg-indigo-600 text-indigo-600 dark:text-white', description: 'Overview & balance', active: true },
        { name: 'Transactions', href: '/transactions', icon: 'pi pi-chart-line', color: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400', description: 'View transaction history' },
        { name: 'Cards', href: '/cards', icon: 'pi pi-credit-card', color: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400', description: 'Manage your cards' },
    ],
    transfers: [
        { name: 'Local Transfer', href: '/transfers/domestic', icon: 'pi pi-send', color: 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400', description: 'Send to local accounts' },
        { name: 'International', href: '/transfers/wire', icon: 'pi pi-globe', color: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400', description: 'Global transfers' },
        { name: 'Internal', href: '/transfers/internal', icon: 'pi pi-arrow-right-arrow-left', color: 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400', description: 'To Finora users' },
        { name: 'Deposit', href: '/deposits', icon: 'pi pi-plus', color: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400', description: 'Add funds to account' },
    ],
    services: [
        { name: 'Loans', href: '/loans', icon: 'pi pi-building', color: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400', description: 'Apply for loans' },
        { name: 'Grants', href: '/grants', icon: 'pi pi-gift', color: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400', description: 'Apply for grants' },
    ],
    account: [
        { name: 'Accounts', href: '/accounts', icon: 'pi pi-wallet', color: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400', description: 'View your accounts' },
        { name: 'Settings', href: '/settings/profile', icon: 'pi pi-cog', color: 'bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400', description: 'Manage your account' },
        { name: 'Support', href: '/support', icon: 'pi pi-headphones', color: 'bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400', description: 'Get assistance' },
    ],
};

// Greeting based on time of day
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
});

// Primary account for mobile display
const primaryAccount = computed(() => {
    const accs = page.props.accounts || [];
    return accs.find(a => a.is_primary) || accs[0] || null;
});

// Format date for mobile card
const lastUpdated = computed(() => {
    const now = new Date();
    return now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' + 
           now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
});

// Toggle balance visibility
const toggleBalance = () => {
    balanceVisible.value = !balanceVisible.value;
};

// Swipe handlers for mobile wallet cards
const handleTouchStart = (e) => {
    startX.value = e.touches ? e.touches[0].clientX : e.clientX;
    isDragging.value = true;
};

const handleTouchMove = (e) => {
    if (!isDragging.value) return;
    currentX.value = e.touches ? e.touches[0].clientX : e.clientX;
};

const handleTouchEnd = () => {
    if (!isDragging.value) return;
    const diffX = startX.value - currentX.value;
    const threshold = 50;
    const totalCards = 2;
    
    if (Math.abs(diffX) > threshold) {
        if (diffX > 0 && currentCard.value < totalCards - 1) {
            currentCard.value++;
        } else if (diffX < 0 && currentCard.value > 0) {
            currentCard.value--;
        }
    }
    
    isDragging.value = false;
    startX.value = 0;
    currentX.value = 0;
};

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
            toast.success(`Good to see you, ${user.value?.first_name || user.value?.name?.split(' ')[0] || 'User'}! 👋`, 'Welcome Back!');
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
    grants: 0,
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
        name: 'Wire',
        href: '/transfers/wire',
        icon: 'pi pi-globe',
        color: 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400',
        description: 'International',
    },
    {
        name: 'Internal',
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
        
        <!-- Mobile Header with Extended Gradient Background -->
        <div class="lg:hidden -mx-4 -mt-4 sm:-mx-6 sm:-mt-6 mb-6">
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 text-white px-4 pt-6 pb-8 rounded-b-3xl">
                <!-- Header Section -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 rounded-full border-2 border-white/20 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ user?.first_name?.charAt(0) || 'U' }}
                        </div>
                        <div>
                            <p class="text-white/80 text-xs">{{ greeting }} 👋</p>
                            <p class="text-white font-semibold text-base">
                                {{ user?.first_name }} {{ user?.last_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Swipeable Mobile Wallet Cards -->
                <div class="mb-6">
                    <!-- Card Container -->
                    <div class="relative overflow-hidden">
                        <div 
                            class="flex transition-transform duration-300 ease-in-out"
                            :style="`transform: translateX(-${currentCard * 100}%)`"
                            @touchstart="handleTouchStart"
                            @touchmove="handleTouchMove"
                            @touchend="handleTouchEnd"
                            @mousedown="handleTouchStart"
                            @mousemove="isDragging && handleTouchMove($event)"
                            @mouseup="handleTouchEnd"
                            @mouseleave="handleTouchEnd"
                        >
                            <!-- Fiat Balance Card -->
                            <div class="w-full flex-shrink-0">
                                <div class="bg-gradient-to-br from-indigo-600 via-indigo-500 to-indigo-700 dark:from-indigo-700 dark:via-indigo-600 dark:to-indigo-800 rounded-2xl p-4 text-white shadow-xl relative overflow-hidden">
                                    <!-- Background Pattern -->
                                    <div class="absolute inset-0 opacity-10">
                                        <div class="absolute top-3 right-3 w-24 h-24 bg-white rounded-full -translate-y-6 translate-x-6"></div>
                                        <div class="absolute bottom-3 left-3 w-20 h-20 bg-white rounded-full translate-y-4 -translate-x-4"></div>
                                    </div>

                                    <div class="relative z-10">
                                        <!-- Account Info -->
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <p class="text-white/60 text-xs uppercase tracking-wide">Finora Bank</p>
                                                <p class="text-white/80 text-xs">{{ user?.first_name }} {{ user?.last_name }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-white/60 text-xs">Fiat Account</p>
                                                <p class="text-white/80 text-xs font-mono" v-if="primaryAccount">
                                                    •••• {{ primaryAccount.account_number?.slice(-4) }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Balance Section -->
                                        <div class="text-center mb-3">
                                            <p class="text-white/80 text-xs mb-1">Available Balance</p>
                                            <div class="flex items-center justify-center space-x-2">
                                                <p v-if="balanceVisible" class="text-2xl sm:text-3xl font-bold">
                                                    {{ formatCurrency(stats.totalBalance, userCurrency) }}
                                                </p>
                                                <p v-else class="text-2xl sm:text-3xl font-bold">
                                                    {{ userCurrency === 'USD' ? '$' : userCurrency === 'EUR' ? '€' : userCurrency === 'GBP' ? '£' : '$' }} ******
                                                </p>
                                                <button 
                                                    @click.stop="toggleBalance" 
                                                    @touchstart.stop
                                                    @mousedown.stop
                                                    class="text-white/60 hover:text-white p-1"
                                                >
                                                    <i v-if="balanceVisible" class="pi pi-eye-slash text-base"></i>
                                                    <i v-else class="pi pi-eye text-base"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Account Status -->
                                        <div class="flex justify-between items-center text-xs">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-1.5 h-1.5 bg-green-400 rounded-full"></div>
                                                <span class="text-white/70">Active</span>
                                            </div>
                                            <div class="text-white/70">
                                                Last updated: {{ lastUpdated }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Summary Card -->
                            <div class="w-full flex-shrink-0">
                                <div class="bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-600 dark:from-emerald-700 dark:via-emerald-600 dark:to-teal-700 rounded-2xl p-4 text-white shadow-xl relative overflow-hidden">
                                    <!-- Background Pattern -->
                                    <div class="absolute inset-0 opacity-10">
                                        <div class="absolute top-3 right-3 w-24 h-24 bg-white rounded-full -translate-y-6 translate-x-6"></div>
                                        <div class="absolute bottom-3 left-3 w-20 h-20 bg-white rounded-full translate-y-4 -translate-x-4"></div>
                                    </div>

                                    <div class="relative z-10">
                                        <!-- Header -->
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <p class="text-white/60 text-xs uppercase tracking-wide">Monthly Overview</p>
                                                <p class="text-white/80 text-xs">{{ new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-white/60 text-xs">Statistics</p>
                                                <i class="pi pi-chart-line text-white/80"></i>
                                            </div>
                                        </div>

                                        <!-- Stats Section -->
                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <div class="text-center">
                                                <p class="text-white/60 text-xs mb-1">Income</p>
                                                <p v-if="balanceVisible" class="text-lg font-bold text-green-200">
                                                    {{ formatCurrency(stats.monthlyIncome, userCurrency) }}
                                                </p>
                                                <p v-else class="text-lg font-bold text-green-200">******</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-white/60 text-xs mb-1">Expenses</p>
                                                <p v-if="balanceVisible" class="text-lg font-bold text-red-200">
                                                    {{ formatCurrency(stats.monthlyExpenses, userCurrency) }}
                                                </p>
                                                <p v-else class="text-lg font-bold text-red-200">******</p>
                                            </div>
                                        </div>

                                        <!-- Pending Info -->
                                        <div class="flex justify-between items-center text-xs">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full"></div>
                                                <span class="text-white/70">{{ stats.pendingTransfers }} pending transfers</span>
                                            </div>
                                            <div class="text-white/70">
                                                <i class="pi pi-refresh mr-1"></i> Live
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Indicators -->
                    <div class="flex justify-center space-x-2 mt-3">
                        <button 
                            @click="currentCard = 0"
                            :class="currentCard === 0 ? 'bg-white' : 'bg-white/30'"
                            class="w-2 h-2 rounded-full transition-colors duration-200"
                        ></button>
                        <button 
                            @click="currentCard = 1"
                            :class="currentCard === 1 ? 'bg-white' : 'bg-white/30'"
                            class="w-2 h-2 rounded-full transition-colors duration-200"
                        ></button>
                    </div>

                    <!-- Swipe Instructions -->
                    <div class="text-center mt-2">
                        <p class="text-white/50 text-xs">
                            <i class="pi pi-arrows-h mr-1"></i> Swipe to switch views
                        </p>
                    </div>
                </div>

                <!-- Mobile Quick Actions - Circular Design -->
                <div class="grid grid-cols-4 gap-3">
                    <!-- Top Up -->
                    <Link href="/deposits" class="flex flex-col items-center justify-center">
                        <div class="w-14 h-14 bg-yellow-400 dark:bg-yellow-500 rounded-full flex items-center justify-center shadow-lg mb-1">
                            <i class="pi pi-plus text-slate-900 text-lg"></i>
                        </div>
                        <span class="text-white text-xs font-medium">Top Up</span>
                    </Link>

                    <!-- Send -->
                    <Link href="/transfers" class="flex flex-col items-center justify-center">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg mb-1 border border-white/10">
                            <i class="pi pi-send text-white text-lg"></i>
                        </div>
                        <span class="text-white text-xs font-medium">Send</span>
                    </Link>

                    <!-- Cards -->
                    <Link href="/cards" class="flex flex-col items-center justify-center">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg mb-1 border border-white/10">
                            <i class="pi pi-credit-card text-white text-lg"></i>
                        </div>
                        <span class="text-white text-xs font-medium">Cards</span>
                    </Link>

                    <!-- More -->
                    <button @click="showQuickActionsModal = true" class="flex flex-col items-center justify-center">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg mb-1 border border-white/10">
                            <i class="pi pi-th-large text-white text-lg"></i>
                        </div>
                        <span class="text-white text-xs font-medium">More</span>
                    </button>
                </div>
            </div>

            <!-- Mobile Cards Section -->
            <div class="px-4 mb-4" v-if="cards.length > 0">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">My Cards</h3>
                    <Link href="/cards" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium flex items-center hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                        Manage <i class="pi pi-chevron-right text-xs ml-1"></i>
                    </Link>
                </div>
                
                <div class="space-y-3">
                    <div v-for="card in cards.slice(0, 2)" :key="card.id" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 overflow-hidden">
                        <div class="p-4">
                            <!-- Card Preview -->
                            <div class="relative h-32 rounded-xl overflow-hidden mb-3" :class="card.card_type === 'virtual' ? 'bg-gradient-to-br from-indigo-500 to-purple-600' : 'bg-gradient-to-br from-slate-700 to-slate-900'">
                                <div class="absolute inset-0 opacity-20">
                                    <div class="absolute top-2 right-2 w-16 h-16 bg-white rounded-full -translate-y-4 translate-x-4"></div>
                                </div>
                                <div class="relative z-10 p-4 text-white">
                                    <div class="flex justify-between items-start mb-6">
                                        <p class="text-xs text-white/70 uppercase">{{ card.card_type }} Card</p>
                                        <i class="pi pi-wifi text-white/70"></i>
                                    </div>
                                    <p class="font-mono text-sm tracking-wider mb-2">•••• •••• •••• {{ card.card_number?.slice(-4) || '****' }}</p>
                                    <p class="text-xs text-white/70">{{ user?.first_name }} {{ user?.last_name }}</p>
                                </div>
                            </div>
                            
                            <!-- Card Info -->
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ card.card_type === 'virtual' ? 'Virtual Card' : 'Physical Card' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Expires {{ card.expiry_date || 'N/A' }}</p>
                                </div>
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    card.status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                                ]">
                                    {{ card.status === 'active' ? 'Active' : card.status }}
                                </span>
                            </div>
                            
                            <Link :href="`/cards/${card.uuid}`" class="block w-full text-center px-3 py-2 bg-indigo-100 dark:bg-indigo-900/50 hover:bg-indigo-200 dark:hover:bg-indigo-800/50 rounded-xl text-xs font-medium text-indigo-700 dark:text-indigo-300 transition-colors duration-200">
                                Manage Card
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Financial Services Overview -->
            <div class="px-4 mb-4 mt-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Financial Services</h3>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Loans -->
                    <Link href="/loans" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                                    <i class="pi pi-building text-amber-600 dark:text-amber-400"></i>
                                </div>
                                <span v-if="pendingItems.loans > 0" class="px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                    {{ pendingItems.loans }} Pending
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Loans</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Apply for loans</p>
                        </div>
                    </Link>

                    <!-- Grants -->
                    <Link href="/grants" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                                    <i class="pi pi-gift text-emerald-600 dark:text-emerald-400"></i>
                                </div>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Grants</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Available grants</p>
                        </div>
                    </Link>

                    <!-- Deposits -->
                    <Link href="/deposits" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                                    <i class="pi pi-download text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <span v-if="pendingItems.deposits > 0" class="px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                    {{ pendingItems.deposits }} Pending
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Deposits</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Add funds</p>
                        </div>
                    </Link>

                    <!-- Transfers -->
                    <Link href="/transfers" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                                    <i class="pi pi-arrow-right-arrow-left text-purple-600 dark:text-purple-400"></i>
                                </div>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Transfers</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Send money</p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Mobile Financial Insights -->
            <div class="px-4 mb-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Financial Insights</h3>
                </div>

                <div class="space-y-3">
                    <!-- Account Health Card -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                        <i class="pi pi-heart text-green-600 dark:text-green-400 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Account Health</span>
                                </div>
                                <span class="text-xs font-semibold text-green-600 dark:text-green-400">Good</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-green-400 to-emerald-500 h-2 rounded-full" style="width: 85%"></div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Your account is in good standing</p>
                        </div>
                    </div>

                    <!-- Monthly Summary -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 overflow-hidden">
                        <div class="p-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Monthly Summary</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Total Income</span>
                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                        {{ balanceVisible ? formatCurrency(stats.monthlyIncome, userCurrency) : '******' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Total Expenses</span>
                                    <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                        {{ balanceVisible ? formatCurrency(stats.monthlyExpenses, userCurrency) : '******' }}
                                    </span>
                                </div>
                                <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Net Balance</span>
                                        <span class="text-sm font-bold" :class="stats.monthlyIncome >= stats.monthlyExpenses ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            {{ balanceVisible ? formatCurrency(stats.monthlyIncome - stats.monthlyExpenses, userCurrency) : '******' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Tip -->
                    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-800 dark:to-gray-700 rounded-2xl border border-indigo-100 dark:border-gray-600 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="pi pi-lightbulb text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                </div>
                                <div>
                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 mb-1">Tip</span>
                                    <p class="text-xs text-gray-600 dark:text-gray-300">Set up automatic transfers to your savings account to build your emergency fund faster.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Activity Feed -->
            <div class="px-4 mb-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                    <Link href="/transactions" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium flex items-center hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                        View All <i class="pi pi-chevron-right ml-1 text-xs"></i>
                    </Link>
                </div>

                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 overflow-hidden">
                    <div v-if="recentTransactions.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="transaction in recentTransactions.slice(0, 5)" :key="transaction.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div :class="[
                                    'w-10 h-10 rounded-xl flex items-center justify-center',
                                    transaction.type === 'credit' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'
                                ]">
                                    <i :class="[
                                        'text-sm',
                                        transaction.type === 'credit' ? 'pi pi-arrow-down-left text-green-600 dark:text-green-400' : 'pi pi-arrow-up-right text-red-600 dark:text-red-400'
                                    ]"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ transaction.description || transaction.transaction_type }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ new Date(transaction.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p :class="[
                                        'text-sm font-semibold',
                                        transaction.type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                                    ]">
                                        {{ transaction.type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount, transaction.currency || userCurrency) }}
                                    </p>
                                    <span :class="[
                                        'text-xs px-1.5 py-0.5 rounded',
                                        transaction.status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' :
                                        transaction.status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' :
                                        'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                                    ]">
                                        {{ transaction.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-8 text-center">
                        <i class="pi pi-inbox text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
                    </div>
                </div>
            </div>

            <!-- Mobile Support Widget -->
            <div class="px-4 mb-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Need Help?</h3>
                    <Link href="/support" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium flex items-center hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                        Support Center <i class="pi pi-chevron-right ml-1 text-xs"></i>
                    </Link>
                </div>

                <!-- Support Options -->
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <Link href="/support" class="block">
                        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mx-auto mb-2">
                                <i class="pi pi-comments text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <p class="text-xs font-medium text-gray-900 dark:text-white">Live Chat</p>
                        </div>
                    </Link>
                    <a href="mailto:support@finorabank.com" class="block">
                        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg dark:shadow-gray-900/50 border border-white/20 dark:border-gray-700/50 p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mx-auto mb-2">
                                <i class="pi pi-envelope text-green-600 dark:text-green-400"></i>
                            </div>
                            <p class="text-xs font-medium text-gray-900 dark:text-white">Email Us</p>
                        </div>
                    </a>
                </div>

                <!-- Support Status -->
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-gray-800 dark:to-gray-700 rounded-2xl border border-emerald-100 dark:border-gray-600 p-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                            <i class="pi pi-headphones text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">24/7 Support Available</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">We're here to help anytime</p>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-green-600 dark:text-green-400 font-medium">Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ==================== DESKTOP VIEW ==================== -->
        <!-- Welcome Message (Desktop only) -->
        <div class="mb-6 hidden lg:block">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Welcome back, {{ user?.first_name || user?.name?.split(' ')[0] || 'User' }}! 👋
            </h2>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Here's what's happening with your accounts today.
            </p>
        </div>

        <!-- Stats Grid (Hidden on mobile - shown in mobile header) -->
        <div class="hidden sm:grid grid-cols-1 gap-3 mb-6 sm:grid-cols-2 lg:grid-cols-4 sm:gap-4">
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

        <!-- Main Content Grid (Desktop only) -->
        <div class="hidden lg:grid grid-cols-1 gap-6 mb-6 xl:grid-cols-3">
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
                        action-href="/accounts"
                        class="py-8"
                    />
                </div>

                <!-- Pending Items Widget -->
                <div class="p-5 bg-white border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700 rounded-2xl">
                    <PendingItemsWidget :items="pendingItems" />
                </div>
            </div>
        </div>

        <!-- Digital Cards Section (Desktop only) -->
        <div class="hidden lg:block mb-6">
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

        <!-- ==================== QUICK ACTIONS MODAL (Mobile Bottom Sheet) ==================== -->
        <Teleport to="body">
            <Transition name="modal-backdrop">
                <div v-if="showQuickActionsModal" class="fixed inset-0 z-50 lg:hidden" @click.self="showQuickActionsModal = false">
                    <!-- Backdrop with Blur -->
                    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showQuickActionsModal = false"></div>
                    
                    <!-- Bottom Sheet Container -->
                    <div class="fixed inset-x-0 bottom-0 z-50">
                        <Transition name="slide-up">
                            <div v-if="showQuickActionsModal" class="bg-white dark:bg-gray-900 rounded-t-3xl shadow-2xl dark:shadow-gray-900/50 max-h-[85vh] overflow-hidden">
                                
                                <!-- Handle Bar -->
                                <div class="flex justify-center pt-3 pb-2">
                                    <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                                </div>
                                
                                <!-- Header -->
                                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center overflow-hidden" :class="user?.avatar_url ? '' : 'bg-gradient-to-br from-indigo-500 to-indigo-700'">
                                                <img
                                                    v-if="user?.avatar_url"
                                                    :src="user.avatar_url"
                                                    :alt="user?.full_name || 'User'"
                                                    class="w-full h-full object-cover"
                                                />
                                                <i v-else class="pi pi-user text-white text-sm"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-900 dark:text-white">{{ user?.first_name }} {{ user?.last_name }}</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
                                            </div>
                                        </div>
                                        <button @click="showQuickActionsModal = false" class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                            <i class="pi pi-times text-gray-600 dark:text-gray-300 text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Navigation Content -->
                                <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                                    
                                    <!-- Main Section -->
                                    <div class="mb-6">
                                        <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Main</h4>
                                        <div class="space-y-1">
                                            <Link v-for="item in menuItems.main" :key="item.name" :href="item.href" @click="showQuickActionsModal = false"
                                                :class="[
                                                    'flex items-center space-x-4 p-3 rounded-2xl group transition-colors',
                                                    item.active ? 'bg-indigo-50 dark:bg-slate-700/80 border border-indigo-100 dark:border-slate-600' : 'hover:bg-gray-50 dark:hover:bg-gray-800'
                                                ]">
                                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform', item.color]">
                                                    <i :class="[item.icon]"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p :class="['font-semibold', item.active ? 'text-indigo-900 dark:text-white' : 'text-gray-900 dark:text-white']">{{ item.name }}</p>
                                                    <p :class="['text-xs', item.active ? 'text-indigo-600 dark:text-slate-300' : 'text-gray-500 dark:text-gray-400']">{{ item.description }}</p>
                                                </div>
                                                <i :class="['pi pi-chevron-right text-xs', item.active ? 'text-indigo-400 dark:text-slate-400' : 'text-gray-400 dark:text-gray-500']"></i>
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <!-- Transfers Section -->
                                    <div class="mb-6">
                                        <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Transfers</h4>
                                        <div class="space-y-1">
                                            <Link v-for="item in menuItems.transfers" :key="item.name" :href="item.href" @click="showQuickActionsModal = false"
                                                class="flex items-center space-x-4 p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800 group transition-colors">
                                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform', item.color]">
                                                    <i :class="[item.icon]"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white">{{ item.name }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.description }}</p>
                                                </div>
                                                <i class="pi pi-chevron-right text-gray-400 dark:text-gray-500 text-xs"></i>
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <!-- Services Section -->
                                    <div class="mb-6">
                                        <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Services</h4>
                                        <div class="space-y-1">
                                            <Link v-for="item in menuItems.services" :key="item.name" :href="item.href" @click="showQuickActionsModal = false"
                                                class="flex items-center space-x-4 p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800 group transition-colors">
                                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform', item.color]">
                                                    <i :class="[item.icon]"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white">{{ item.name }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.description }}</p>
                                                </div>
                                                <i class="pi pi-chevron-right text-gray-400 dark:text-gray-500 text-xs"></i>
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Account Section -->
                                    <div class="mb-4">
                                        <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Account</h4>
                                        <div class="space-y-1">
                                            <Link v-for="item in menuItems.account" :key="item.name" :href="item.href" @click="showQuickActionsModal = false"
                                                class="flex items-center space-x-4 p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800 group transition-colors">
                                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform', item.color]">
                                                    <i :class="[item.icon]"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white">{{ item.name }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.description }}</p>
                                                </div>
                                                <i class="pi pi-chevron-right text-gray-400 dark:text-gray-500 text-xs"></i>
                                            </Link>
                                            
                                            <!-- Logout -->
                                            <Link href="/logout" method="post" as="button" @click="showQuickActionsModal = false"
                                                class="w-full text-left flex items-center space-x-4 p-3 rounded-2xl hover:bg-red-50 dark:hover:bg-red-900/20 group transition-colors">
                                                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center group-hover:bg-red-200 dark:group-hover:bg-red-800/50 transition-colors">
                                                    <i class="pi pi-sign-out text-red-600 dark:text-red-400"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-semibold text-red-900 dark:text-red-400">Sign Out</p>
                                                    <p class="text-xs text-red-600 dark:text-red-500">Logout from account</p>
                                                </div>
                                                <i class="pi pi-chevron-right text-red-400 dark:text-red-500 text-xs"></i>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Footer -->
                                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center justify-center space-x-6">
                                        <div class="text-center">
                                            <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-1">
                                                <i class="pi pi-shield text-indigo-600 dark:text-indigo-400 text-xs"></i>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">Secure</p>
                                        </div>
                                        <div class="text-center">
                                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-1">
                                                <i class="pi pi-clock text-green-600 dark:text-green-400 text-xs"></i>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">24/7</p>
                                        </div>
                                        <div class="text-center">
                                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-1">
                                                <i class="pi pi-phone text-blue-600 dark:text-blue-400 text-xs"></i>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">Support</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </DashboardLayout>
</template>

<style scoped>
/* Modal backdrop transition */
.modal-backdrop-enter-active,
.modal-backdrop-leave-active {
    transition: opacity 0.3s ease;
}
.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
    opacity: 0;
}

/* Slide up transition for bottom sheet */
.slide-up-enter-active {
    transition: transform 0.3s ease-out;
}
.slide-up-leave-active {
    transition: transform 0.2s ease-in;
}
.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
}
</style>
