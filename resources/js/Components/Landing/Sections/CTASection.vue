<script setup>
import { ref, onMounted } from 'vue';

const isVisible = ref(false);
const sectionRef = ref(null);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    isVisible.value = true;
                }
            });
        },
        { threshold: 0.2 }
    );
    
    if (sectionRef.value) {
        observer.observe(sectionRef.value);
    }
});
</script>

<template>
    <section 
        ref="sectionRef" 
        class="py-16 md:py-24 bg-gradient-to-br from-primary-900 via-primary-800 to-primary-950 relative overflow-hidden"
    >
        <!-- Animated Background -->
        <div class="absolute inset-0">
            <!-- Gradient Orbs -->
            <div class="absolute top-0 left-0 w-96 h-96 bg-gold-500/20 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-primary-400/20 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gold-400/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Pattern Overlay -->
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div 
                class="text-center"
                :class="isVisible ? 'animate-fade-in-up' : 'opacity-0'"
            >
                <!-- Badge -->
                <span class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-wider text-gold-400 bg-gold-400/10 border border-gold-400/30 rounded-full mb-6">
                    <span class="w-2 h-2 bg-gold-400 rounded-full mr-2 animate-pulse"></span>
                    LIMITED TIME OFFER
                </span>

                <!-- Heading -->
                <h2 class="text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-white mb-6 leading-tight">
                    Ready to Experience<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold-300 to-gold-500">Modern Banking?</span>
                </h2>

                <!-- Subtext -->
                <p class="text-lg md:text-xl text-white/70 max-w-2xl mx-auto mb-8">
                    Join over 2 million customers who trust Finora for their financial needs. 
                    Open your free account in minutes and get a <span class="text-gold-400 font-semibold">$100 welcome bonus</span>.
                </p>

                <!-- Benefits -->
                <div class="flex flex-wrap justify-center gap-4 md:gap-6 mb-10">
                    <div class="flex items-center text-white/80 text-sm">
                        <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        No monthly fees
                    </div>
                    <div class="flex items-center text-white/80 text-sm">
                        <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Free debit card
                    </div>
                    <div class="flex items-center text-white/80 text-sm">
                        <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        5 minutes signup
                    </div>
                    <div class="flex items-center text-white/80 text-sm">
                        <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        FDIC insured
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a 
                        href="/register"
                        class="group inline-flex items-center justify-center px-8 py-4 text-base font-bold text-primary-900 bg-gradient-to-r from-gold-400 to-gold-500 rounded-xl hover:from-gold-300 hover:to-gold-400 transition-all duration-300 shadow-lg shadow-gold-500/30 hover:shadow-xl hover:shadow-gold-500/40 hover:scale-105"
                    >
                        Open Free Account
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    <a 
                        href="/login"
                        class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white border-2 border-white/30 rounded-xl hover:bg-white/10 hover:border-white/50 transition-all duration-300"
                    >
                        Sign In to Account
                    </a>
                </div>

                <!-- Trust Line -->
                <p class="mt-8 text-sm text-white/50">
                    Trusted by Fortune 500 companies and individuals worldwide
                </p>

                <!-- Company Logos Placeholder -->
                <div 
                    class="mt-8 flex flex-wrap justify-center items-center gap-8 opacity-40"
                    :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                    style="animation-delay: 0.4s"
                >
                    <div class="text-white font-bold text-xl tracking-wider">FORBES</div>
                    <div class="text-white font-bold text-xl tracking-wider">TechCrunch</div>
                    <div class="text-white font-bold text-xl tracking-wider">WIRED</div>
                    <div class="text-white font-bold text-xl tracking-wider">Bloomberg</div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes pulseSlow {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.3; transform: scale(1.1); }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}

.animate-pulse-slow {
    animation: pulseSlow 4s ease-in-out infinite;
}
</style>
