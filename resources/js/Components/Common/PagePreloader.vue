<script setup>
/**
 * PagePreloader - Ultra Modern Loading Animation
 * A sleek, professional preloader for banking applications
 * Features: Animated logo, progress indicator, smooth transitions
 */
import { ref, onMounted, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    /**
     * Minimum loading time in milliseconds
     * Ensures smooth UX even on fast connections
     */
    minLoadTime: {
        type: Number,
        default: 1500,
    },
    /**
     * Show loading text below animation
     */
    showText: {
        type: Boolean,
        default: true,
    },
    /**
     * Variant: 'full' for page load, 'overlay' for route changes
     */
    variant: {
        type: String,
        default: 'full',
        validator: (value) => ['full', 'overlay'].includes(value),
    },
});

const emit = defineEmits(['complete']);

const page = usePage();
const isVisible = ref(true);
const progress = ref(0);
const isExiting = ref(false);

// Get branding from settings
const favicon = computed(() => page.props.settings?.branding?.favicon);
const siteName = computed(() => page.props.settings?.general?.site_name || 'Finora Bank');
const siteInitial = computed(() => siteName.value.charAt(0).toUpperCase());

// Progress animation
let progressInterval = null;

const startProgress = () => {
    progress.value = 0;
    progressInterval = setInterval(() => {
        if (progress.value < 90) {
            // Slow down as it approaches 90%
            const increment = Math.random() * (90 - progress.value) / 10;
            progress.value = Math.min(90, progress.value + increment);
        }
    }, 100);
};

const completeProgress = () => {
    if (progressInterval) {
        clearInterval(progressInterval);
        progressInterval = null;
    }
    progress.value = 100;
};

const hidePreloader = () => {
    isExiting.value = true;
    setTimeout(() => {
        isVisible.value = false;
        emit('complete');
    }, 500);
};

