<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import AppLogo from '@/Components/Common/AppLogo.vue';
import CopyrightText from '@/Components/Common/CopyrightText.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const toast = useToast();

// Get settings from page props with fallbacks
const siteName = computed(() => page.props.settings?.general?.site_name || 'Finora Bank');
const logoLight = computed(() => page.props.settings?.branding?.logo_light || null);
const logoDark = computed(() => page.props.settings?.branding?.logo_dark || null);

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);
const isVisible = ref(true);
const lastScrollY = ref(0);

const navLinks = [
    { name: 'Home', href: '/' },
    { name: 'About Us', href: '/about' },
    { name: 'Services', href: '/services' },
    { name: 'Online Banking', href: '/login' },
    { name: 'Cards', href: '/credit-cards' },
    { name: 'Loans', href: '/loans-and-mortgages' },
    { name: 'Contact', href: '/contact' },
];

const handleScroll = () => {
    const currentScrollY = window.scrollY;
    isScrolled.value = currentScrollY > 50;
    
    if (window.innerWidth < 1024) {
        isVisible.value = currentScrollY < lastScrollY.value || currentScrollY < 100;
    } else {
        isVisible.value = true;
    }
    
    lastScrollY.value = currentScrollY;
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    document.body.style.overflow = isMobileMenuOpen.value ? 'hidden' : '';
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
    document.body.style.overflow = '';
};

