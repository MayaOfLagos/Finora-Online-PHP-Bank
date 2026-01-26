<script setup>
/**
 * NotificationPanel Component
 * Dropdown panel showing user notifications
 * Desktop: PrimeVue OverlayPanel
 * Mobile: Glassmorphism slide-in panel
 */
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import OverlayPanel from 'primevue/overlaypanel';
import { formatRelativeDate } from '@/Utils/formatters';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['mark-read', 'mark-all-read', 'clear']);

const op = ref(null);
const isMobile = ref(false);
const mobileOpen = ref(false);
const activeTab = ref('all'); // 'all' | 'unread'

// Check mobile on mount and resize
const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

// Lock body scroll when mobile panel is open
watch(mobileOpen, (isOpen) => {
    if (isMobile.value) {
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }
});

// Demo notifications (will be replaced with real data)
const demoNotifications = [
    {
        id: 1,
        type: 'transfer',
        title: 'Transfer Completed',
        message: 'Your wire transfer of $1,500.00 to John Doe has been completed.',
        icon: 'pi-send',
        color: 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
        read: false,
        created_at: new Date(Date.now() - 1800000).toISOString(),
        href: '/transactions',
    },
    {
        id: 2,
        type: 'security',
        title: 'New Login Detected',
        message: 'A new login was detected from Chrome on Windows.',
        icon: 'pi-shield',
        color: 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
        read: false,
        created_at: new Date(Date.now() - 3600000).toISOString(),
        href: '/dashboard',
    },
    {
        id: 3,
        type: 'deposit',
        title: 'Deposit Pending',
        message: 'Your check deposit of $2,500.00 is under review.',
        icon: 'pi-download',
        color: 'text-amber-600 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400',
        read: false,
        created_at: new Date(Date.now() - 7200000).toISOString(),
        href: '/deposits',
    },
    {
        id: 4,
        type: 'card',
        title: 'Card Shipped',
        message: 'Your new Platinum card has been shipped.',
        icon: 'pi-credit-card',
        color: 'text-purple-600 bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400',
        read: true,
        created_at: new Date(Date.now() - 86400000).toISOString(),
        href: '/cards',
    },
    {
        id: 5,
        type: 'promo',
        title: 'Special Offer',
        message: 'Get 0% APR on balance transfers for 18 months!',
        icon: 'pi-gift',
        color: 'text-rose-600 bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400',
        read: true,
        created_at: new Date(Date.now() - 172800000).toISOString(),
        href: '/dashboard',
    },
];

const allNotifications = computed(() =>
    props.notifications.length > 0 ? props.notifications : demoNotifications
);

const filteredNotifications = computed(() => {
    if (activeTab.value === 'unread') {
        return allNotifications.value.filter(n => !n.read);
    }
    return allNotifications.value;
});

const unreadCount = computed(() =>
    allNotifications.value.filter(n => !n.read).length
);

const hasNotifications = computed(() => filteredNotifications.value.length > 0);

// Toggle panel
const toggle = (event) => {
    if (isMobile.value) {
        mobileOpen.value = !mobileOpen.value;
    } else {
        op.value.toggle(event);
    }
};

// Close mobile panel
const closeMobilePanel = () => {
    mobileOpen.value = false;
};

// Mark single notification as read
const markAsRead = (notification) => {
    emit('mark-read', notification.id);
    if (notification.href) {
        router.visit(notification.href);
        if (isMobile.value) {
            closeMobilePanel();
        } else {
            op.value.hide();
        }
    }
};

// Mark all as read
const markAllAsRead = () => {
    emit('mark-all-read');
};

// Clear all notifications
const clearAll = () => {
    emit('clear');
};

// Get notification icon with fallback
const getNotificationIcon = (notification) => {
    const iconMap = {
        transfer: 'pi-send',
        security: 'pi-shield',
        deposit: 'pi-download',
        card: 'pi-credit-card',
        loan: 'pi-wallet',
        grant: 'pi-dollar',
        support: 'pi-comments',
        promo: 'pi-gift',
        system: 'pi-info-circle',
    };
    return notification.icon || iconMap[notification.type] || 'pi-bell';
};

// Expose toggle method
defineExpose({ toggle });
</script>

