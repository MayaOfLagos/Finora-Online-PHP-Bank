<script setup>
/**
 * DashboardLayout
 * Main authenticated layout with sidebar and bottom nav
 * Features: Dark mode, notifications, search, responsive design
 */
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Popover from 'primevue/popover';
import Button from 'primevue/button';

import AppLogo from '@/Components/Common/AppLogo.vue';
import ImpersonationBanner from '@/Components/Common/ImpersonationBanner.vue';
import Sidebar from '@/Components/Navigation/Sidebar.vue';
import BottomNav from '@/Components/Navigation/BottomNav.vue';
import UserMenu from '@/Components/Navigation/UserMenu.vue';
import NotificationPanel from '@/Components/Navigation/NotificationPanel.vue';
import DarkModeToggle from '@/Components/Navigation/DarkModeToggle.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { MOBILE_NAV_ITEMS } from '@/Utils/constants';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notifications = computed(() => page.props.notifications ?? []);
const unreadNotificationsCount = computed(() => page.props.unreadNotificationsCount ?? 0);
const isImpersonating = computed(() => page.props.auth?.isImpersonating ?? false);

// Initialize dark mode
const { initDarkMode, isDark } = useDarkMode();

// Sidebar state
const sidebarCollapsed = ref(false);
const mobileMenuOpen = ref(false);

// Mobile menu expanded items
const mobileExpandedItems = ref(new Set());

// Search state
const searchQuery = ref('');
const searchPopover = ref(null);
const isSearching = ref(false);
const searchResults = ref([]);

// Quick search suggestions
const quickSearchItems = [
    { label: 'Wire Transfer', icon: 'pi-globe', href: '/transfers/wire' },
    { label: 'Domestic Transfer', icon: 'pi-map-marker', href: '/transfers/domestic' },
    { label: 'Internal Transfer', icon: 'pi-arrow-right-arrow-left', href: '/transfers/internal' },
    { label: 'View Accounts', icon: 'pi-wallet', href: '/accounts' },
    { label: 'My Cards', icon: 'pi-credit-card', href: '/cards' },
    { label: 'Transaction History', icon: 'pi-history', href: '/transactions' },
    { label: 'Mobile Deposit', icon: 'pi-mobile', href: '/deposits/mobile' },
    { label: 'Check Deposit', icon: 'pi-file-check', href: '/deposits/check' },
    { label: 'Settings', icon: 'pi-cog', href: '/settings' },
];

// Check if path is active
const isActivePath = (href) => {
    const currentPath = page.url;
    if (href === '/dashboard' || href === '#') {
        return currentPath === '/dashboard' || currentPath === '/';
    }
    return currentPath.startsWith(href);
};

const isParentActivePath = (item) => {
    if (!item.children) return false;
    return item.children.some(child => isActivePath(child.href));
};

const toggleMobileSubmenu = (itemName) => {
    if (mobileExpandedItems.value.has(itemName)) {
        mobileExpandedItems.value.delete(itemName);
    } else {
        mobileExpandedItems.value.add(itemName);
    }
};

const isMobileExpanded = (itemName) => {
    const item = MOBILE_NAV_ITEMS.find(i => i.name === itemName);
    return mobileExpandedItems.value.has(itemName) || isParentActivePath(item);
};

// Responsive detection
const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

// Keyboard shortcut handler
const handleKeydown = (e) => {
    // Cmd/Ctrl + K to open search
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('global-search')?.focus();
    }
};

onMounted(() => {
    checkMobile();
    initDarkMode();
    window.addEventListener('resize', checkMobile);
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('keydown', handleKeydown);
});

// Toggle sidebar
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

// Close mobile menu
const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

