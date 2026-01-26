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
    <section ref="sectionRef" class="py-20 lg:py-28 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Image Side -->
                <div 
                    class="relative order-2 lg:order-1"
                    :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                >
                    <div class="relative rounded-2xl overflow-hidden shadow-xl">
                        <img 
                            src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=800&q=80"
                            alt="Online Banking on Mobile"
                            class="w-full h-80 lg:h-[400px] object-cover"
                            loading="lazy"
                        >
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-primary-900/30 to-transparent"></div>
                        
                        <!-- Play Button (optional for video feel) -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center shadow-lg cursor-pointer hover:bg-white hover:scale-110 transition-all duration-300">
                                <svg class="w-6 h-6 text-primary-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Side -->
                <div 
                    class="order-1 lg:order-2"
                    :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                    style="animation-delay: 0.2s"
                >
                    <!-- Section Label -->
                    <span class="inline-block text-xs font-semibold tracking-widest text-primary-600 uppercase mb-4">
                        Digital Banking
                    </span>

                    <!-- Title -->
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-6">
                        The Ins & Outs of<br>
                        <span class="text-primary-600">Online Banking</span>
                    </h2>

                    <!-- Description -->
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        With our intuitive mobile app and online platform, it is now easier and faster 
                        than ever to manage your finances. Transfer funds, pay bills, deposit checks, 
                        and track your spending — all from the palm of your hand.
                    </p>

                    <!-- Feature List -->
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Instant fund transfers
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Mobile check deposit
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Real-time notifications
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Bill pay & scheduling
                        </li>
                    </ul>

                    <!-- CTA Button -->
                    <Link 
                        href="/login"
                        class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-lg shadow-primary-600/20 hover:shadow-xl"
                    >
                        Online Banking
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
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