<template>
    <div class="notification-panel">
        <!-- Trigger Button -->
        <button
            class="relative p-2 text-gray-500 transition-colors dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl"
            @click="toggle"
            v-tooltip.bottom="'Notifications'"
        >
            <i class="text-xl pi pi-bell"></i>
            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center px-1 text-[10px] font-bold text-white bg-red-500 rounded-full animate-pulse"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Desktop: PrimeVue Overlay Panel -->
        <OverlayPanel
            ref="op"
            class="hidden lg:block"
            :pt="{
                root: { class: 'w-[400px] max-w-[calc(100vw-2rem)] shadow-2xl border border-gray-100 dark:border-gray-700 dark:bg-gray-800 rounded-2xl overflow-hidden' },
                content: { class: 'p-0' },
            }"
        >
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-500/5 to-purple-500/5 dark:from-indigo-500/10 dark:to-purple-500/10">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="pi pi-bell text-indigo-500"></i>
                        Notifications
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ unreadCount }} unread message{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="unreadCount > 0"
                        icon="pi pi-check-circle"
                        size="small"
                        text
                        rounded
                        severity="secondary"
                        v-tooltip.left="'Mark all as read'"
                        @click="markAllAsRead"
                    />
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-gray-100 dark:border-gray-700">
                <button
                    :class="[
                        'flex-1 py-2.5 text-xs font-medium transition-colors',
                        activeTab === 'all' 
                            ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-500' 
                            : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                    ]"
                    @click="activeTab = 'all'"
                >
                    All
                </button>
                <button
                    :class="[
                        'flex-1 py-2.5 text-xs font-medium transition-colors relative',
                        activeTab === 'unread' 
                            ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-500' 
                            : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                    ]"
                    @click="activeTab = 'unread'"
                >
                    Unread
                    <span v-if="unreadCount > 0" class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                        {{ unreadCount }}
                    </span>
                </button>
            </div>

            <!-- Notifications List -->
            <div class="max-h-[350px] overflow-y-auto">
                <div v-if="hasNotifications">
                    <div
                        v-for="notification in filteredNotifications"
                        :key="notification.id"
                        :class="[
                            'flex gap-3 p-4 cursor-pointer transition-all duration-200 border-b border-gray-50 dark:border-gray-700/50 last:border-0 group',
                            notification.read
                                ? 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                                : 'bg-indigo-50/50 dark:bg-indigo-900/20 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'
                        ]"
                        @click="markAsRead(notification)"
                    >
                        <!-- Icon -->
                        <div
                            :class="[
                                'flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110',
                                notification.color
                            ]"
                        >
                            <i :class="['pi', getNotificationIcon(notification)]"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p
                                    :class="[
                                        'text-sm font-medium truncate',
                                        notification.read ? 'text-gray-700 dark:text-gray-300' : 'text-gray-900 dark:text-white'
                                    ]"
                                >
                                    {{ notification.title }}
                                </p>
                                <!-- Unread indicator -->
                                <span
                                    v-if="!notification.read"
                                    class="flex-shrink-0 w-2 h-2 bg-indigo-500 rounded-full mt-1.5 animate-pulse"
                                ></span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">
                                {{ notification.message }}
                            </p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5 flex items-center gap-1">
                                <i class="pi pi-clock text-[8px]"></i>
                                {{ formatRelativeDate(notification.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="p-8 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 rounded-2xl">
                        <i class="text-2xl text-gray-400 pi pi-bell-slash"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">All caught up!</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">No {{ activeTab === 'unread' ? 'unread ' : '' }}notifications</p>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="allNotifications.length > 0" class="p-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <Link href="/notifications" class="block">
                    <Button
                        label="View All Notifications"
                        icon="pi pi-external-link"
                        iconPos="right"
                        class="w-full"
                        size="small"
                        text
                        @click="op.hide()"
                    />
                </Link>
            </div>
        </OverlayPanel>

        <!-- Mobile: Glassmorphism Slide Panel -->
        <Teleport to="body">
            <!-- Backdrop -->
            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="mobileOpen && isMobile"
                    class="fixed inset-0 z-[80] bg-black/40 backdrop-blur-sm lg:hidden"
                    @click="closeMobilePanel"
                />
            </Transition>

            <!-- Mobile Panel -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="translate-x-full opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-full opacity-0"
            >
                <aside
                    v-if="mobileOpen && isMobile"
                    class="mobile-notification-glass fixed top-3 bottom-3 right-3 z-[90] flex flex-col w-[calc(100%-24px)] max-w-[340px] lg:hidden overflow-hidden rounded-3xl border border-white/20 dark:border-white/10 shadow-2xl shadow-black/20"
                >
                    <!-- Glass overlay for depth -->
                    <div class="absolute inset-0 bg-gradient-to-br from-white/40 via-transparent to-white/20 dark:from-white/10 dark:to-white/5 pointer-events-none"></div>

                    <!-- Header -->
                    <div class="relative flex items-center justify-between h-16 px-5 border-b border-white/20 dark:border-white/10">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                    <i class="pi pi-bell text-white text-sm"></i>
                                </div>
                                Notifications
                            </h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                v-if="unreadCount > 0"
                                @click="markAllAsRead"
                                class="p-2 text-indigo-600 dark:text-indigo-400 transition-all hover:bg-white/50 dark:hover:bg-white/10 rounded-xl active:scale-95"
                                v-tooltip.left="'Mark all read'"
                            >
                                <i class="pi pi-check-circle"></i>
                            </button>
                            <button
                                @click="closeMobilePanel"
                                class="p-2.5 text-gray-600 dark:text-gray-300 transition-all hover:bg-white/50 dark:hover:bg-white/10 rounded-xl active:scale-95"
                            >
                                <i class="text-lg pi pi-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Tabs -->
                    <div class="relative flex mx-4 mt-4 p-1 bg-white/30 dark:bg-white/10 rounded-2xl border border-white/20 dark:border-white/10">
                        <button
                            :class="[
                                'flex-1 py-2 text-xs font-medium rounded-xl transition-all duration-200',
                                activeTab === 'all' 
                                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' 
                                    : 'text-gray-600 dark:text-gray-400'
                            ]"
                            @click="activeTab = 'all'"
                        >
                            All
                        </button>
                        <button
                            :class="[
                                'flex-1 py-2 text-xs font-medium rounded-xl transition-all duration-200 relative',
                                activeTab === 'unread' 
                                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' 
                                    : 'text-gray-600 dark:text-gray-400'
                            ]"
                            @click="activeTab = 'unread'"
                        >
                            Unread
                            <span 
                                v-if="unreadCount > 0" 
                                class="absolute -top-1 -right-1 min-w-[16px] h-[16px] flex items-center justify-center px-1 text-[9px] font-bold text-white bg-red-500 rounded-full"
                            >
                                {{ unreadCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Notifications List -->
                    <div class="relative flex-1 overflow-y-auto p-4 space-y-3">
                        <template v-if="hasNotifications">
                            <div
                                v-for="notification in filteredNotifications"
                                :key="notification.id"
                                :class="[
                                    'notification-card flex gap-3 p-4 rounded-2xl cursor-pointer transition-all duration-200 border active:scale-[0.98]',
                                    notification.read
                                        ? 'bg-white/50 dark:bg-gray-800/50 border-white/30 dark:border-white/10'
                                        : 'bg-indigo-50/80 dark:bg-indigo-900/40 border-indigo-200/50 dark:border-indigo-500/20'
                                ]"
                                @click="markAsRead(notification)"
                            >
                                <!-- Icon -->
                                <div
                                    :class="[
                                        'flex-shrink-0 w-11 h-11 rounded-xl flex items-center justify-center shadow-sm',
                                        notification.color
                                    ]"
                                >
                                    <i :class="['pi text-base', getNotificationIcon(notification)]"></i>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p
                                            :class="[
                                                'text-sm font-semibold truncate',
                                                notification.read ? 'text-gray-700 dark:text-gray-300' : 'text-gray-900 dark:text-white'
                                            ]"
                                        >
                                            {{ notification.title }}
                                        </p>
                                        <!-- Unread dot -->
                                        <span
                                            v-if="!notification.read"
                                            class="flex-shrink-0 w-2.5 h-2.5 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full mt-1 shadow-sm shadow-indigo-500/50"
                                        ></span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2 leading-relaxed">
                                        {{ notification.message }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2 flex items-center gap-1">
                                        <i class="pi pi-clock text-[8px]"></i>
                                        {{ formatRelativeDate(notification.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div v-else class="flex flex-col items-center justify-center h-full py-12">
                            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-gray-100/80 to-gray-50/80 dark:from-gray-700/80 dark:to-gray-800/80 flex items-center justify-center mb-4 shadow-lg">
                                <i class="pi pi-bell-slash text-3xl text-gray-400"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">All caught up!</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                No {{ activeTab === 'unread' ? 'unread ' : '' }}notifications
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div v-if="allNotifications.length > 0" class="relative p-4 border-t border-white/20 dark:border-white/10">
                        <Link 
                            href="/notifications" 
                            @click="closeMobilePanel"
                            class="block w-full py-3 px-4 text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-white/50 dark:bg-white/10 hover:bg-white/70 dark:hover:bg-white/20 rounded-xl transition-all duration-200 active:scale-[0.98]"
                        >
                            View All Notifications
                            <i class="pi pi-arrow-right ml-2 text-xs"></i>
                        </Link>
                    </div>
                </aside>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Mobile Notification Panel Glassmorphism */
.mobile-notification-glass {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(24px) saturate(200%);
    -webkit-backdrop-filter: blur(24px) saturate(200%);
}

.dark .mobile-notification-glass {
    background: rgba(30, 30, 40, 0.85);
}

/* Notification card hover effect */
.notification-card {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

/* Smooth scrollbar for mobile */
.mobile-notification-glass ::-webkit-scrollbar {
    width: 4px;
}

.mobile-notification-glass ::-webkit-scrollbar-track {
    background: transparent;
}

.mobile-notification-glass ::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 2px;
}

.dark .mobile-notification-glass ::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
}

/* Animation for unread badge */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
