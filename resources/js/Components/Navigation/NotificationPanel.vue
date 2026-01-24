<script setup>
/**
 * NotificationPanel Component
 * Dropdown panel showing user notifications
 */
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Badge from 'primevue/badge';
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

// Demo notifications (will be replaced with real data)
const demoNotifications = [
    {
        id: 1,
        type: 'transfer',
        title: 'Transfer Completed',
        message: 'Your wire transfer of $1,500.00 to John Doe has been completed.',
        icon: 'pi-send',
        color: 'text-green-600 bg-green-100',
        read: false,
        created_at: new Date(Date.now() - 1800000).toISOString(), // 30 mins ago
        href: '/transactions',
    },
    {
        id: 2,
        type: 'security',
        title: 'New Login Detected',
        message: 'A new login was detected from Chrome on Windows.',
        icon: 'pi-shield',
        color: 'text-blue-600 bg-blue-100',
        read: false,
        created_at: new Date(Date.now() - 3600000).toISOString(), // 1 hour ago
        href: '/settings/security',
    },
    {
        id: 3,
        type: 'deposit',
        title: 'Deposit Pending',
        message: 'Your check deposit of $2,500.00 is under review.',
        icon: 'pi-download',
        color: 'text-amber-600 bg-amber-100',
        read: false,
        created_at: new Date(Date.now() - 7200000).toISOString(), // 2 hours ago
        href: '/deposits',
    },
    {
        id: 4,
        type: 'card',
        title: 'Card Shipped',
        message: 'Your new Platinum card has been shipped.',
        icon: 'pi-credit-card',
        color: 'text-purple-600 bg-purple-100',
        read: true,
        created_at: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
        href: '/cards',
    },
    {
        id: 5,
        type: 'promo',
        title: 'Special Offer',
        message: 'Get 0% APR on balance transfers for 18 months!',
        icon: 'pi-gift',
        color: 'text-rose-600 bg-rose-100',
        read: true,
        created_at: new Date(Date.now() - 172800000).toISOString(), // 2 days ago
        href: '/offers',
    },
];

const allNotifications = computed(() =>
    props.notifications.length > 0 ? props.notifications : demoNotifications
);

const unreadCount = computed(() =>
    allNotifications.value.filter(n => !n.read).length
);

const hasNotifications = computed(() => allNotifications.value.length > 0);

// Toggle panel
const toggle = (event) => {
    op.value.toggle(event);
};

// Mark single notification as read
const markAsRead = (notification) => {
    emit('mark-read', notification.id);
    if (notification.href) {
        router.visit(notification.href);
        op.value.hide();
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
                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center px-1 text-[10px] font-bold text-white bg-red-500 rounded-full"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Overlay Panel -->
        <OverlayPanel
            ref="op"
            :pt="{
                root: { class: 'w-[380px] max-w-[calc(100vw-2rem)] shadow-xl border-0 dark:bg-gray-800' },
                content: { class: 'p-0' },
            }"
        >
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Notifications</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ unreadCount }} unread
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="unreadCount > 0"
                        label="Mark all read"
                        size="small"
                        text
                        class="text-xs"
                        @click="markAllAsRead"
                    />
                </div>
            </div>

            <!-- Notifications List -->
            <div class="max-h-[400px] overflow-y-auto">
                <div v-if="hasNotifications">
                    <div
                        v-for="notification in allNotifications"
                        :key="notification.id"
                        :class="[
                            'flex gap-3 p-4 cursor-pointer transition-colors border-b border-gray-50 dark:border-gray-700/50 last:border-0',
                            notification.read
                                ? 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                                : 'bg-indigo-50/50 dark:bg-indigo-900/20 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'
                        ]"
                        @click="markAsRead(notification)"
                    >
                        <!-- Icon -->
                        <div
                            :class="[
                                'flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center',
                                notification.color
                            ]"
                        >
                            <i :class="['pi', notification.icon]"></i>
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
                                    class="flex-shrink-0 w-2 h-2 bg-indigo-500 rounded-full mt-1.5"
                                ></span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">
                                {{ notification.message }}
                            </p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                                {{ formatRelativeDate(notification.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="p-8 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full dark:bg-gray-700">
                        <i class="text-2xl text-gray-400 pi pi-bell-slash"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No notifications yet</p>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="hasNotifications" class="p-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <Link href="/notifications" class="block">
                    <Button
                        label="View All Notifications"
                        class="w-full"
                        size="small"
                        outlined
                        @click="op.hide()"
                    />
                </Link>
            </div>
        </OverlayPanel>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
