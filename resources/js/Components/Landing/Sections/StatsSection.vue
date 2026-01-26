<script setup>
import { ref, onMounted, watch } from 'vue';

const isVisible = ref(false);
const sectionRef = ref(null);
const hasAnimated = ref(false);

const stats = [
    { value: 15, suffix: '+', label: 'Years of Excellence', prefix: '' },
    { value: 2, suffix: 'M+', label: 'Happy Customers', prefix: '' },
    { value: 50, suffix: 'B+', label: 'Assets Managed', prefix: '$' },
    { value: 99.9, suffix: '%', label: 'Uptime Guarantee', prefix: '' },
];

const animatedValues = ref(stats.map(() => 0));

const animateCounter = (index, targetValue) => {
    const duration = 2000;
    const steps = 60;
    const stepValue = targetValue / steps;
    const stepDuration = duration / steps;
    let currentStep = 0;

    const interval = setInterval(() => {
        currentStep++;
        animatedValues.value[index] = Math.min(stepValue * currentStep, targetValue);
        
        if (currentStep >= steps) {
            animatedValues.value[index] = targetValue;
            clearInterval(interval);
        }
    }, stepDuration);
};

const formatValue = (value, suffix) => {
    if (suffix === '%') {
        return value.toFixed(1);
    }
    return Math.floor(value);
};

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !hasAnimated.value) {
                    isVisible.value = true;
                    hasAnimated.value = true;
                    
                    // Start counter animations
                    stats.forEach((stat, index) => {
                        setTimeout(() => {
                            animateCounter(index, stat.value);
                        }, index * 200);
                    });
                }
            });
        },
        { threshold: 0.3 }
    );
    
    if (sectionRef.value) {
        observer.observe(sectionRef.value);
    }
});
</script>

<template>
    <section 
        ref="sectionRef" 
        class="py-16 md:py-24 bg-gradient-to-br from-primary-800 via-primary-900 to-primary-950 relative overflow-hidden"
    >
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-gold-400/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-primary-400/10 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <!-- Section Header -->
            <div 
                class="text-center max-w-3xl mx-auto mb-12 md:mb-16"
                :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
            >
                <span class="inline-block px-4 py-1.5 text-xs font-semibold tracking-wider text-gold-400 bg-gold-400/10 border border-gold-400/30 rounded-full mb-4">
                    OUR ACHIEVEMENTS
                </span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                    Numbers That Speak<br>
                    <span class="text-gold-400">Trust & Excellence</span>
                </h2>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                <div 
                    v-for="(stat, index) in stats"
                    :key="stat.label"
                    class="text-center p-6 md:p-8 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10"
                    :class="isVisible ? 'animate-fade-in-up' : 'opacity-0'"
                    :style="{ animationDelay: `${index * 0.1}s` }"
                >
                    <div class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-2">
                        {{ stat.prefix }}{{ formatValue(animatedValues[index], stat.suffix) }}{{ stat.suffix }}
                    </div>
                    <div class="text-sm md:text-base text-white/60">
                        {{ stat.label }}
                    </div>
                </div>
            </div>

            <!-- Trust Badges -->
            <div 
                class="flex flex-wrap justify-center items-center gap-6 md:gap-12 mt-12 md:mt-16 pt-12 border-t border-white/10"
                :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                style="animation-delay: 0.5s"
            >
                <div class="flex items-center space-x-2 text-white/60">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="text-sm font-medium">FDIC Insured</span>
                </div>
                <div class="flex items-center space-x-2 text-white/60">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span class="text-sm font-medium">256-bit Encryption</span>
                </div>
                <div class="flex items-center space-x-2 text-white/60">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <span class="text-sm font-medium">PCI DSS Compliant</span>
                </div>
                <div class="flex items-center space-x-2 text-white/60">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-sm font-medium">Equal Housing Lender</span>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out forwards;
}
</style>
