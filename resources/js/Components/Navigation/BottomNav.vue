<script setup>
/**
 * BottomNav Component
 * Mobile bottom navigation bar with glassmorphism effect and speed dial FAB
 */
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const speedDialOpen = ref(false);

const currentPath = computed(() => page.url);

const isActive = (href) => {
    if (href === '/dashboard') {
        return currentPath.value === '/dashboard' || currentPath.value === '/';
    }
    return currentPath.value.startsWith(href);
};

const navItems = [
    { name: 'Home', href: '/dashboard', icon: 'pi pi-home' },
    { name: 'Cards', href: '/cards', icon: 'pi pi-credit-card' },
    // Center space for FAB
    { name: 'Activity', href: '/transactions', icon: 'pi pi-history' },
    { name: 'More', href: null, icon: 'pi pi-th-large', action: 'menu' },
];

const transferOptions = [
    { 
        name: 'Wire Transfer', 
        href: '/transfers/wire', 
        icon: 'pi pi-globe',
        color: 'from-purple-500 to-indigo-600',
        description: 'International'
    },
    { 
        name: 'Domestic', 
        href: '/transfers/domestic', 
        icon: 'pi pi-building',
        color: 'from-blue-500 to-cyan-600',
        description: 'Local banks'
    },
    { 
        name: 'Internal', 
        href: '/transfers/internal', 
        icon: 'pi pi-users',
        color: 'from-emerald-500 to-teal-600',
        description: 'Finora users'
    },
    { 
        name: 'My Accounts', 
        href: '/transfers/account',
        icon: 'pi pi-sync',
        color: 'from-amber-500 to-orange-600',
        description: 'Between accounts'
    },
];

const toggleSpeedDial = () => {
    speedDialOpen.value = !speedDialOpen.value;
};

const closeSpeedDial = () => {
    speedDialOpen.value = false;
};

const navigateToTransfer = (href) => {
    closeSpeedDial();
    router.visit(href);
};

const handleNavClick = (item) => {
    if (item.action === 'menu') {
        emit('openMenu');
    }
};

const emit = defineEmits(['openMenu']);
</script>

<template>
    <!-- Backdrop overlay when speed dial is open -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div 
            v-if="speedDialOpen" 
            class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
            @click="closeSpeedDial"
        />
    </Transition>

    <!-- Speed Dial Panel -->
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-8 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-8 scale-95"
    >
        <div 
            v-if="speedDialOpen"
            class="fixed bottom-28 left-1/2 -translate-x-1/2 z-50 lg:hidden"
        >
            <div class="glass-panel p-4 rounded-3xl shadow-2xl">
                <div class="text-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Quick Transfer</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Select transfer type</p>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <button
                        v-for="option in transferOptions"
                        :key="option.name"
                        @click="navigateToTransfer(option.href)"
                        class="speed-dial-item group"
                    >
                        <div :class="['speed-dial-icon bg-gradient-to-br', option.color]">
                            <i :class="[option.icon, 'text-lg text-white']"></i>
                        </div>
                        <div class="text-left">
                            <span class="text-xs font-semibold text-gray-800 dark:text-white block">{{ option.name }}</span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ option.description }}</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-4 left-4 right-4 z-50 lg:hidden safe-area-bottom">
        <div class="glass-nav rounded-2xl shadow-xl">
            <div class="flex items-center justify-around h-16 px-2 relative">
                <!-- Left Nav Items -->
                <template v-for="(item, index) in navItems" :key="item.name">
                    <!-- Spacer for FAB -->
                    <div v-if="index === 2" class="w-16"></div>
                    
                    <Link
                        v-if="item.href && item.action !== 'menu'"
                        :href="item.href"
                        :class="[
                            'nav-item',
                            isActive(item.href) ? 'nav-item-active' : 'nav-item-inactive'
                        ]"
                    >
                        <div :class="['nav-icon-wrapper', isActive(item.href) ? 'nav-icon-active' : '']">
                            <i :class="[item.icon, 'text-lg']"></i>
                        </div>
                        <span class="nav-label">{{ item.name }}</span>
                    </Link>

                    <button
                        v-else-if="item.action === 'menu'"
                        @click="$emit('openMenu')"
                        class="nav-item nav-item-inactive"
                    >
                        <div class="nav-icon-wrapper">
                            <i :class="[item.icon, 'text-lg']"></i>
                        </div>
                        <span class="nav-label">{{ item.name }}</span>
                    </button>
                </template>

                <!-- Center FAB Button -->
                <button
                    @click="toggleSpeedDial"
                    :class="[
                        'fab-button',
                        speedDialOpen ? 'fab-button-active' : 'fab-button-inactive'
                    ]"
                >
                    <div class="fab-inner">
                        <i :class="['pi', speedDialOpen ? 'pi-times' : 'pi-arrow-right-arrow-left', 'text-xl transition-transform duration-300', speedDialOpen ? 'rotate-90' : '']"></i>
                    </div>
                    <div class="fab-ring"></div>
                    <div class="fab-glow"></div>
                </button>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom, 0);
}

