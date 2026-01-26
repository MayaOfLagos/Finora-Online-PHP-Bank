<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const slides = [
    {
        id: 1,
        tag: 'WELCOME TO FINORA BANK',
        title: 'Banking Made Simple',
        subtitle: 'Experience seamless digital banking with world-class security and personalized service.',
        image: 'https://images.unsplash.com/photo-1560472355-536de3962603?auto=format&fit=crop&w=1920&q=80',
        cta: { primary: 'Open Account', secondary: 'Learn More' }
    },
    {
        id: 2,
        tag: 'SECURE ONLINE BANKING',
        title: 'Your Money, Your Control',
        subtitle: 'Access your accounts 24/7 from anywhere in the world with bank-grade security.',
        image: 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=1920&q=80',
        cta: { primary: 'Get Started', secondary: 'View Features' }
    },
    {
        id: 3,
        tag: 'PERSONAL & BUSINESS LOANS',
        title: 'Achieve Your Dreams',
        subtitle: 'Competitive rates on personal, auto, and business loans tailored to your needs.',
        image: 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=1920&q=80',
        cta: { primary: 'Apply Now', secondary: 'Check Rates' }
    },
    {
        id: 4,
        tag: 'PREMIUM BANKING CARDS',
        title: 'Rewards That Matter',
        subtitle: 'Earn cashback, travel points, and exclusive benefits with our premium card selection.',
        image: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1920&q=80',
        cta: { primary: 'Explore Cards', secondary: 'Compare Benefits' }
    },
    {
        id: 5,
        tag: '24/7 CUSTOMER SUPPORT',
        title: 'Always Here for You',
        subtitle: 'Our dedicated team is available around the clock to assist with all your banking needs.',
        image: 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1920&q=80',
        cta: { primary: 'Contact Us', secondary: 'Help Center' }
    }
];

const currentSlide = ref(0);
const isAutoPlaying = ref(true);
const isPaused = ref(false);
let autoPlayInterval = null;

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % slides.length;
};

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length;
};

const goToSlide = (index) => {
    currentSlide.value = index;
    resetAutoPlay();
};

const startAutoPlay = () => {
    if (autoPlayInterval) clearInterval(autoPlayInterval);
    autoPlayInterval = setInterval(() => {
        if (!isPaused.value) {
            nextSlide();
        }
    }, 6000);
};

const resetAutoPlay = () => {
    if (autoPlayInterval) clearInterval(autoPlayInterval);
    startAutoPlay();
};

const pauseAutoPlay = () => {
    isPaused.value = true;
};

const resumeAutoPlay = () => {
    isPaused.value = false;
};

onMounted(() => {
    startAutoPlay();
});

onUnmounted(() => {
    if (autoPlayInterval) clearInterval(autoPlayInterval);
});
</script>

