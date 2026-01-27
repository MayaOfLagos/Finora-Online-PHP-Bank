<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SeoHead from '@/Components/Common/SeoHead.vue';
import MainHeader from '@/Components/Landing/Header/MainHeader.vue';
import MainFooter from '@/Components/Landing/Footer/MainFooter.vue';
import MobileStickyFooter from '@/Components/Landing/Footer/MobileStickyFooter.vue';

const props = defineProps({
    page: {
        type: Object,
        required: true,
    },
});

const pageData = usePage();
const siteName = computed(() => pageData.props.settings?.general?.site_name || pageData.props.settings?.general?.app_name || 'Finora Bank');

const primaryCta = computed(() => props.page.primaryCta ?? {
    label: 'Open an account',
    href: '/register',
});

const secondaryCta = computed(() => props.page.secondaryCta ?? {
    label: 'Contact support',
    href: '/contact',
});

const sections = computed(() => props.page.sections ?? []);

const iconMap = {
    'Protected accounts': 'pi-shield',
    'Human support': 'pi-users',
    'Responsible banking': 'pi-check-circle',
    'Clear fees': 'pi-wallet',
    'Every device ready': 'pi-mobile',
    'Global-ready': 'pi-globe',
    'Secure accounts': 'pi-lock',
    'Cards and payments': 'pi-credit-card',
    'Online banking': 'pi-desktop',
    'Loans and financing': 'pi-home',
    'Business solutions': 'pi-chart-bar',
    'Insights and reporting': 'pi-chart-line',
    'Smart accounts': 'pi-chart-pie',
    'Instant transfers': 'pi-send',
    'Card controls': 'pi-sliders-h',
    'PIN and OTP layers': 'pi-lock-open',
    'Alerts that matter': 'pi-bell',
    'Fraud response': 'pi-exclamation-circle',
    'Treasury-like visibility': 'pi-eye',
    'Team permissions': 'pi-user-edit',
    'Vendor-friendly payments': 'pi-dollar',
    'Working capital': 'pi-briefcase',
    'Expense discipline': 'pi-list',
    'Compliance ready': 'pi-check',
    'Instant card actions': 'pi-bolt',
    'Biometric login': 'pi-key',
    'Smart alerts': 'pi-bell',
    'Encrypted sessions': 'pi-shield-check',
    'Location-aware checks': 'pi-map-marker',
    'One-tap support': 'pi-phone',
    'Tailored options': 'pi-palette',
    'Upfront timelines': 'pi-calendar',
    'Payment confidence': 'pi-star-fill',
    'Document help': 'pi-file',
    'Rate visibility': 'pi-eye',
    'Early payoff friendly': 'pi-star',
    'Instant issuance': 'pi-bolt',
    'Real-time alerts': 'pi-bell',
    'Travel ready': 'pi-share-alt',
    'Rewards focus': 'pi-gift',
    'Fees kept low': 'pi-dollar',
    'Security layers': 'pi-lock',
    'Secure messaging': 'pi-envelope',
    'Phone support': 'pi-phone',
    'Email experts': 'pi-envelope',
    'Contact details': 'pi-address-book',
    'Context': 'pi-comment',
    'Timing': 'pi-clock',
};

const getIcon = (title) => {
    return iconMap[title] || 'pi-star';
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50 text-gray-900">
        <SeoHead :title="page.title" :description="page.summary" />

        <MainHeader />

        <main class="pt-28 pb-20 lg:pt-32 lg:pb-24">
            <!-- Hero Section -->
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
                <!-- Background Decoration -->
                <div class="absolute inset-0 -z-10 overflow-hidden">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-primary-100/30 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl"></div>
                </div>

                <div class="relative bg-gradient-to-br from-white/90 via-white/80 to-primary-50/30 backdrop-blur-xl border border-gray-200/50 shadow-2xl shadow-primary-100/40 rounded-3xl overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-primary-400/10 to-blue-400/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative p-8 md:p-12 lg:p-16">
                        <p class="inline-flex items-center gap-2 text-sm font-bold text-primary-600 uppercase tracking-wide mb-4 bg-primary-50 px-4 py-2 rounded-full">
                            <span class="w-2 h-2 bg-primary-600 rounded-full animate-pulse"></span>
                            {{ page.eyebrow }}
                        </p>
                        
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6 max-w-4xl">
                            {{ page.title }}
                        </h1>
                        
                        <p class="text-xl md:text-2xl text-gray-600 leading-relaxed max-w-3xl mb-8">
                            {{ page.summary }}
                        </p>

                        <div class="flex flex-wrap gap-4">
                            <Link
                                :href="primaryCta.href"
                                class="group inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-bold text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 shadow-lg shadow-primary-600/40 hover:shadow-xl hover:shadow-primary-600/50 transition-all duration-300 hover:-translate-y-0.5"
                            >
                                {{ primaryCta.label }}
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </Link>
                            
                            <Link
                                :href="secondaryCta.href"
                                class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-bold text-primary-700 bg-white hover:bg-gray-50 border-2 border-primary-200 hover:border-primary-300 transition-all duration-200"
                            >
                                {{ secondaryCta.label }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <section v-for="(section, sectionIndex) in sections" :key="section.title" class="mb-20">
                    <!-- Section Header -->
                    <div class="text-center max-w-3xl mx-auto mb-12">
                        <p class="inline-flex items-center gap-2 text-sm font-bold text-primary-600 uppercase tracking-wider mb-4 bg-primary-50 px-4 py-2 rounded-full">
                            <span class="w-2 h-2 bg-primary-600 rounded-full"></span>
                            {{ section.title }}
                        </p>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            {{ section.description }}
                        </h2>
                    </div>

                    <!-- Cards Grid -->
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <article
                            v-for="(item, itemIndex) in section.items"
                            :key="item.title"
                            class="group relative bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-xl hover:border-primary-200 transition-all duration-300 hover:-translate-y-1 overflow-hidden"
                        >
                            <!-- Hover Effect Background -->
                            <div class="absolute inset-0 bg-gradient-to-br from-primary-50/0 to-blue-50/0 group-hover:from-primary-50/50 group-hover:to-blue-50/30 transition-all duration-300 -z-10"></div>
                            
                            <!-- Icon -->
                            <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-primary-100 to-blue-100 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                                <i :class="`pi ${getIcon(item.title)} text-2xl text-primary-600`"></i>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-primary-600 transition-colors">
                                {{ item.title }}
                            </h3>
                            
                            <p class="text-base text-gray-600 leading-relaxed">
                                {{ item.description }}
                            </p>

                            <!-- Decorative Element -->
                            <div class="absolute bottom-0 right-0 w-24 h-24 bg-gradient-to-tl from-primary-100/20 to-transparent rounded-tl-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </article>
                    </div>
                </section>
            </div>

            <!-- CTA Section -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-24">
                <div class="relative bg-gradient-to-r from-primary-600 to-blue-600 rounded-3xl p-8 md:p-12 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative text-center">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                            Ready to get started?
                        </h2>
                        <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                            Join thousands of customers who trust {{ siteName }} for their financial needs.
                        </p>
                        <div class="flex flex-wrap gap-4 justify-center">
                            <Link
                                href="/register"
                                class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-bold text-primary-700 bg-white hover:bg-gray-50 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                            >
                                Open Account Now
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </Link>
                            <Link
                                href="/contact"
                                class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-bold text-white bg-white/10 hover:bg-white/20 border-2 border-white/30 backdrop-blur-sm transition-all duration-200"
                            >
                                Talk to an Expert
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <MainFooter />
        <MobileStickyFooter />
    </div>
</template>
