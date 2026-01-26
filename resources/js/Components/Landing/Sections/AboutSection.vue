<script setup>
import { ref, onMounted } from 'vue';

const isVisible = ref(false);
const sectionRef = ref(null);

const highlights = [
    { icon: 'shield', label: 'FDIC Insured', value: 'Up to $250,000' },
    { icon: 'clock', label: 'Years of Excellence', value: '15+' },
    { icon: 'users', label: 'Happy Customers', value: '2M+' },
    { icon: 'globe', label: 'Countries Served', value: '50+' },
];

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    isVisible.value = true;
                    observer.disconnect();
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
    <section id="about" ref="sectionRef" class="py-16 md:py-24 lg:py-32 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Image Side -->
                <div 
                    class="relative"
                    :class="isVisible ? 'animate-fade-in-left' : 'opacity-0'"
                >
                    <!-- Main Image -->
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <img 
                            src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80"
                            alt="Professional banking team"
                            class="w-full h-80 md:h-96 lg:h-[500px] object-cover"
                            loading="lazy"
                        >
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-primary-900/50 to-transparent"></div>
                    </div>

                    <!-- Experience Badge -->
                    <div class="absolute -bottom-6 -right-6 md:bottom-8 md:-right-8 bg-white rounded-2xl shadow-xl p-4 md:p-6 border border-gray-100">
                        <div class="text-center">
                            <span class="block text-3xl md:text-4xl font-bold text-primary-600">15+</span>
                            <span class="block text-sm text-gray-600 font-medium">Years of<br>Excellence</span>
                        </div>
                    </div>

                    <!-- Decorative Element -->
                    <div class="absolute -top-4 -left-4 w-24 h-24 bg-gold-400/20 rounded-full blur-2xl"></div>
                    <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-primary-400/20 rounded-full blur-2xl"></div>
                </div>

                <!-- Content Side -->
                <div 
                    class="lg:pl-8"
                    :class="isVisible ? 'animate-fade-in-right' : 'opacity-0'"
                    style="animation-delay: 0.2s"
                >
                    <!-- Section Tag -->
                    <span class="inline-block px-4 py-1.5 text-xs font-semibold tracking-wider text-primary-600 bg-primary-50 rounded-full mb-4">
                        ABOUT FINORA BANK
                    </span>

                    <!-- Title -->
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-6">
                        A Legacy of Trust,<br>
                        <span class="text-primary-600">A Future of Innovation</span>
                    </h2>

                    <!-- Description -->
                    <p class="text-lg text-gray-600 leading-relaxed mb-6">
                        For over 15 years, Finora Bank has been at the forefront of financial innovation, 
                        combining traditional banking values with cutting-edge technology. We're committed 
                        to helping individuals and businesses achieve their financial goals through 
                        personalized service and innovative solutions.
                    </p>

                    <p class="text-gray-600 leading-relaxed mb-8">
                        Our mission is simple: to make banking accessible, secure, and rewarding for everyone. 
                        Whether you're saving for your first home, expanding your business, or planning for 
                        retirement, we're here to guide you every step of the way.
                    </p>

                    <!-- Highlights Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div 
                            v-for="(item, index) in highlights" 
                            :key="item.label"
                            class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl"
                        >
                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-primary-100 rounded-lg">
                                <svg v-if="item.icon === 'shield'" class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <svg v-if="item.icon === 'clock'" class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <svg v-if="item.icon === 'users'" class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <svg v-if="item.icon === 'globe'" class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-lg font-bold text-gray-900">{{ item.value }}</span>
                                <span class="block text-xs text-gray-500">{{ item.label }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <a 
                        href="#services"
                        class="inline-flex items-center text-primary-600 font-semibold hover:text-primary-700 group"
                    >
                        Discover Our Services
                        <svg class="ml-2 w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-fade-in-left {
    animation: fadeInLeft 0.8s ease-out forwards;
}

.animate-fade-in-right {
    animation: fadeInRight 0.8s ease-out forwards;
}
</style>
