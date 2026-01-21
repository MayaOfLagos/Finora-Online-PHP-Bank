<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: String,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const sidebarOpen = ref(false);
const dropdownOpen = ref(false);

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: 'home' },
    { name: 'Accounts', href: '/accounts', icon: 'credit-card' },
    { name: 'Transfers', href: '/transfers', icon: 'arrows-right-left' },
    { name: 'Deposits', href: '/deposits', icon: 'banknotes' },
    { name: 'Loans', href: '/loans', icon: 'building-library' },
    { name: 'Cards', href: '/cards', icon: 'credit-card' },
    { name: 'Beneficiaries', href: '/beneficiaries', icon: 'users' },
    { name: 'Support', href: '/support', icon: 'chat-bubble-left-right' },
];
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-gray-50">
        <!-- Mobile sidebar backdrop -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-200 ease-in-out lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-6 border-b border-gray-200">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">F</span>
                    </div>
                    <span class="text-gray-900 text-lg font-semibold">Finora Bank</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-colors group"
                >
                    <span class="text-sm font-medium">{{ item.name }}</span>
                </Link>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-40 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button
                        @click="sidebarOpen = true"
                        class="lg:hidden p-2 text-gray-500 hover:text-gray-700"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page title -->
                    <h1 class="text-xl font-semibold text-gray-900 hidden lg:block">
                        {{ title }}
                    </h1>

                    <!-- User menu -->
                    <div class="relative">
                        <button
                            @click="dropdownOpen = !dropdownOpen"
                            class="flex items-center space-x-3 text-gray-700 hover:text-gray-900"
                        >
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 font-medium text-sm">
                                    {{ user?.name?.charAt(0) || 'U' }}
                                </span>
                            </div>
                            <span class="hidden sm:block text-sm font-medium">
                                {{ user?.name || 'User' }}
                            </span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div
                            v-if="dropdownOpen"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1"
                        >
                            <Link
                                href="/profile"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Profile
                            </Link>
                            <Link
                                href="/settings"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Settings
                            </Link>
                            <hr class="my-1">
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                            >
                                Sign Out
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
