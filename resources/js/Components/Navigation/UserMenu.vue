<script setup>
/**
 * UserMenu Component
 * User dropdown menu in header
 */
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Menu from 'primevue/menu';
import Avatar from 'primevue/avatar';
import { getInitials } from '@/Utils/formatters';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const menu = ref();

const menuItems = ref([
    {
        label: 'Profile',
        items: [
            {
                label: 'My Profile',
                icon: 'pi pi-user',
                command: () => router.visit('/profile'),
            },
            {
                label: 'Settings',
                icon: 'pi pi-cog',
                command: () => router.visit('/settings'),
            },
            {
                label: 'Security',
                icon: 'pi pi-shield',
                command: () => router.visit('/profile?tab=security'),
            },
        ],
    },
    {
        separator: true,
    },
    {
        label: 'Support',
        items: [
            {
                label: 'Help Center',
                icon: 'pi pi-question-circle',
                command: () => router.visit('/support'),
            },
            {
                label: 'Contact Us',
                icon: 'pi pi-envelope',
                command: () => router.visit('/support/new'),
            },
        ],
    },
    {
        separator: true,
    },
    {
        label: 'Logout',
        icon: 'pi pi-sign-out',
        command: () => router.post(route('logout')),
    },
]);

const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <div class="relative">
        <button
            @click="toggle"
            class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
            <Avatar
                :label="getInitials(`${user?.first_name} ${user?.last_name}`)"
                class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400"
                shape="circle"
            />
            <div class="hidden sm:block text-left">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ user?.first_name }} {{ user?.last_name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ user?.email }}
                </p>
            </div>
            <i class="pi pi-angle-down text-gray-400 dark:text-gray-500 hidden sm:block"></i>
        </button>

        <Menu
            ref="menu"
            :model="menuItems"
            :popup="true"
            :pt="{
                root: { class: 'w-56 dark:bg-gray-800 dark:border-gray-700' },
                item: { class: 'dark:hover:bg-gray-700' },
                label: { class: 'dark:text-gray-200' },
            }"
        />
    </div>
</template>
