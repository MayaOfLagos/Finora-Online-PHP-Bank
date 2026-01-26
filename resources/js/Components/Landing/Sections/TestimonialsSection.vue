<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isVisible = ref(false);
const sectionRef = ref(null);
const currentIndex = ref(0);
const isAnimating = ref(false);

const testimonials = [
    {
        name: 'Sarah Johnson',
        role: 'Small Business Owner',
        location: 'New York, USA',
        image: 'SJ',
        rating: 5,
        quote: 'Finora has transformed how I manage my business finances. Their mobile app is intuitive, and the customer support is exceptional. I can manage payroll, invoices, and accounts all from my phone.',
        highlight: 'Best banking experience ever'
    },
    {
        name: 'Michael Chen',
        role: 'Software Engineer',
        location: 'San Francisco, USA',
        image: 'MC',
        rating: 5,
        quote: 'The security features give me peace of mind. Real-time fraud alerts and biometric login mean my money is always protected. Plus, their API for developers is fantastic!',
        highlight: 'Unmatched security'
    },
    {
        name: 'Emily Rodriguez',
        role: 'Marketing Director',
        location: 'Miami, USA',
        image: 'ER',
        rating: 5,
        quote: 'Switching to Finora was the best financial decision I made. Zero fees on international transfers saved me thousands last year. Their rates are unbeatable.',
        highlight: 'Saved thousands in fees'
    },
    {
        name: 'David Thompson',
        role: 'Retired Teacher',
        location: 'Chicago, USA',
        image: 'DT',
        rating: 5,
        quote: 'At my age, I needed a bank that makes things simple. Finora\'s interface is clean and easy to use. Their phone support team is patient and helpful.',
        highlight: 'Simple & easy to use'
    },
    {
        name: 'Priya Patel',
        role: 'Healthcare Professional',
        location: 'Houston, USA',
        image: 'PP',
        rating: 5,
        quote: 'The instant notifications and spending insights help me stay on top of my finances. I love how I can set savings goals and track my progress easily.',
        highlight: 'Great financial tools'
    }
];

let autoplayInterval = null;

const nextTestimonial = () => {
    if (isAnimating.value) return;
    isAnimating.value = true;
    currentIndex.value = (currentIndex.value + 1) % testimonials.length;
    setTimeout(() => isAnimating.value = false, 500);
};

const prevTestimonial = () => {
    if (isAnimating.value) return;
    isAnimating.value = true;
    currentIndex.value = (currentIndex.value - 1 + testimonials.length) % testimonials.length;
    setTimeout(() => isAnimating.value = false, 500);
};

const goToTestimonial = (index) => {
    if (isAnimating.value || index === currentIndex.value) return;
    isAnimating.value = true;
    currentIndex.value = index;
    setTimeout(() => isAnimating.value = false, 500);
};

const startAutoplay = () => {
    autoplayInterval = setInterval(() => {
        nextTestimonial();
    }, 7000);
};

const stopAutoplay = () => {
    if (autoplayInterval) {
        clearInterval(autoplayInterval);
    }
};

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    isVisible.value = true;
                    startAutoplay();
                } else {
                    stopAutoplay();
                }
            });
        },
        { threshold: 0.2 }
    );
    
    if (sectionRef.value) {
        observer.observe(sectionRef.value);
    }
});

onUnmounted(() => {
    stopAutoplay();
});
</script>

