<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isVisible = ref(false);
const sectionRef = ref(null);

const services = [
    {
        icon: 'wallet',
        title: 'Personal Banking',
        description: 'Comprehensive savings and checking accounts with competitive interest rates and no hidden fees.',
        link: '/register',
        features: ['Free ATM access', 'Mobile deposits', 'Overdraft protection']
    },
    {
        icon: 'briefcase',
        title: 'Business Banking',
        description: 'Tailored financial solutions for businesses of all sizes, from startups to enterprises.',
        link: '/register',
        features: ['Business checking', 'Merchant services', 'Payroll solutions']
    },
    {
        icon: 'device-mobile',
        title: 'Online Banking',
        description: '24/7 access to your accounts from anywhere with our secure digital banking platform.',
        link: '/login',
        features: ['Bill pay', 'Fund transfers', 'Account alerts']
    },
    {
        icon: 'currency-dollar',
        title: 'Loans & Mortgages',
        description: 'Competitive rates on personal, auto, home, and business loans with flexible terms.',
        link: '/register',
        features: ['Low rates', 'Fast approval', 'Flexible terms']
    },
    {
        icon: 'credit-card',
        title: 'Credit Cards',
        description: 'Rewards cards, travel cards, and business cards with exclusive benefits and cashback.',
        link: '/register',
        features: ['Cash rewards', 'Travel perks', 'No annual fee']
    },
    {
        icon: 'chart-bar',
        title: 'Investments',
        description: 'Build your wealth with our investment advisory services and diverse portfolio options.',
        link: '/register',
        features: ['Expert advice', 'Diverse options', 'Retirement planning']
    }
];

const getIcon = (iconName) => {
    const icons = {
        'wallet': 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'briefcase': 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'device-mobile': 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        'currency-dollar': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'credit-card': 'M3 10h18M7 15h1m4 0h1M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'chart-bar': 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
    };
    return icons[iconName] || icons['wallet'];
};

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
    <section id="services" ref="sectionRef" class="py-16 md:py-24 lg:py-32 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div 
                class="text-center max-w-3xl mx-auto mb-12 md:mb-16"
                :class="isVisible ? 'animate-fade-in-up' : 'opacity-0'"
            >
                <span class="inline-block px-4 py-1.5 text-xs font-semibold tracking-wider text-primary-600 bg-primary-100 rounded-full mb-4">
                    OUR SERVICES
                </span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Banking Solutions for<br class="hidden sm:block">
                    <span class="text-primary-600">Every Need</span>
                </h2>
                <p class="text-lg text-gray-600">
                    From everyday banking to wealth management, we offer comprehensive financial 
                    services designed to help you achieve your goals.
                </p>
            </div>

            <!-- Services Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                <div 
                    v-for="(service, index) in services"
                    :key="service.title"
                    class="group bg-white rounded-2xl p-6 md:p-8 shadow-sm hover:shadow-xl border border-gray-100 hover:border-primary-100 transition-all duration-300"
                    :class="isVisible ? 'animate-fade-in-up' : 'opacity-0'"
                    :style="{ animationDelay: `${index * 0.1}s` }"
                >
                    <!-- Icon -->
                    <div class="w-14 h-14 flex items-center justify-center bg-primary-50 group-hover:bg-primary-100 rounded-xl mb-6 transition-colors duration-300">
                        <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getIcon(service.icon)" />
                        </svg>
                    </div>

                    <!-- Title -->
                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-primary-600 transition-colors duration-300">
                        {{ service.title }}
                    </h3>

                    <!-- Description -->
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        {{ service.description }}
                    </p>

                    <!-- Features -->
                    <ul class="space-y-2 mb-6">
                        <li 
                            v-for="feature in service.features" 
                            :key="feature"
                            class="flex items-center text-sm text-gray-500"
                        >
                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ feature }}
                        </li>
                    </ul>

                    <!-- Link -->
                    <Link 
                        :href="service.link"
                        class="inline-flex items-center text-primary-600 font-semibold hover:text-primary-700 group/link"
                    >
                        Learn More
                        <svg class="ml-2 w-4 h-4 transform group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- CTA -->
            <div 
                class="text-center mt-12"
                :class="isVisible ? 'animate-fade-in-up' : 'opacity-0'"
                style="animation-delay: 0.6s"
            >
                <p class="text-gray-600 mb-4">Ready to experience better banking?</p>
                <Link 
                    href="/register"
                    class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                    Open an Account Today
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
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

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out forwards;
}
</style>