<template>
    <section 
        class="relative h-screen min-h-[600px] max-h-[900px] lg:max-h-none overflow-hidden"
        @mouseenter="pauseAutoPlay"
        @mouseleave="resumeAutoPlay"
    >
        <!-- Slides -->
        <div class="absolute inset-0">
            <TransitionGroup
                enter-active-class="transition-all duration-1000 ease-out"
                enter-from-class="opacity-0 scale-105"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition-all duration-1000 ease-in absolute inset-0"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div 
                    v-for="(slide, index) in slides" 
                    :key="slide.id"
                    v-show="currentSlide === index"
                    class="absolute inset-0"
                >
                    <!-- Background Image -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center transition-transform duration-[10000ms] ease-linear"
                        :style="{ 
                            backgroundImage: `url(${slide.image})`,
                            transform: currentSlide === index ? 'scale(1.1)' : 'scale(1)'
                        }"
                    ></div>
                    
                    <!-- Overlay Gradient -->
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-900/90 via-primary-900/70 to-primary-900/40"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary-900/80 via-transparent to-primary-900/30"></div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Content -->
        <div class="relative h-full flex items-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="max-w-2xl lg:max-w-3xl pt-20 lg:pt-0">
                    <TransitionGroup
                        enter-active-class="transition-all duration-700 ease-out"
                        enter-from-class="opacity-0 translate-y-8"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-300 ease-in absolute"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-4"
                    >
                        <div v-for="(slide, index) in slides" :key="slide.id" v-show="currentSlide === index">
                            <!-- Tag -->
                            <span class="inline-block px-4 py-1.5 text-xs md:text-sm font-semibold tracking-wider text-gold-400 bg-gold-400/10 border border-gold-400/30 rounded-full mb-4 md:mb-6">
                                {{ slide.tag }}
                            </span>

                            <!-- Title -->
                            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold text-white leading-tight mb-4 md:mb-6">
                                {{ slide.title }}
                            </h1>

                            <!-- Subtitle -->
                            <p class="text-base md:text-lg lg:text-xl text-white/80 leading-relaxed mb-6 md:mb-8 max-w-xl">
                                {{ slide.subtitle }}
                            </p>

                            <!-- CTA Buttons -->
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                                <Link 
                                    href="/register"
                                    class="inline-flex items-center justify-center px-6 md:px-8 py-3 md:py-4 text-sm md:text-base font-semibold text-primary-900 bg-white rounded-xl hover:bg-gray-100 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl"
                                >
                                    {{ slide.cta.primary }}
                                    <svg class="ml-2 w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </Link>
                                <a 
                                    href="#about"
                                    class="inline-flex items-center justify-center px-6 md:px-8 py-3 md:py-4 text-sm md:text-base font-semibold text-white bg-white/10 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/20 transition-all duration-200"
                                >
                                    {{ slide.cta.secondary }}
                                </a>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>
            </div>
        </div>

        <!-- Navigation Arrows (Desktop) -->
        <div class="hidden lg:flex absolute inset-y-0 left-4 xl:left-8 items-center">
            <button 
                @click="prevSlide"
                class="p-3 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full transition-all duration-200 group"
                aria-label="Previous slide"
            >
                <svg class="w-6 h-6 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>
        <div class="hidden lg:flex absolute inset-y-0 right-4 xl:right-8 items-center">
            <button 
                @click="nextSlide"
                class="p-3 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full transition-all duration-200 group"
                aria-label="Next slide"
            >
                <svg class="w-6 h-6 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Slide Indicators -->
        <div class="absolute bottom-8 md:bottom-12 left-1/2 -translate-x-1/2 flex items-center space-x-2 md:space-x-3">
            <button 
                v-for="(slide, index) in slides" 
                :key="slide.id"
                @click="goToSlide(index)"
                class="group relative p-1"
                :aria-label="`Go to slide ${index + 1}`"
                :aria-current="currentSlide === index ? 'true' : 'false'"
            >
                <span 
                    class="block w-2 h-2 md:w-3 md:h-3 rounded-full transition-all duration-300"
                    :class="currentSlide === index 
                        ? 'bg-white scale-100' 
                        : 'bg-white/40 scale-75 group-hover:bg-white/60 group-hover:scale-90'"
                ></span>
                <!-- Progress indicator for current slide -->
                <svg 
                    v-if="currentSlide === index"
                    class="absolute -inset-1 w-4 h-4 md:w-5 md:h-5 -rotate-90"
                    viewBox="0 0 20 20"
                >
                    <circle 
                        cx="10" cy="10" r="8" 
                        fill="none" 
                        stroke="currentColor" 
                        stroke-width="1.5"
                        class="text-white/30"
                    />
                    <circle 
                        cx="10" cy="10" r="8" 
                        fill="none" 
                        stroke="currentColor" 
                        stroke-width="1.5"
                        stroke-dasharray="50.27"
                        class="text-white animate-progress"
                    />
                </svg>
            </button>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 hidden md:flex flex-col items-center text-white/60 animate-bounce">
            <span class="text-xs tracking-wider mb-2">SCROLL</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </div>
    </section>
</template>

<style scoped>
@keyframes progress {
    from {
        stroke-dashoffset: 50.27;
    }
    to {
        stroke-dashoffset: 0;
    }
}

.animate-progress {
    animation: progress 6s linear;
}
</style>
