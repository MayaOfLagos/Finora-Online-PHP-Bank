<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
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
    <!-- Hero Section with Container -->
    <section class="relative bg-primary-950 pt-22 sm:pt-30 pb-6 px-3 sm:px-4 lg:px-6">
        <!-- Hero Container with Border Radius -->
        <div 
            class="relative max-w-7xl mx-auto h-[85vh] min-h-[550px] max-h-[800px] rounded-2xl lg:rounded-3xl overflow-hidden shadow-2xl"
            @mouseenter="pauseAutoPlay"
            @mouseleave="resumeAutoPlay"
        >
            <!-- Slides -->
            <div class="absolute inset-0">
                <div 
                    v-for="(slide, index) in slides" 
                    :key="slide.id"
                    class="absolute inset-0 transition-opacity duration-700 ease-in-out"
                    :class="currentSlide === index ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                >
                    <!-- Background Image -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center"
                        :style="{ backgroundImage: `url(${slide.image})` }"
                    ></div>
                    
                    <!-- Overlay Gradient -->
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-900/90 via-primary-900/70 to-primary-900/40"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary-900/80 via-transparent to-primary-900/30"></div>
                </div>
            </div>

            <!-- Content Layout with Side Navigation -->
            <div class="relative h-full flex items-center z-20">
                <!-- Left Navigation Arrow (Desktop) -->
                <div class="hidden lg:flex flex-shrink-0 w-16 xl:w-20 h-full items-center justify-center">
                    <button 
                        @click="prevSlide"
                        class="p-3 xl:p-4 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full transition-all duration-200 group"
                        aria-label="Previous slide"
                    >
                        <svg class="w-5 h-5 xl:w-6 xl:h-6 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 px-6 sm:px-8 lg:px-12 xl:px-16">
                    <div class="max-w-2xl pt-16 lg:pt-0">
                        <div 
                            v-for="(slide, index) in slides" 
                            :key="slide.id"
                            class="transition-opacity duration-500 ease-in-out"
                            :class="currentSlide === index ? 'opacity-100' : 'opacity-0 absolute'"
                            v-show="currentSlide === index"
                        >
                            <!-- Tag -->
                            <span class="inline-block px-4 py-1.5 text-xs md:text-sm font-semibold tracking-wider text-gold-400 bg-gold-400/10 border border-gold-400/30 rounded-full mb-5 md:mb-6">
                                {{ slide.tag }}
                            </span>

                            <!-- Title -->
                            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold text-white leading-tight mb-5 md:mb-6">
                                {{ slide.title }}
                            </h1>

                            <!-- Subtitle -->
                            <p class="text-base md:text-lg lg:text-xl text-white/80 leading-relaxed mb-8 md:mb-10 max-w-xl">
                                {{ slide.subtitle }}
                            </p>

                            <!-- CTA Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <Link 
                                    href="/register"
                                    class="inline-flex items-center justify-center px-6 md:px-8 py-3.5 md:py-4 text-sm md:text-base font-semibold text-primary-900 bg-white rounded-xl hover:bg-gray-100 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl"
                                >
                                    {{ slide.cta.primary }}
                                    <svg class="ml-2 w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </Link>
                                <a 
                                    href="#about"
                                    class="inline-flex items-center justify-center px-6 md:px-8 py-3.5 md:py-4 text-sm md:text-base font-semibold text-white bg-white/10 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/20 transition-all duration-200"
                                >
                                    {{ slide.cta.secondary }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Navigation Arrow (Desktop) -->
                <div class="hidden lg:flex flex-shrink-0 w-16 xl:w-20 h-full items-center justify-center">
                    <button 
                        @click="nextSlide"
                        class="p-3 xl:p-4 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full transition-all duration-200 group"
                        aria-label="Next slide"
                    >
                        <svg class="w-5 h-5 xl:w-6 xl:h-6 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Slide Indicators -->
            <div class="absolute bottom-6 md:bottom-8 left-1/2 -translate-x-1/2 flex items-center space-x-2 md:space-x-3 z-30">
                <button 
                    v-for="(slide, index) in slides" 
                    :key="slide.id"
                    @click="goToSlide(index)"
                    class="group relative p-1"
                    :aria-label="`Go to slide ${index + 1}`"
                    :aria-current="currentSlide === index ? 'true' : 'false'"
                >
                    <span 
                        class="block w-2 h-2 md:w-2.5 md:h-2.5 rounded-full transition-all duration-300"
                        :class="currentSlide === index 
                            ? 'bg-white w-6 md:w-8' 
                            : 'bg-white/40 group-hover:bg-white/60'"
                    ></span>
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Simple fade transition only */
</style>
