<script setup>
/**
 * Notifications Index Page
 * Full page view of all user notifications
 */
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from 'primevue/button';
import DataView from 'primevue/dataview';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import { formatRelativeDate } from '@/Utils/formatters';

defineOptions({
    layout: DashboardLayout,
});

const props = defineProps({
    notifications: {
        type: Object,
        default: () => ({ data: [] }),
    },
    unreadCount: {
        type: Number,
        default: 0,
    },
});

const page = usePage();
const confirm = useConfirm();
const isLoading = ref(false);
const activeTab = ref('all'); // 'all' | 'unread'

const allNotifications = computed(() => props.notifications.data || []);
const filteredNotifications = computed(() => {
    if (activeTab.value === 'unread') {
        return allNotifications.value.filter(n => !n.read);
    }
    return allNotifications.value;
});

const hasNotifications = computed(() => filteredNotifications.value.length > 0);

// Get notification icon with fallback
const getNotificationIcon = (notification) => {
    const iconMap = {
        transfer: 'pi-send',
        transfer_received: 'pi-download',
        deposit: 'pi-download',
        card: 'pi-credit-card',
        loan: 'pi-wallet',
        grant: 'pi-dollar',
        security: 'pi-shield',
        support: 'pi-comments',
        kyc: 'pi-id-card',
        reward: 'pi-star-fill',
        promo: 'pi-gift',
        admin: 'pi-megaphone',
        system: 'pi-info-circle',
    };
    return notification.icon || iconMap[notification.type] || 'pi-bell';
};

// Mark single notification as read
const markAsRead = (notification) => {
    if (notification.read) return;
    
    router.post(`/notifications/${notification.id}/mark-read`, {}, {
        preserveScroll: true,
    });
};

// Navigate to notification link
const viewNotification = (notification) => {
    markAsRead(notification);
    if (notification.href) {
        router.visit(notification.href);
    }
};

// Mark all as read
const markAllAsRead = () => {
    isLoading.value = true;
    router.post('/notifications/mark-all-read', {}, {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

// Delete single notification
const deleteNotification = (notification) => {
    confirm.require({
        message: 'Are you sure you want to delete this notification?',
        header: 'Delete Notification',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/notifications/${notification.id}`, {
                preserveScroll: true,
            });
        },
    });
};

// Clear all notifications
const clearAll = () => {
    confirm.require({
        message: 'Are you sure you want to delete all notifications? This cannot be undone.',
        header: 'Clear All Notifications',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            isLoading.value = true;
            router.delete('/notifications', {
                preserveScroll: true,
                onFinish: () => {
                    isLoading.value = false;
                },
            });
        },
    });
};
</script>

<template>
    <Head title="Notifications" />
    
    <ConfirmDialog />
    
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Notifications
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ props.unreadCount }} unread notification{{ props.unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                
                <div class="flex items-center gap-2">
                    <Button
                        v-if="props.unreadCount > 0"
                        label="Mark All Read"
                        icon="pi pi-check-circle"
                        size="small"
                        outlined
                        :loading="isLoading"
                        @click="markAllAsRead"
                    />
                    <Button
                        v-if="allNotifications.length > 0"
                        label="Clear All"
                        icon="pi pi-trash"
                        size="small"
                        severity="danger"
                        outlined
                        :loading="isLoading"
                        @click="clearAll"
                    />
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                <button
                    :class="[
                        'flex-1 py-2.5 px-4 text-sm font-medium rounded-lg transition-all duration-200',
                        activeTab === 'all' 
                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' 
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                    ]"
                    @click="activeTab = 'all'"
                >
                    All Notifications
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300">
                        {{ allNotifications.length }}
                    </span>
                </button>
                <button
                    :class="[
                        'flex-1 py-2.5 px-4 text-sm font-medium rounded-lg transition-all duration-200 relative',
                        activeTab === 'unread' 
                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' 
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                    ]"
                    @click="activeTab = 'unread'"
                >
                    Unread
                    <span 
                        v-if="props.unreadCount > 0" 
                        class="ml-2 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400"
                    >
                        {{ props.unreadCount }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
            <template v-if="hasNotifications">
                <div
                    v-for="notification in filteredNotifications"
                    :key="notification.id"
                    :class="[
                        'flex gap-4 p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-all duration-200 group',
                        notification.read
                            ? 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                            : 'bg-indigo-50/50 dark:bg-indigo-900/20 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'
                    ]"
                >
                    <!-- Icon -->
                    <div
                        :class="[
                            'flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center shadow-sm',
                            notification.color
                        ]"
                    >
                        <i :class="['pi text-lg', getNotificationIcon(notification)]"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 cursor-pointer" @click="viewNotification(notification)">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <p
                                    :class="[
                                        'text-base font-semibold',
                                        notification.read ? 'text-gray-700 dark:text-gray-300' : 'text-gray-900 dark:text-white'
                                    ]"
                                >
                                    {{ notification.title }}
                                </p>
                                <!-- Unread indicator -->
                                <span
                                    v-if="!notification.read"
                                    class="flex-shrink-0 w-2.5 h-2.5 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full shadow-sm shadow-indigo-500/50"
                                ></span>
                            </div>
                            <Tag 
                                :value="notification.type.replace('_', ' ')" 
                                class="capitalize hidden sm:inline-flex"
                                :severity="notification.read ? 'secondary' : 'info'"
                            />
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                            {{ notification.message }}
                        </p>
                        <div class="flex items-center gap-4 mt-2">
                            <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                <i class="pi pi-clock text-[10px]"></i>
                                {{ formatRelativeDate(notification.created_at) }}
                            </p>
                            <Tag 
                                :value="notification.type.replace('_', ' ')" 
                                class="capitalize sm:hidden"
                                :severity="notification.read ? 'secondary' : 'info'"
                                size="small"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-1">
                        <Button
                            v-if="!notification.read"
                            icon="pi pi-check"
                            size="small"
                            text
                            rounded
                            severity="secondary"
                            v-tooltip.left="'Mark as read'"
                            class="opacity-0 group-hover:opacity-100 transition-opacity"
                            @click.stop="markAsRead(notification)"
                        />
                        <Button
                            icon="pi pi-trash"
                            size="small"
                            text
                            rounded
                            severity="danger"
                            v-tooltip.left="'Delete'"
                            class="opacity-0 group-hover:opacity-100 transition-opacity"
                            @click.stop="deleteNotification(notification)"
                        />
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div v-else class="p-12 text-center">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 rounded-2xl">
                    <i class="text-3xl text-gray-400 pi pi-bell-slash"></i>
                </div>
                <p class="text-lg font-medium text-gray-600 dark:text-gray-300">All caught up!</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                    No {{ activeTab === 'unread' ? 'unread ' : '' }}notifications to show
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="props.notifications.links && props.notifications.links.length > 3" class="mt-6 flex justify-center gap-2">
            <template v-for="link in props.notifications.links" :key="link.label">
                <Button
                    v-if="link.url"
                    :label="link.label.replace('&laquo;', '«').replace('&raquo;', '»')"
                    :severity="link.active ? 'primary' : 'secondary'"
                    :outlined="!link.active"
                    size="small"
                    @click="router.visit(link.url)"
                />
            </template>
        </div>
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