const handleLogout = () => {
    toast.info('You are being logged out. Goodbye!', 'Logging Out');
    
    setTimeout(() => {
        router.post(route('logout'));
    }, 500);
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
    <!-- Morphic Glass Header -->
    <header 
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 px-3 sm:px-4 lg:px-6"
        :class="isVisible ? 'translate-y-0' : '-translate-y-full'"
    >
        <!-- Glass Container -->
        <div 
            class="max-w-7xl mx-auto mt-3 sm:mt-4 transition-all duration-500 rounded-2xl lg:rounded-3xl overflow-hidden"
            :class="[
                isScrolled 
                    ? 'glass-header-scrolled shadow-glass-scrolled' 
                    : 'glass-header shadow-glass'
            ]"
        >
            <div 
                class="flex items-center justify-between px-4 sm:px-6 lg:px-8 transition-all duration-300"
                :class="isScrolled ? 'h-14 lg:h-16' : 'h-16 lg:h-20'"
            >
                <!-- Logo -->
                <Link href="/" class="flex items-center space-x-3 group">
                    <AppLogo size="sm" />
                </Link>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <Link 
                        v-for="link in navLinks" 
                        :key="link.name"
                        :href="link.href"
                        class="px-4 py-2 text-sm font-medium rounded-xl transition-all duration-200"
                        :class="isScrolled 
                            ? 'text-gray-700 hover:text-primary-600 hover:bg-primary-50/80' 
                            : 'text-gray-700 hover:text-primary-600 hover:bg-primary-50/80'"
                    >
                        {{ link.name }}
                    </Link>
                </nav>

                <!-- Desktop CTA Buttons -->
                <div class="hidden lg:flex items-center space-x-3">
                    <template v-if="!user">
                        <Link 
                            href="/login"
                            class="px-5 py-2 text-sm font-semibold rounded-xl transition-all duration-200"
                            :class="isScrolled 
                                ? 'text-primary-600 hover:bg-primary-50/80' 
                                : 'text-primary-600 hover:bg-primary-50/80'"
                        >
                            Sign In
                        </Link>
                        <Link 
                            href="/register"
                            class="px-5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5"
                            :class="isScrolled 
                                ? 'bg-primary-600 text-white hover:bg-primary-700 shadow-lg shadow-primary-600/30 hover:shadow-xl hover:shadow-primary-600/40' 
                                : 'bg-primary-600 text-white hover:bg-primary-700 shadow-lg shadow-primary-600/30 hover:shadow-xl hover:shadow-primary-600/40'"
                        >
                            Open Account
                        </Link>
                    </template>
                    <template v-else>
                        <Link 
                            href="/dashboard"
                            class="px-5 py-2 text-sm font-semibold rounded-xl transition-all duration-200"
                            :class="isScrolled 
                                ? 'text-primary-600 hover:bg-primary-50/80' 
                                : 'text-primary-600 hover:bg-primary-50/80'"
                        >
                            Dashboard
                        </Link>
                        <button
                            @click="handleLogout"
                            class="px-5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5"
                            :class="isScrolled 
                                ? 'bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-600/30 hover:shadow-xl hover:shadow-red-600/40' 
                                : 'bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-600/30 hover:shadow-xl hover:shadow-red-600/40'"
                        >
                            Logout
                        </button>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <button 
                    @click="toggleMobileMenu"
                    class="lg:hidden p-2 rounded-xl transition-all duration-200"
                    :class="isScrolled 
                        ? 'text-gray-700 hover:bg-gray-100/80' 
                        : 'text-gray-700 hover:bg-gray-100/80'"
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
            class="fixed inset-0 bg-black/60 backdrop-blur-md z-[60] lg:hidden"
            @click="closeMobileMenu"
        ></div>
    </Transition>

    <!-- Mobile Menu Panel - Offcanvas Detached -->
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
            class="fixed top-4 right-4 bottom-4 w-80 max-w-[85vw] bg-white/98 backdrop-blur-2xl shadow-2xl rounded-3xl z-[70] lg:hidden overflow-hidden flex flex-col"
        >
            <!-- Mobile Menu Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-100/50">
                <div class="flex items-center space-x-3">
                    <!-- Logo from settings (light mode) -->
                    <img 
                        v-if="logoLight"
                        :src="logoLight"
                        :alt="siteName"
                        class="h-10 w-auto object-contain"
                    />
                    <!-- Fallback logo if no logo from settings -->
                    <template v-else>
                        <div class="flex items-center justify-center w-10 h-10 bg-primary-600 rounded-xl shadow-lg shadow-primary-600/30">
                            <span class="text-xl font-bold text-white">F</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Finora<span class="font-light text-primary-600">Bank</span></span>
                    </template>
                </div>
                <button 
                    @click="closeMobileMenu"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100/80 rounded-xl"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu Content - Scrollable -->
            <div class="flex-1 overflow-y-auto">
                <!-- Mobile Menu Links -->
                <nav class="p-4 space-y-1">
                    <Link 
                        v-for="link in navLinks" 
                        :key="link.name"
                        :href="link.href"
                        @click="closeMobileMenu"
                        class="flex items-center px-4 py-3 text-gray-700 hover:text-primary-600 hover:bg-primary-50/80 rounded-xl transition-colors duration-200"
                    >
                        {{ link.name }}
                    </Link>
                </nav>

                <!-- Mobile Menu CTA -->
                <div class="p-4 mt-4 space-y-3 border-t border-gray-100/50">
                    <template v-if="!user">
                        <Link 
                            href="/login"
                            @click="closeMobileMenu"
                            class="flex items-center justify-center w-full px-4 py-3 text-primary-600 font-semibold border-2 border-primary-200 rounded-xl hover:bg-primary-50 transition-colors duration-200"
                        >
                            Sign In
                        </Link>
                        <Link 
                            href="/register"
                            @click="closeMobileMenu"
                            class="flex items-center justify-center w-full px-4 py-3 text-white font-semibold bg-primary-600 rounded-xl hover:bg-primary-700 transition-colors duration-200 shadow-lg shadow-primary-600/30"
                        >
                            Open Account
                        </Link>
                    </template>
                    <template v-else>
                        <Link 
                            href="/dashboard"
                            @click="closeMobileMenu"
                            class="flex items-center justify-center w-full px-4 py-3 text-primary-600 font-semibold border-2 border-primary-200 rounded-xl hover:bg-primary-50 transition-colors duration-200"
                        >
                            Dashboard
                        </Link>
                        <button
                            @click="handleLogout"
                            class="flex items-center justify-center w-full px-4 py-3 text-white font-semibold bg-red-600 rounded-xl hover:bg-red-700 transition-colors duration-200 shadow-lg shadow-red-600/30"
                        >
                            Logout
                        </button>
                    </template>
                </div>
            </div>

            <!-- Mobile Menu Footer Info -->
            <div class="p-4 border-t border-gray-100/50 bg-gray-50/50">
                <div class="text-center">
                    <CopyrightText :show-extra-text="true" />
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* Morphic Liquid Glass Effect - Not Scrolled */
.glass-header {
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.85) 0%,
        rgba(255, 255, 255, 0.75) 50%,
        rgba(255, 255, 255, 0.85) 100%
    );
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(0, 86, 179, 0.15);
    box-shadow: 
        0 4px 30px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.5),
        inset 0 -1px 0 rgba(0, 0, 0, 0.05);
}

.shadow-glass {
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.12),
        0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Morphic Glass Effect - Scrolled (Light) */
.glass-header-scrolled {
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.95) 0%,
        rgba(255, 255, 255, 0.9) 50%,
        rgba(255, 255, 255, 0.95) 100%
    );
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 
        0 4px 30px rgba(0, 0, 0, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.8),
        inset 0 -1px 0 rgba(0, 0, 0, 0.02);
}

.shadow-glass-scrolled {
    box-shadow: 
        0 8px 32px rgba(0, 86, 179, 0.08),
        0 2px 16px rgba(0, 0, 0, 0.06),
        0 1px 4px rgba(0, 0, 0, 0.04);
}
</style>
