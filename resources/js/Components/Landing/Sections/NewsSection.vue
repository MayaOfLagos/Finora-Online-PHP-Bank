<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isVisible = ref(false);
const sectionRef = ref(null);

const newsItems = [
    {
        id: 1,
        title: 'Finora Bank Launches New Mobile App with Enhanced Security Features',
        excerpt: 'Our latest mobile banking app introduces biometric authentication, real-time fraud alerts, and an intuitive new interface for easier banking.',
        date: 'January 20, 2026',
        image: 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=600&q=80',
        category: 'Technology'
    },
    {
        id: 2,
        title: 'New Business Loan Program to Support Small Enterprises',
        excerpt: 'Finora Bank announces a $50 million fund dedicated to supporting small businesses with competitive rates and flexible terms.',
        date: 'January 15, 2026',
        image: 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=600&q=80',
        category: 'Business'
    },
    {
        id: 3,
        title: 'Community Investment: $100K Donated to Local Education',
        excerpt: 'As part of our commitment to the community, Finora Bank has donated to local educational institutions to support student programs.',
        date: 'January 10, 2026',
        image: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=600&q=80',
        category: 'Community'
    }
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
        { threshold: 0.1 }
    );
    
    if (sectionRef.value) {
        observer.observe(sectionRef.value);
    }
});
</script>

<template>
    <section ref="sectionRef" class="py-20 lg:py-28 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div 
                class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-12"
                :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
            >
                <div>
                    <span class="inline-block text-xs font-semibold tracking-widest text-primary-600 uppercase mb-4">
                        News & Updates
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                        Latest from Finora
                    </h2>
                </div>
                <Link 
                    href="/news"
                    class="hidden sm:inline-flex items-center text-primary-600 font-semibold hover:text-primary-700 transition-colors mt-4 sm:mt-0"
                >
                    View All News
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
            </div>

            <!-- News Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <article 
                    v-for="(item, index) in newsItems"
                    :key="item.id"
                    class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100"
                    :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                    :style="{ animationDelay: `${(index + 1) * 0.15}s` }"
                >
                    <!-- Image -->
                    <div class="relative h-48 overflow-hidden">
                        <img 
                            :src="item.image" 
                            :alt="item.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            loading="lazy"
                        >
                        <!-- Category Badge -->
                        <span class="absolute top-4 left-4 px-3 py-1 text-xs font-semibold bg-white/90 backdrop-blur-sm rounded-full text-primary-600">
                            {{ item.category }}
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <!-- Date -->
                        <span class="text-sm text-gray-500 mb-3 block">
                            {{ item.date }}
                        </span>

                        <!-- Title -->
                        <h3 class="text-lg font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-primary-600 transition-colors">
                            {{ item.title }}
                        </h3>

                        <!-- Excerpt -->
                        <p class="text-gray-600 text-sm leading-relaxed line-clamp-3 mb-4">
                            {{ item.excerpt }}
                        </p>

                        <!-- Read More -->
                        <Link 
                            href="/news"
                            class="inline-flex items-center text-primary-600 font-semibold text-sm hover:text-primary-700 transition-colors"
                        >
                            Read More
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </article>
            </div>

            <!-- Mobile View All Link -->
            <div class="sm:hidden mt-8 text-center">
                <Link 
                    href="/news"
                    class="inline-flex items-center text-primary-600 font-semibold hover:text-primary-700 transition-colors"
                >
                    View All News
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
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

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