onMounted(() => {
    const startTime = Date.now();
    startProgress();

    const checkReady = () => {
        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, props.minLoadTime - elapsed);
        
        setTimeout(() => {
            completeProgress();
            setTimeout(hidePreloader, 300);
        }, remaining);
    };

    if (document.readyState === 'complete') {
        checkReady();
    } else {
        window.addEventListener('load', checkReady, { once: true });
    }

    // Cleanup
    return () => {
        if (progressInterval) {
            clearInterval(progressInterval);
        }
    };
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            leave-active-class="transition-all duration-500 ease-out"
            leave-to-class="opacity-0 scale-105"
        >
            <div
                v-if="isVisible"
                class="fixed inset-0 z-[99999] flex flex-col items-center justify-center overflow-hidden"
                :class="[
                    variant === 'full' 
                        ? 'bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900' 
                        : 'bg-slate-900/95 backdrop-blur-sm'
                ]"
            >
                <!-- Animated Background Elements -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <!-- Gradient Orbs -->
                    <div class="absolute top-1/4 -left-20 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl animate-float"></div>
                    <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl animate-float-delayed"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-3xl animate-pulse-slow"></div>
                    
                    <!-- Grid Pattern -->
                    <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
                    
                    <!-- Particle Effect -->
                    <div class="particles">
                        <div v-for="i in 20" :key="i" class="particle" :style="{ '--delay': `${i * 0.2}s`, '--x': `${Math.random() * 100}%`, '--duration': `${3 + Math.random() * 4}s` }"></div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="relative z-10 flex flex-col items-center">
                    <!-- Logo Animation Container -->
                    <div class="relative mb-8">
                        <!-- Outer Rotating Ring -->
                        <div class="absolute -inset-8 rounded-full border-2 border-dashed border-indigo-500/30 animate-spin-slow"></div>
                        
                        <!-- Middle Pulsing Ring -->
                        <div class="absolute -inset-4 rounded-full border border-indigo-400/40 animate-ping-slow"></div>
                        
                        <!-- Glow Effect -->
                        <div class="absolute -inset-2 rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500 opacity-50 blur-xl animate-pulse"></div>
                        
                        <!-- Logo Container -->
                        <div class="relative w-24 h-24 md:w-28 md:h-28 rounded-full bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 shadow-2xl flex items-center justify-center overflow-hidden group">
                            <!-- Inner Shine -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                            
                            <!-- Logo/Favicon -->
                            <img 
                                v-if="favicon"
                                :src="favicon"
                                :alt="siteName"
                                class="w-14 h-14 md:w-16 md:h-16 object-contain drop-shadow-2xl animate-float-subtle"
                            />
                            <!-- Fallback Initial -->
                            <div v-else class="flex flex-col items-center justify-center animate-float-subtle">
                                <span class="text-4xl md:text-5xl font-bold bg-gradient-to-br from-white to-indigo-200 bg-clip-text text-transparent">
                                    {{ siteInitial }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Site Name -->
                    <h1 class="text-xl md:text-2xl font-semibold text-white/90 tracking-wide mb-6 animate-fade-in-up" style="animation-delay: 0.2s;">
                        {{ siteName }}
                    </h1>

                    <!-- Progress Bar -->
                    <div class="relative w-56 md:w-72 h-1.5 bg-white/10 rounded-full overflow-hidden backdrop-blur-sm">
                        <!-- Progress Fill -->
                        <div 
                            class="absolute inset-y-0 left-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-400 rounded-full transition-all duration-300 ease-out"
                            :style="{ width: `${progress}%` }"
                        >
                            <!-- Shimmer Effect -->
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer"></div>
                        </div>
                        
                        <!-- Glowing Dot at Progress End -->
                        <div 
                            class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-white rounded-full shadow-lg shadow-indigo-500/50 transition-all duration-300"
                            :style="{ left: `calc(${progress}% - 6px)` }"
                        >
                            <div class="absolute inset-0 rounded-full bg-white animate-ping opacity-75"></div>
                        </div>
                    </div>

                    <!-- Loading Text -->
                    <div v-if="showText" class="mt-6 flex items-center gap-2 text-sm text-white/50 animate-fade-in-up" style="animation-delay: 0.4s;">
                        <span class="inline-flex gap-1">
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.15s;"></span>
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.3s;"></span>
                        </span>
                        <span>Loading your experience</span>
                    </div>

                    <!-- Percentage (optional) -->
                    <p class="mt-2 text-xs text-white/30 font-mono tabular-nums">
                        {{ Math.round(progress) }}%
                    </p>
                </div>

                <!-- Bottom Branding -->
                <div class="absolute bottom-8 left-0 right-0 text-center">
                    <p class="text-xs text-white/20">
                        Secure Banking Platform
                    </p>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Spin Animations */
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin-slow {
    animation: spin-slow 15s linear infinite;
}

/* Ping Animation */
@keyframes ping-slow {
    0% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.3; }
    100% { transform: scale(1); opacity: 0.5; }
}

.animate-ping-slow {
    animation: ping-slow 2s ease-in-out infinite;
}

/* Float Animations */
@keyframes float {
    0%, 100% { transform: translateY(0) translateX(0); }
    25% { transform: translateY(-20px) translateX(10px); }
    50% { transform: translateY(0) translateX(20px); }
    75% { transform: translateY(20px) translateX(10px); }
}

.animate-float {
    animation: float 8s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float 8s ease-in-out infinite;
    animation-delay: -4s;
}

@keyframes float-subtle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.animate-float-subtle {
    animation: float-subtle 3s ease-in-out infinite;
}

/* Pulse Slow */
@keyframes pulse-slow {
    0%, 100% { opacity: 0.1; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 0.2; transform: translate(-50%, -50%) scale(1.1); }
}

.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}

/* Shimmer Effect */
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.animate-shimmer {
    animation: shimmer 2s ease-in-out infinite;
}

/* Fade In Up */
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
}

/* Particles */
.particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    left: var(--x);
    bottom: -10px;
    animation: particle-rise var(--duration) ease-in infinite;
    animation-delay: var(--delay);
}

@keyframes particle-rise {
    0% {
        transform: translateY(0) scale(1);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateY(-100vh) scale(0.5);
        opacity: 0;
    }
}

/* Bounce for loading dots */
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
</style>
