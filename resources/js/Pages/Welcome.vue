<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { useToast } from '@/Composables/useToast';

// Import landing page components - Minimal Design
import Preloader from '@/Components/Landing/Preloader.vue';
import MainHeader from '@/Components/Landing/Header/MainHeader.vue';
import HeroBanner from '@/Components/Landing/Hero/HeroBanner.vue';
import QuickLinksBar from '@/Components/Landing/Sections/QuickLinksBar.vue';
import WhoWeAreSection from '@/Components/Landing/Sections/WhoWeAreSection.vue';
import CoreValuesSection from '@/Components/Landing/Sections/CoreValuesSection.vue';
import OnlineBankingSection from '@/Components/Landing/Sections/OnlineBankingSection.vue';
import NewsSection from '@/Components/Landing/Sections/NewsSection.vue';
import MainFooter from '@/Components/Landing/Footer/MainFooter.vue';
import MobileStickyFooter from '@/Components/Landing/Footer/MobileStickyFooter.vue';
import LiveChatWidget from '@/Components/Common/LiveChatWidget.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const toast = useToast();
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
                
                // Show logout toast if user just logged out
                if (page.props.flash?.logout) {
                    toast.success('You have been successfully logged out. See you soon!', 'Logged Out');
                }
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

        <!-- Quick Links Bar (Personal Banking, Business Banking, etc.) -->
        <QuickLinksBar />

        <!-- Who We Are Section -->
        <WhoWeAreSection id="about" />

        <!-- Core Values Section -->
        <CoreValuesSection />

        <!-- Online Banking CTA Section -->
        <OnlineBankingSection />

        <!-- News & Updates Section -->
        <NewsSection />

        <!-- Footer -->
        <MainFooter />

        <!-- Mobile Sticky Footer -->
        <MobileStickyFooter />

        <!-- Live Chat Widget -->
        <LiveChatWidget context="public" />
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