/* Glassmorphism Navigation */
.glass-nav {
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.1),
        0 2px 8px rgba(0, 0, 0, 0.05),
        inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.dark .glass-nav {
    background: rgba(17, 24, 39, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* Glass Panel for Speed Dial */
.glass-panel {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(24px) saturate(200%);
    -webkit-backdrop-filter: blur(24px) saturate(200%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.25),
        0 12px 24px -8px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    min-width: 280px;
}

.dark .glass-panel {
    background: rgba(17, 24, 39, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.5),
        0 12px 24px -8px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* Nav Items */
.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    height: 100%;
    gap: 2px;
    transition: all 0.2s ease;
    text-decoration: none;
    position: relative;
}

.nav-item-active {
    color: #6366f1;
}

.nav-item-inactive {
    color: #6b7280;
}

.dark .nav-item-inactive {
    color: #9ca3af;
}

.nav-item-inactive:hover {
    color: #374151;
}

.dark .nav-item-inactive:hover {
    color: #e5e7eb;
}

.nav-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 28px;
    border-radius: 14px;
    transition: all 0.2s ease;
}

.nav-icon-active {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
}

.dark .nav-icon-active {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3));
}

.nav-label {
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 0.01em;
}

/* FAB Button */
.fab-button {
    position: absolute;
    left: 50%;
    top: -24px;
    transform: translateX(-50%);
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    outline: none;
    z-index: 10;
}

.fab-inner {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
    color: white;
    box-shadow: 
        0 4px 15px rgba(99, 102, 241, 0.4),
        0 2px 6px rgba(99, 102, 241, 0.3);
    transition: all 0.3s ease;
}

.fab-button:hover .fab-inner {
    transform: scale(1.05);
    box-shadow: 
        0 6px 20px rgba(99, 102, 241, 0.5),
        0 3px 10px rgba(99, 102, 241, 0.4);
}

.fab-button:active .fab-inner {
    transform: scale(0.95);
}

.fab-button-active .fab-inner {
    background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
    box-shadow: 
        0 4px 15px rgba(239, 68, 68, 0.4),
        0 2px 6px rgba(239, 68, 68, 0.3);
}

.fab-ring {
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(168, 85, 247, 0.3));
    z-index: 1;
    opacity: 0;
    transition: all 0.3s ease;
}

.fab-button:hover .fab-ring {
    opacity: 1;
    inset: -5px;
}

.fab-glow {
    position: absolute;
    inset: -8px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
    z-index: 0;
    animation: pulse-glow 2s ease-in-out infinite;
}

.fab-button-active .fab-glow {
    background: radial-gradient(circle, rgba(239, 68, 68, 0.2) 0%, transparent 70%);
}

@keyframes pulse-glow {
    0%, 100% {
        transform: scale(1);
        opacity: 0.5;
    }
    50% {
        transform: scale(1.15);
        opacity: 0.8;
    }
}

/* Speed Dial Items */
.speed-dial-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.2s ease;
    cursor: pointer;
}

.dark .speed-dial-item {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.speed-dial-item:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dark .speed-dial-item:hover {
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.speed-dial-item:active {
    transform: translateY(0);
}

.speed-dial-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Liquid morphing animation for the glass effect */
@supports (backdrop-filter: blur(20px)) {
    .glass-nav::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.1) 0%,
            transparent 50%,
            rgba(255, 255, 255, 0.05) 100%
        );
        pointer-events: none;
    }
}
</style>
