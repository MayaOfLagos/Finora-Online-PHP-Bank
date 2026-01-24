<script setup>
/**
 * BottomNav Component
 * Mobile bottom navigation bar
 */
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BOTTOM_NAV_ITEMS } from '@/Utils/constants';

const page = usePage();

const currentPath = computed(() => page.url);

const isActive = (href) => {
    if (href === '/dashboard') {
        return currentPath.value === '/dashboard' || currentPath.value === '/';
    }
    return currentPath.value.startsWith(href);
};
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 lg:hidden safe-area-bottom">
        <div class="flex items-center justify-around h-16 px-2">
            <Link
                v-for="item in BOTTOM_NAV_ITEMS"
                :key="item.name"
                :href="item.href"
                :class="[
                    'flex flex-col items-center justify-center flex-1 h-full gap-1 transition-colors',
                    isActive(item.href)
                        ? 'text-indigo-600 dark:text-indigo-400'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                ]"
            >
                <i :class="[item.icon, 'text-xl']"></i>
                <span class="text-xs font-medium">{{ item.name }}</span>
            </Link>

            <!-- More Menu Button -->
            <button
                @click="$emit('openMenu')"
                class="flex flex-col items-center justify-center flex-1 h-full gap-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
            >
                <i class="pi pi-bars text-xl"></i>
                <span class="text-xs font-medium">More</span>
            </button>
        </div>
    </nav>
</template>

<style scoped>
.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
