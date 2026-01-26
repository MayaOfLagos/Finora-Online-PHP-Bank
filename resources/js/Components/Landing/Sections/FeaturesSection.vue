<script setup>
import { ref, onMounted } from 'vue';

const isVisible = ref(false);
const sectionRef = ref(null);

const features = [
    {
        icon: 'shield',
        title: 'Bank-Grade Security',
        description: 'Your money and data are protected with military-grade 256-bit encryption, biometric authentication, and real-time fraud monitoring.',
        highlights: ['Multi-Factor Auth', 'Fraud Detection', 'Secure Login']
    },
    {
        icon: 'clock',
        title: '24/7 Availability',
        description: 'Access your accounts anytime, anywhere. Our mobile and online banking platforms never sleep, so your money is always at your fingertips.',
        highlights: ['Mobile App', 'Online Banking', 'ATM Network']
    },
    {
        icon: 'money',
        title: 'Low Fees & Great Rates',
        description: 'Enjoy competitive interest rates and transparent fee structure. No hidden charges, no surprises - just straightforward banking.',
        highlights: ['No Hidden Fees', 'High Interest', 'Low Charges']
    },
    {
        icon: 'support',
        title: 'Expert Support',
        description: 'Our dedicated team of financial experts is available round the clock to help you with any questions or concerns.',
        highlights: ['24/7 Support', 'Live Chat', 'Personal Advisor']
    }
];

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
    <section ref="sectionRef" class="py-16 md:py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div 
                class="text-center max-w-3xl mx-auto mb-12 md:mb-16"
                :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
            >
                <span class="inline-block px-4 py-1.5 text-xs font-semibold tracking-wider text-primary-600 bg-primary-50 border border-primary-100 rounded-full mb-4">
                    WHY CHOOSE FINORA
                </span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Banking Built for <span class="text-primary-600">Your Success</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Experience the perfect blend of cutting-edge technology and personalized service that sets Finora apart.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                <div 
                    v-for="(feature, index) in features"
                    :key="feature.title"
                    class="group relative bg-white rounded-2xl p-6 md:p-8 shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100 hover:border-primary-200"
                    :class="isVisible ? 'animate-fade-in-up' : 'opacity-0'"
                    :style="{ animationDelay: `${index * 0.15}s` }"
                >
                    <!-- Icon -->
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-primary-500/20">
                        <!-- Shield Icon -->
                        <svg v-if="feature.icon === 'shield'" class="w-7 h-7 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <!-- Clock Icon -->
                        <svg v-else-if="feature.icon === 'clock'" class="w-7 h-7 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Money Icon -->
                        <svg v-else-if="feature.icon === 'money'" class="w-7 h-7 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Support Icon -->
                        <svg v-else class="w-7 h-7 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 group-hover:text-primary-700 transition-colors">
                        {{ feature.title }}
                    </h3>
                    <p class="text-gray-600 leading-relaxed mb-5">
                        {{ feature.description }}
                    </p>

                    <!-- Highlights -->
                    <div class="flex flex-wrap gap-2">
                        <span 
                            v-for="highlight in feature.highlights"
                            :key="highlight"
                            class="inline-flex items-center px-3 py-1 text-xs font-medium bg-primary-50 text-primary-700 rounded-full"
                        >
                            <svg class="w-3 h-3 mr-1.5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            {{ highlight }}
                        </span>
                    </div>

                    <!-- Hover Accent -->
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-500 to-gold-500 rounded-b-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
            </div>

            <!-- CTA -->
            <div 
                class="text-center mt-12 md:mt-16"
                :class="isVisible ? 'animate-fade-in' : 'opacity-0'"
                style="animation-delay: 0.6s"
            >
                <a 
                    href="/register"
                    class="inline-flex items-center px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl hover:from-primary-700 hover:to-primary-800 transition-all duration-300 shadow-lg shadow-primary-500/30 hover:shadow-xl hover:shadow-primary-500/40"
                >
                    Start Banking Today
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
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
