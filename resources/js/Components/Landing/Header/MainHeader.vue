<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);
const isVisible = ref(true);
const lastScrollY = ref(0);

const navLinks = [
    { name: 'Home', href: '/' },
    { name: 'About Us', href: '#about' },
    { name: 'Services', href: '#services' },
    { name: 'Online Banking', href: '/login' },
    { name: 'Cards', href: '#services' },
    { name: 'Loans', href: '#services' },
    { name: 'Contact', href: '#contact' },
];

const handleScroll = () => {
    const currentScrollY = window.scrollY;
    
    // Add background when scrolled
    isScrolled.value = currentScrollY > 50;
    
    // Hide/show header on scroll direction (mobile only)
    if (window.innerWidth < 1024) {
        isVisible.value = currentScrollY < lastScrollY.value || currentScrollY < 100;
    } else {
        isVisible.value = true;
    }
    
    lastScrollY.value = currentScrollY;
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    // Prevent body scroll when menu is open
    document.body.style.overflow = isMobileMenuOpen.value ? 'hidden' : '';
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
    document.body.style.overflow = '';
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.body.style.overflow = '';
});
</script>

<template>
    <!-- Desktop Navigation -->
    <header 
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        :class="[
            isScrolled ? 'bg-white shadow-lg' : 'bg-transparent',
            isVisible ? 'translate-y-0' : '-translate-y-full'
        ]"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div 
                class="flex items-center justify-between transition-all duration-300"
                :class="isScrolled ? 'h-16 lg:h-20' : 'h-20 lg:h-24'"
            >
                <!-- Logo -->
                <Link href="/" class="flex items-center space-x-3 group">
                    <div 
                        class="flex items-center justify-center w-10 h-10 lg:w-12 lg:h-12 rounded-xl transition-all duration-300"
                        :class="isScrolled ? 'bg-primary-600' : 'bg-white/10 backdrop-blur-sm border border-white/20'"
                    >
                        <span 
                            class="text-xl lg:text-2xl font-bold transition-colors duration-300"
                            :class="isScrolled ? 'text-white' : 'text-white'"
                        >F</span>
                    </div>
                    <div class="hidden sm:block">
                        <span 
                            class="text-xl lg:text-2xl font-bold tracking-tight transition-colors duration-300"
                            :class="isScrolled ? 'text-primary-800' : 'text-white'"
                        >Finora</span>
                        <span 
                            class="text-xl lg:text-2xl font-light transition-colors duration-300"
                            :class="isScrolled ? 'text-primary-600' : 'text-white/80'"
                        >Bank</span>
                    </div>
                </Link>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <template v-for="link in navLinks" :key="link.name">
                        <a 
                            v-if="link.href.startsWith('#')"
                            :href="link.href"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200"
                            :class="isScrolled 
                                ? 'text-gray-700 hover:text-primary-600 hover:bg-primary-50' 
                                : 'text-white/90 hover:text-white hover:bg-white/10'"
                        >
                            {{ link.name }}
                        </a>
                        <Link 
                            v-else
                            :href="link.href"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200"
                            :class="isScrolled 
                                ? 'text-gray-700 hover:text-primary-600 hover:bg-primary-50' 
                                : 'text-white/90 hover:text-white hover:bg-white/10'"
                        >
                            {{ link.name }}
                        </Link>
                    </template>
                </nav>

                <!-- Desktop CTA Buttons -->
                <div class="hidden lg:flex items-center space-x-3">
                    <Link 
                        href="/login"
                        class="px-5 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200"
                        :class="isScrolled 
                            ? 'text-primary-600 hover:text-primary-700 hover:bg-primary-50' 
                            : 'text-white hover:bg-white/10'"
                    >
                        Sign In
                    </Link>
                    <Link 
                        href="/register"
                        class="px-5 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        :class="isScrolled 
                            ? 'bg-primary-600 text-white hover:bg-primary-700' 
                            : 'bg-white text-primary-700 hover:bg-white/90'"
                    >
                        Open Account
                    </Link>
                </div>

                <!-- Mobile Menu Button -->
                <button 
                    @click="toggleMobileMenu"
                    class="lg:hidden p-2 rounded-lg transition-colors duration-200"
                    :class="isScrolled ? 'text-gray-700 hover:bg-gray-100' : 'text-white hover:bg-white/10'"
                    :aria-expanded="isMobileMenuOpen"
                    aria-label="Toggle menu"
                >
                    <svg v-if="!isMobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div 
            v-if="isMobileMenuOpen" 
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden"
            @click="closeMobileMenu"
        ></div>
    </Transition>

    <!-- Mobile Menu Panel -->
    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-300 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div 
            v-if="isMobileMenuOpen"
            class="fixed top-0 right-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl z-50 lg:hidden overflow-y-auto"
        >
            <!-- Mobile Menu Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-primary-600 rounded-xl">
                        <span class="text-xl font-bold text-white">F</span>
                    </div>
                    <span class="text-xl font-bold text-primary-800">Finora<span class="font-light text-primary-600">Bank</span></span>
                </div>
                <button 
                    @click="closeMobileMenu"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu Links -->
            <nav class="p-4 space-y-1">
                <template v-for="link in navLinks" :key="link.name">
                    <a 
                        v-if="link.href.startsWith('#')"
                        :href="link.href"
                        @click="closeMobileMenu"
                        class="flex items-center px-4 py-3 text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors duration-200"
                    >
                        {{ link.name }}
                    </a>
                    <Link 
                        v-else
                        :href="link.href"
                        @click="closeMobileMenu"
                        class="flex items-center px-4 py-3 text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors duration-200"
                    >
                        {{ link.name }}
                    </Link>
                </template>
            </nav>

            <!-- Mobile Menu CTA -->
            <div class="p-4 mt-4 space-y-3 border-t border-gray-100">
                <Link 
                    href="/login"
                    @click="closeMobileMenu"
                    class="flex items-center justify-center w-full px-4 py-3 text-primary-600 font-semibold border-2 border-primary-600 rounded-xl hover:bg-primary-50 transition-colors duration-200"
                >
                    Sign In
                </Link>
                <Link 
                    href="/register"
                    @click="closeMobileMenu"
                    class="flex items-center justify-center w-full px-4 py-3 text-white font-semibold bg-primary-600 rounded-xl hover:bg-primary-700 transition-colors duration-200"
                >
                    Open Account
                </Link>
            </div>

            <!-- Mobile Menu Footer Info -->
            <div class="p-4 mt-auto border-t border-gray-100 bg-gray-50">
                <p class="text-xs text-gray-500 text-center">
                    © 2026 Finora Bank. All Rights Reserved.<br>
                    Member FDIC | Equal Housing Lender
                </p>
            </div>
        </div>
    </Transition>
</template>
