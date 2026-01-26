<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isVisible = ref(false);
const sectionRef = ref(null);

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
    <section id="about" ref="sectionRef" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Content Side -->
                <div 
                    :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                >
                    <!-- Section Label -->
                    <span class="inline-block text-xs font-semibold tracking-widest text-primary-600 uppercase mb-4">
                        Who We Are
                    </span>

                    <!-- Title -->
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-6">
                        Finora is a bank focused on <span class="text-primary-600">creating opportunities.</span>
                    </h2>

                    <!-- Description -->
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        We are built on a foundation of strong relationships between the people we serve, 
                        the communities we live in and the employees who represent our financial institution. 
                        We see potential in all we do.
                    </p>

                    <!-- CTA Button -->
                    <Link 
                        href="/about"
                        class="inline-flex items-center text-primary-600 font-semibold hover:text-primary-700 transition-colors group"
                    >
                        About Us
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>

                <!-- Image Side -->
                <div 
                    class="relative"
                    :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                    style="animation-delay: 0.2s"
                >
                    <div class="relative rounded-2xl overflow-hidden shadow-xl">
                        <img 
                            src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80"
                            alt="Finora Bank Team"
                            class="w-full h-80 lg:h-[450px] object-cover"
                            loading="lazy"
                        >
                        <!-- Subtle overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-primary-900/20 to-transparent"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}
</style>