// Search functionality
const handleSearch = () => {
    if (searchQuery.value.length >= 2) {
        isSearching.value = true;
        // Filter quick search items based on query
        searchResults.value = quickSearchItems.filter(item =>
            item.label.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
        isSearching.value = false;
    } else {
        searchResults.value = [];
    }
};

const navigateToSearch = (href) => {
    router.visit(href);
    searchQuery.value = '';
    searchResults.value = [];
    searchPopover.value?.hide();
};

// Watch search query
watch(searchQuery, handleSearch);

// Notification handlers
const handleMarkRead = (id) => {
    router.post(`/notifications/${id}/mark-read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Notification will be updated via page props refresh
        },
    });
};

const handleMarkAllRead = () => {
    router.post('/notifications/mark-all-read', {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Notifications will be updated via page props refresh
        },
    });
};
</script>

<template>
    <div class="min-h-screen transition-colors duration-300 bg-gray-50 dark:bg-gray-900">
        <Head :title="title" />

        <!-- Impersonation Banner -->
        <ImpersonationBanner />

        <!-- Toast notifications -->
        <Toast 
            position="top-right"
            :pt="{
                root: { class: 'w-full md:w-auto max-w-[calc(100vw-1rem)] md:max-w-sm' },
                message: { class: 'flex-1 max-w-full break-words' }
            }"
            class="mobile-toast"
        />
        <ConfirmDialog />

        <!-- Desktop Sidebar -->
        <Sidebar
            :collapsed="sidebarCollapsed"
            @toggle="toggleSidebar"
        />

        <!-- Mobile Sidebar Drawer -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-[60] bg-gray-900/50 lg:hidden"
                @click="closeMobileMenu"
            />
        </Transition>

        <!-- Mobile Sidebar Panel - Glassmorphism -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="-translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="-translate-x-full opacity-0"
        >
            <aside
                v-if="mobileMenuOpen"
                class="mobile-sidebar-glass fixed top-3 bottom-3 left-3 z-[70] flex flex-col w-[280px] lg:hidden overflow-hidden rounded-3xl border border-white/20 dark:border-white/10 shadow-2xl shadow-black/20"
            >
                <!-- Glass overlay for depth -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/40 via-transparent to-white/20 dark:from-white/10 dark:to-white/5 pointer-events-none"></div>
                
                <!-- Mobile Sidebar Header -->
                <div class="relative flex items-center justify-between h-16 px-5 border-b border-white/20 dark:border-white/10">
                    <Link href="/dashboard" @click="closeMobileMenu">
                        <AppLogo :show-text="false" />
                    </Link>
                    <button
                        @click="closeMobileMenu"
                        class="p-2.5 text-gray-600 dark:text-gray-300 transition-all hover:bg-white/50 dark:hover:bg-white/10 rounded-xl active:scale-95"
                    >
                        <i class="text-lg pi pi-times"></i>
                    </button>
                </div>

                <!-- Mobile Navigation -->
                <nav class="relative flex-1 p-4 overflow-y-auto">
                    <ul class="space-y-1">
                        <li v-for="item in MOBILE_NAV_ITEMS" :key="item.name">
                            <!-- Parent Menu Item with Children -->
                            <div v-if="item.children">
                                <!-- Parent Button -->
                                <button
                                    @click="toggleMobileSubmenu(item.name)"
                                    :class="[
                                        'flex items-center gap-3 px-4 py-3 rounded-xl transition-all w-full text-left',
                                        isParentActivePath(item) || isMobileExpanded(item.name)
                                            ? 'bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-medium shadow-sm'
                                            : 'text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10'
                                    ]"
                                >
                                    <i :class="[item.icon, 'text-lg']"></i>
                                    <span class="flex-1">{{ item.name }}</span>
                                    <i 
                                        :class="[
                                            'pi transition-transform duration-200 text-xs',
                                            isMobileExpanded(item.name) ? 'pi-angle-down rotate-0' : 'pi-angle-right'
                                        ]"
                                    ></i>
                                </button>

                                <!-- Submenu -->
                                <Transition
                                    enter-active-class="transition-all duration-200 ease-out"
                                    enter-from-class="opacity-0 -translate-y-2 max-h-0"
                                    enter-to-class="opacity-100 translate-y-0 max-h-96"
                                    leave-active-class="transition-all duration-150 ease-in"
                                    leave-from-class="opacity-100 translate-y-0 max-h-96"
                                    leave-to-class="opacity-0 -translate-y-2 max-h-0"
                                >
                                    <ul v-if="isMobileExpanded(item.name)" class="mt-1 ml-4 space-y-1 border-l-2 border-indigo-300/30 dark:border-indigo-500/30 pl-3 overflow-hidden">
                                        <li v-for="child in item.children" :key="child.name">
                                            <Link
                                                :href="child.href"
                                                @click="closeMobileMenu"
                                                :class="[
                                                    'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all text-sm',
                                                    isActivePath(child.href)
                                                        ? 'bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-medium'
                                                        : 'text-gray-600 dark:text-gray-300 hover:bg-white/50 dark:hover:bg-white/10'
                                                ]"
                                            >
                                                <i :class="[child.icon, 'text-base']"></i>
                                                <span>{{ child.name }}</span>
                                            </Link>
                                        </li>
                                    </ul>
                                </Transition>
                            </div>

                            <!-- Single Menu Item -->
                            <Link
                                v-else
                                :href="item.href"
                                @click="closeMobileMenu"
                                :class="[
                                    'flex items-center gap-3 px-4 py-3 rounded-xl transition-all',
                                    isActivePath(item.href)
                                        ? 'bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-medium shadow-sm'
                                        : 'text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10'
                                ]"
                            >
                                <i :class="[item.icon, 'text-lg']"></i>
                                <span>{{ item.name }}</span>
                            </Link>
                        </li>
                    </ul>
                </nav>

                <!-- Mobile User Section -->
                <div class="relative p-4 border-t border-white/20 dark:border-white/10">
                    <Link
                        href="/profile"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 px-4 py-3 text-gray-700 dark:text-gray-200 transition-all rounded-xl hover:bg-white/50 dark:hover:bg-white/10"
                    >
                        <div class="flex items-center justify-center w-10 h-10 rounded-full overflow-hidden bg-indigo-500/20 ring-2 ring-white/50">
                            <img
                                v-if="user?.avatar_url"
                                :src="user.avatar_url"
                                :alt="user?.full_name || 'User'"
                                class="w-full h-full object-cover"
                            />
                            <span v-else class="font-medium text-indigo-700 dark:text-indigo-300">
                                {{ user?.initials || user?.first_name?.charAt(0) || 'U' }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                {{ user?.first_name }} {{ user?.last_name }}
                            </p>
                            <p class="text-xs text-gray-500 truncate dark:text-gray-400">
                                {{ user?.email }}
                            </p>
                        </div>
                    </Link>
                </div>
            </aside>
        </Transition>

        <!-- Main Content Area -->
        <div
            :class="[
                'transition-all duration-300',
                sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64',
                isImpersonating ? 'pt-12 sm:pt-10' : ''
            ]"
        >
            <!-- Top Header -->
            <header 
                class="sticky z-30 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700" 
                :class="isImpersonating ? 'top-12 sm:top-10' : 'top-0'"
            >
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <!-- Mobile Logo & Menu Toggle -->
                    <div class="flex items-center gap-3 lg:hidden">
                        <button
                            @click="mobileMenuOpen = true"
                            class="p-2 text-gray-500 transition-colors dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl"
                        >
                            <i class="text-xl pi pi-bars"></i>
                        </button>
                        <Link href="/dashboard">
                            <AppLogo :show-text="false" />
                        </Link>
                    </div>

                    <!-- Page Title (Desktop) -->
                    <h1 class="hidden mx-4 text-lg font-semibold text-gray-900 lg:block dark:text-white">
                        {{ title }}
                    </h1>

                    <!-- Search Bar (Desktop) -->
                    <div class="items-center flex-1 hidden max-w-md lg:flex">
                        <IconField class="w-full">
                            <InputIcon class="pi pi-search" />
                            <InputText
                                id="global-search"
                                v-model="searchQuery"
                                placeholder="Search... (Ctrl+K)"
                                class="w-full !pl-10 !py-2.5 !bg-gray-50 dark:!bg-gray-700 dark:!text-white !border-gray-200 dark:!border-gray-600 !rounded-xl focus:!bg-white dark:focus:!bg-gray-600"
                                @focus="(e) => searchPopover?.show(e)"
                            />
                        </IconField>

                        <!-- Search Results Popover -->
                        <Popover ref="searchPopover" :pt="{ root: { class: 'w-80 mt-2' } }">
                            <div class="p-2">
                                <!-- Search Results -->
                                <div v-if="searchResults.length > 0">
                                    <p class="px-3 py-2 text-xs font-medium text-gray-400 dark:text-gray-500">
                                        Results
                                    </p>
                                    <button
                                        v-for="result in searchResults"
                                        :key="result.href"
                                        class="flex items-center w-full gap-3 p-3 text-left transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                        @click="navigateToSearch(result.href)"
                                    >
                                        <i :class="['pi', result.icon, 'text-gray-400']"></i>
                                        <span class="text-gray-700 dark:text-gray-200">{{ result.label }}</span>
                                    </button>
                                </div>

                                <!-- Quick Actions -->
                                <div v-else>
                                    <p class="px-3 py-2 text-xs font-medium text-gray-400 dark:text-gray-500">
                                        Quick Actions
                                    </p>
                                    <button
                                        v-for="item in quickSearchItems"
                                        :key="item.href"
                                        class="flex items-center w-full gap-3 p-3 text-left transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                        @click="navigateToSearch(item.href)"
                                    >
                                        <i :class="['pi', item.icon, 'text-gray-400']"></i>
                                        <span class="text-gray-700 dark:text-gray-200">{{ item.label }}</span>
                                    </button>
                                </div>
                            </div>
                        </Popover>
                    </div>

                    <!-- Right Section - Actions -->
                    <div class="flex items-center gap-1 sm:gap-2">
                        <!-- Dark Mode Toggle -->
                        <DarkModeToggle />

                        <!-- Help Button -->
                        <button
                            class="hidden p-2 text-gray-500 transition-colors sm:flex dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl"
                            v-tooltip.bottom="'Help & Support'"
                        >
                            <i class="text-xl pi pi-question-circle"></i>
                        </button>

                        <!-- Notifications -->
                        <NotificationPanel
                            :notifications="notifications"
                            :unread-count="unreadNotificationsCount"
                            @mark-read="handleMarkRead"
                            @mark-all-read="handleMarkAllRead"
                        />

                        <!-- Divider -->
                        <div class="hidden w-px h-8 mx-1 bg-gray-200 sm:block dark:bg-gray-600"></div>

                        <!-- User Menu -->
                        <UserMenu />
                    </div>
                </div>

                <!-- Mobile Search Bar (expandable) -->
                <div class="px-4 pb-3 lg:hidden" v-if="false">
                    <IconField class="w-full">
                        <InputIcon class="pi pi-search" />
                        <InputText
                            v-model="searchQuery"
                            placeholder="Search..."
                            class="w-full !pl-10 !py-2 !bg-gray-100 dark:!bg-gray-700 !border-0 !rounded-xl"
                        />
                    </IconField>
                </div>
            </header>

            <!-- Page Content -->
            <main class="min-h-screen p-4 pb-24 sm:p-6 lg:pb-6">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <BottomNav @open-menu="mobileMenuOpen = true" />
    </div>
</template>

<style scoped>
/* Mobile Sidebar Glassmorphism */
.mobile-sidebar-glass {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
}

.dark .mobile-sidebar-glass {
    background: rgba(30, 30, 40, 0.85);
}

/* Mobile Toast Responsiveness */
.mobile-toast :deep(.p-toast) {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 50;
    max-width: calc(100vw - 2rem);
}

.mobile-toast :deep(.p-toast-message) {
    margin: 0;
    box-sizing: border-box;
}

.mobile-toast :deep(.p-toast-message-content) {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    word-break: break-word;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.mobile-toast :deep(.p-toast-icon-close) {
    flex-shrink: 0;
    margin-left: 0.5rem;
}

/* Ensure toast doesn't hide content on mobile */
@media (max-width: 768px) {
    .mobile-toast :deep(.p-toast) {
        left: 0.5rem;
        right: 0.5rem;
        max-width: none;
    }
    
    .mobile-toast :deep(.p-toast-message) {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
}

@media (min-width: 768px) {
    .mobile-toast :deep(.p-toast) {
        top: 1.5rem;
        right: 1.5rem;
    }
    
    .mobile-toast :deep(.p-toast-message-content) {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
}
</style>