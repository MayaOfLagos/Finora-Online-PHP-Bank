<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const isLoading = ref(true);
const progress = ref(0);
const page = usePage();

// Get favicon from settings, or use fallback
const favicon = computed(() => {
    return page.props.settings?.branding?.favicon || '/favicon.ico';
});

onMounted(() => {
    // Animate progress bar
    const interval = setInterval(() => {
        progress.value += Math.random() * 15;
        if (progress.value >= 100) {
            progress.value = 100;
            clearInterval(interval);
            setTimeout(() => {
                isLoading.value = false;
            }, 300);
        }
    }, 100);

    // Fallback timeout
    setTimeout(() => {
        isLoading.value = false;
    }, 3000);
});
</script>

<template>
    <Transition
        leave-active-class="transition-opacity duration-500"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isLoading"
            class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-gradient-to-br from-primary-900 via-primary-800 to-primary-900"
        >
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>

            <!-- Logo Container -->
            <div class="relative mb-4">
                <!-- Animated Ring -->
                <div class="absolute -inset-4 rounded-full border-2 border-gold-400/30 animate-ping"></div>
                <div class="absolute -inset-8 rounded-full border border-gold-400/20 animate-pulse"></div>
                
                <!-- Logo -->
                <div class="relative flex items-center justify-center w-24 h-24 md:w-32 md:h-32 bg-white/10 backdrop-blur-sm rounded-full border border-white/20 shadow-2xl">
                    <!-- Favicon from Settings -->
                    <img 
                        v-if="favicon"
                        :src="favicon"
                        alt="Finora Logo"
                        class="w-16 h-16 md:w-20 md:h-20 object-contain"
                    />
                    <!-- Fallback Logo (shown if no favicon) -->
                    <div v-else class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-white tracking-tight">F</div>
                        <div class="text-[8px] md:text-[10px] font-medium text-gold-400 tracking-[0.2em] uppercase">Finora</div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-48 md:w-64 h-1 bg-white/10 rounded-full overflow-hidden">
                <div 
                    class="h-full bg-gradient-to-r from-gold-400 to-gold-500 rounded-full transition-all duration-300 ease-out"
                    :style="{ width: `${progress}%` }"
                ></div>
            </div>

            <!-- Loading Text -->
            <p class="mt-4 text-xs text-white/40 animate-pulse">
                Loading...
            </p>
        </div>
    </Transition>
</template>

<style scoped>
.animate-ping {
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes ping {
    75%, 100% {
        transform: scale(1.5);
        opacity: 0;
    }
}
</style>
