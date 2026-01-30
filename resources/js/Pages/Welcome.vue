<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useToast } from '@/Composables/useToast';

// Import landing page components - Minimal Design
import PagePreloader from '@/Components/Common/PagePreloader.vue';
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

const handlePreloaderComplete = () => {
    isLoading.value = false;
    
    // Show logout toast if user just logged out
    if (page.props.flash?.logout) {
        toast.success('You have been successfully logged out. See you soon!', 'Logged Out');
    }
};
</script>

<template>
    <Head title="Welcome to Finora Bank - Modern Banking Solutions" />

    <!-- Ultra Modern Preloader -->
    <PagePreloader :min-load-time="1800" @complete="handlePreloaderComplete" />

    <!-- Main Page Content -->
    <Transition
        enter-active-class="transition-all duration-700 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
    >
        <div v-show="!isLoading" class="min-h-screen bg-white">
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
    </Transition>
</template>

<style scoped>
/* Smooth page reveal */
</style>

