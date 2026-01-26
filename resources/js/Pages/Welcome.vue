<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

// Import all landing page components
import Preloader from '@/Components/Landing/Preloader.vue';
import MainHeader from '@/Components/Landing/Header/MainHeader.vue';
import HeroBanner from '@/Components/Landing/Hero/HeroBanner.vue';
import AboutSection from '@/Components/Landing/Sections/AboutSection.vue';
import ServicesSection from '@/Components/Landing/Sections/ServicesSection.vue';
import FeaturesSection from '@/Components/Landing/Sections/FeaturesSection.vue';
import StatsSection from '@/Components/Landing/Sections/StatsSection.vue';
import TestimonialsSection from '@/Components/Landing/Sections/TestimonialsSection.vue';
import CTASection from '@/Components/Landing/Sections/CTASection.vue';
import MainFooter from '@/Components/Landing/Footer/MainFooter.vue';
import MobileStickyFooter from '@/Components/Landing/Footer/MobileStickyFooter.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isLoading = ref(true);
const isPageReady = ref(false);

onMounted(() => {
    // Show preloader for minimum time or until page loads
    const minLoadTime = 2000;
    const startTime = Date.now();

    const checkReady = () => {
        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, minLoadTime - elapsed);
        
        setTimeout(() => {
            isLoading.value = false;
            setTimeout(() => {
                isPageReady.value = true;
            }, 300);
        }, remaining);
    };

    if (document.readyState === 'complete') {
        checkReady();
    } else {
        window.addEventListener('load', checkReady);
    }
});
</script>

<template>
    <Head title="Welcome to Finora Bank - Modern Banking Solutions" />

    <!-- Preloader -->
    <Preloader v-if="isLoading" />

    <!-- Main Page Content -->
    <div 
        class="min-h-screen bg-white"
        :class="{ 'opacity-0': isLoading, 'animate-fade-in': !isLoading }"
    >
        <!-- Header -->
        <MainHeader />

        <!-- Hero Banner -->
        <HeroBanner />

        <!-- About Section -->
        <AboutSection id="about" />

        <!-- Services Section -->
        <ServicesSection id="services" />

        <!-- Features Section (Why Choose Us) -->
        <FeaturesSection />

        <!-- Stats Section -->
        <StatsSection />

        <!-- Testimonials Section -->
        <TestimonialsSection />

        <!-- CTA Section -->
        <CTASection />

        <!-- Footer -->
        <MainFooter />

        <!-- Mobile Sticky Footer -->
        <MobileStickyFooter />
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
</style>