<template>
    <section 
        ref="sectionRef" 
        class="py-16 md:py-24 bg-white relative overflow-hidden"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
    >
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-[0.02]">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600 to-gold-600"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <!-- Section Header -->
            <div 
                class="text-center max-w-3xl mx-auto mb-12 md:mb-16"
                :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
            >
                <span class="inline-block px-4 py-1.5 text-xs font-semibold tracking-wider text-gold-600 bg-gold-50 border border-gold-200 rounded-full mb-4">
                    TESTIMONIALS
                </span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Trusted by <span class="text-primary-600">Millions</span> Worldwide
                </h2>
                <p class="text-lg text-gray-600">
                    Don't just take our word for it. Here's what our customers say about their Finora experience.
                </p>
            </div>

            <!-- Testimonials Carousel -->
            <div 
                class="relative max-w-4xl mx-auto"
                :class="isVisible ? 'animate-fade-in-up' : 'opacity-0'"
                style="animation-delay: 0.2s"
            >
                <!-- Main Card -->
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-3xl p-6 md:p-10 shadow-xl border border-gray-100">
                    <!-- Quote Icon -->
                    <div class="absolute top-6 right-6 md:top-10 md:right-10 opacity-10">
                        <svg class="w-16 h-16 md:w-24 md:h-24 text-primary-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                        </svg>
                    </div>

                    <!-- Testimonial Content -->
                    <div class="relative">
                        <transition name="fade" mode="out-in">
                            <div :key="currentIndex" class="text-center md:text-left">
                                <!-- Stars -->
                                <div class="flex justify-center md:justify-start mb-4">
                                    <svg v-for="n in 5" :key="n" class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>

                                <!-- Highlight Badge -->
                                <span class="inline-block px-3 py-1 text-xs font-semibold text-primary-600 bg-primary-50 rounded-full mb-4">
                                    "{{ testimonials[currentIndex].highlight }}"
                                </span>

                                <!-- Quote -->
                                <p class="text-lg md:text-xl lg:text-2xl text-gray-700 leading-relaxed mb-8">
                                    "{{ testimonials[currentIndex].quote }}"
                                </p>

                                <!-- Author -->
                                <div class="flex flex-col md:flex-row items-center md:items-start gap-4">
                                    <!-- Avatar -->
                                    <div class="w-14 h-14 bg-gradient-to-br from-primary-600 to-primary-800 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                        {{ testimonials[currentIndex].image }}
                                    </div>
                                    <div class="text-center md:text-left">
                                        <h4 class="text-lg font-bold text-gray-900">
                                            {{ testimonials[currentIndex].name }}
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            {{ testimonials[currentIndex].role }}
                                        </p>
                                        <p class="text-xs text-gray-400 flex items-center justify-center md:justify-start mt-1">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ testimonials[currentIndex].location }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>

                <!-- Navigation Arrows (Desktop) -->
                <button 
                    @click="prevTestimonial"
                    class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1/2 w-12 h-12 bg-white rounded-full shadow-lg items-center justify-center text-gray-600 hover:text-primary-600 hover:scale-110 transition-all duration-300 border border-gray-100"
                    :disabled="isAnimating"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button 
                    @click="nextTestimonial"
                    class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 w-12 h-12 bg-white rounded-full shadow-lg items-center justify-center text-gray-600 hover:text-primary-600 hover:scale-110 transition-all duration-300 border border-gray-100"
                    :disabled="isAnimating"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Dots Navigation -->
                <div class="flex justify-center mt-6 gap-2">
                    <button
                        v-for="(_, index) in testimonials"
                        :key="index"
                        @click="goToTestimonial(index)"
                        class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                        :class="currentIndex === index ? 'bg-primary-600 w-8' : 'bg-gray-300 hover:bg-gray-400'"
                    />
                </div>

                <!-- Mobile Navigation Arrows -->
                <div class="flex md:hidden justify-center gap-4 mt-6">
                    <button 
                        @click="prevTestimonial"
                        class="w-12 h-12 bg-white rounded-full shadow-lg flex items-center justify-center text-gray-600 hover:text-primary-600 transition-all duration-300 border border-gray-100"
                        :disabled="isAnimating"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button 
                        @click="nextTestimonial"
                        class="w-12 h-12 bg-white rounded-full shadow-lg flex items-center justify-center text-gray-600 hover:text-primary-600 transition-all duration-300 border border-gray-100"
                        :disabled="isAnimating"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateX(20px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}
</style>
