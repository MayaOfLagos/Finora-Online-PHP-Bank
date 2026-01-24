<script setup>
/**
 * Sidebar Component
 * Desktop sidebar navigation with sub-navigation support
 */
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/Components/Common/AppLogo.vue';
import { NAV_ITEMS } from '@/Utils/constants';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const currentPath = computed(() => page.url);

// Track expanded menu items
const expandedItems = ref(new Set());

const isActive = (href) => {
    if (href === '/dashboard' || href === '#') {
        return currentPath.value === '/dashboard' || currentPath.value === '/';
    }
    return currentPath.value.startsWith(href);
};

const isParentActive = (item) => {
    if (!item.children) return false;
    return item.children.some(child => isActive(child.href));
};

const toggleSubmenu = (itemName) => {
    if (expandedItems.value.has(itemName)) {
        expandedItems.value.delete(itemName);
    } else {
        expandedItems.value.add(itemName);
    }
};

const isExpanded = (itemName) => {
    return expandedItems.value.has(itemName) || isParentActive(NAV_ITEMS.find(item => item.name === itemName));
};
</script>

<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-40 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 hidden lg:flex lg:flex-col',
            collapsed ? 'w-20' : 'w-64'
        ]"
    >
        <!-- Logo -->
        <div class="flex items-center h-16 px-4 border-b border-gray-200 dark:border-gray-700">
            <Link href="/dashboard" class="flex items-center">
                <AppLogo :show-text="!collapsed" />
            </Link>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4">
            <ul class="space-y-1">
                <li v-for="item in NAV_ITEMS" :key="item.name">
                    <!-- Parent Menu Item -->
                    <div v-if="item.children">
                        <!-- Parent Button -->
                        <button
                            @click="toggleSubmenu(item.name)"
                            :class="[
                                'flex items-center gap-3 px-4 py-3 rounded-xl transition-all w-full text-left',
                                isParentActive(item) || isExpanded(item.name)
                                    ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 font-medium'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'
                            ]"
                            :title="collapsed ? item.name : ''"
                        >
                            <i :class="[item.icon, 'text-lg flex-shrink-0']"></i>
                            <span v-if="!collapsed" class="truncate flex-1">{{ item.name }}</span>
                            <i 
                                v-if="!collapsed"
                                :class="[
                                    'pi transition-transform text-xs',
                                    isExpanded(item.name) ? 'pi-angle-down' : 'pi-angle-right'
                                ]"
                            ></i>
                        </button>

                        <!-- Submenu -->
                        <Transition
                            enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <ul v-if="isExpanded(item.name) && !collapsed" class="mt-1 ml-4 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                                <li v-for="child in item.children" :key="child.name">
                                    <Link
                                        :href="child.href"
                                        :class="[
                                            'flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-sm',
                                            isActive(child.href)
                                                ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 font-medium'
                                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'
                                        ]"
                                    >
                                        <i :class="[child.icon, 'text-base flex-shrink-0']"></i>
                                        <span class="truncate">{{ child.name }}</span>
                                    </Link>
                                </li>
                            </ul>
                        </Transition>
                    </div>

                    <!-- Single Menu Item -->
                    <Link
                        v-else
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-4 py-3 rounded-xl transition-all',
                            isActive(item.href)
                                ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 font-medium'
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'
                        ]"
                        :title="collapsed ? item.name : ''"
                    >
                        <i :class="[item.icon, 'text-lg flex-shrink-0']"></i>
                        <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                    </Link>
                </li>
            </ul>
        </nav>

        <!-- User Section -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-4">
            <Link
                href="/profile"
                :class="[
                    'flex items-center gap-3 px-4 py-3 rounded-xl transition-colors',
                    'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200'
                ]"
            >
                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 dark:text-indigo-400 font-medium text-sm">
                        {{ user?.first_name?.charAt(0) || 'U' }}
                    </span>
                </div>
                <div v-if="!collapsed" class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ user?.first_name }} {{ user?.last_name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ user?.email }}
                    </p>
                </div>
            </Link>
        </div>

        <!-- Collapse Toggle -->
        <button
            @click="emit('toggle')"
            class="absolute -right-3 top-20 w-6 h-6 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500 transition-colors shadow-sm"
        >
            <i :class="collapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'" class="text-xs"></i>
        </button>
    </aside>
</template>
