<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const currentYear = new Date().getFullYear();

// Get settings from page props with fallbacks
const siteName = computed(() => page.props.settings?.general?.site_name || 'Finora Bank');
const sitePhone = computed(() => page.props.settings?.general?.site_phone || '+1 (800) 555-0199');
const siteEmail = computed(() => page.props.settings?.general?.site_email || 'info@finorabank.com');
const siteAddress = computed(() => page.props.settings?.general?.site_address || '123 Financial District\nNew York, NY 10004');
const footerDescription = computed(() => page.props.settings?.branding?.site_footer_text || 'Your trusted partner for modern banking. We\'re committed to providing secure, innovative financial solutions for individuals and businesses worldwide.');
const copyrightText = computed(() => page.props.settings?.branding?.site_copyright_text || page.props.settings?.branding?.copyright_text || 'Finora Bank');
const copyrightYear = computed(() => page.props.settings?.branding?.site_copyright_year || page.props.settings?.branding?.copyright_year || currentYear);
const logoLight = computed(() => page.props.settings?.branding?.logo_light || null);
const logoDark = computed(() => page.props.settings?.branding?.logo_dark || null);

const quickLinks = [
    { name: 'About Us', href: '/about' },
    { name: 'Our Services', href: '/services' },
    { name: 'Contact Us', href: '/contact' },
    { name: 'Careers', href: '/careers' },
    { name: 'Press & Media', href: '/press' },
    { name: 'Blog', href: '/blog' },
];

const bankingLinks = [
    { name: 'Personal Banking', href: '/personal-banking' },
    { name: 'Business Banking', href: '/business-banking' },
    { name: 'Online Banking', href: '/login' },
    { name: 'Mobile Banking', href: '/mobile-banking' },
    { name: 'Loans & Mortgages', href: '/loans-and-mortgages' },
    { name: 'Credit Cards', href: '/credit-cards' },
];

const supportLinks = [
    { name: 'Help Center', href: '/help-center' },
    { name: 'FAQs', href: '/faqs' },
    { name: 'Security Center', href: '/security-center' },
    { name: 'Report Fraud', href: '/report-fraud' },
    { name: 'ATM Locator', href: '/atm-locator' },
    { name: 'Fee Schedule', href: '/fees' },
];

const legalLinks = [
    { name: 'Privacy Policy', href: '/privacy-policy' },
    { name: 'Terms of Service', href: '/terms' },
    { name: 'Cookie Policy', href: '/cookie-policy' },
    { name: 'Accessibility', href: '/accessibility' },
];

const socialLinks = [
    { name: 'Facebook', icon: 'facebook', href: '#' },
    { name: 'Twitter', icon: 'twitter', href: '#' },
    { name: 'LinkedIn', icon: 'linkedin', href: '#' },
    { name: 'Instagram', icon: 'instagram', href: '#' },
];
</script>

<template>
    <footer class="bg-gray-900 text-white">
        <!-- Main Footer -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12">
                <!-- Brand Column -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <!-- Logo -->
                    <div class="flex items-center mb-6">
                        <!-- Logo from settings (dark mode for footer with dark bg) -->
                        <img 
                            v-if="logoDark"
                            :src="logoDark"
                            :alt="siteName"
                            class="h-10 w-auto object-contain"
                        />
                        <!-- Fallback to light logo if dark not available -->
                        <img 
                            v-else-if="logoLight"
                            :src="logoLight"
                            :alt="siteName"
                            class="h-10 w-auto object-contain"
                        />
                        <!-- Fallback if no logos from settings -->
                        <template v-else>
                            <div class="w-10 h-10 bg-gradient-to-br from-gold-400 to-gold-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-xl font-bold">{{ siteName }}</span>
                        </template>
                    </div>
                    
                    <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                        {{ footerDescription }}
                    </p>

                    <!-- Contact Info -->
                    <div class="space-y-3">
                        <div class="flex items-center text-sm text-gray-400">
                            <svg class="w-4 h-4 mr-3 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ sitePhone }}
                        </div>
                        <div class="flex items-center text-sm text-gray-400">
                            <svg class="w-4 h-4 mr-3 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ siteEmail }}
                        </div>
                        <div class="flex items-start text-sm text-gray-400">
                            <svg class="w-4 h-4 mr-3 mt-0.5 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ siteAddress }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                        Quick Links
                    </h3>
                    <ul class="space-y-3">
                        <li v-for="link in quickLinks" :key="link.name">
                            <Link 
                                :href="link.href" 
                                class="text-sm text-gray-400 hover:text-gold-400 transition-colors duration-200"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Banking Services -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                        Banking Services
                    </h3>
                    <ul class="space-y-3">
                        <li v-for="link in bankingLinks" :key="link.name">
                            <Link 
                                :href="link.href" 
                                class="text-sm text-gray-400 hover:text-gold-400 transition-colors duration-200"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                        Support
                    </h3>
                    <ul class="space-y-3">
                        <li v-for="link in supportLinks" :key="link.name">
                            <Link 
                                :href="link.href" 
                                class="text-sm text-gray-400 hover:text-gold-400 transition-colors duration-200"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">
                        Stay Updated
                    </h3>
                    <p class="text-sm text-gray-400 mb-4">
                        Subscribe to our newsletter for financial tips and updates.
                    </p>
                    <form @submit.prevent class="space-y-3">
                        <input 
                            type="email" 
                            placeholder="Enter your email"
                            class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-gold-400 focus:ring-1 focus:ring-gold-400 transition-colors"
                        />
                        <button 
                            type="submit"
                            class="w-full px-4 py-2.5 bg-gradient-to-r from-gold-500 to-gold-600 text-sm font-semibold text-gray-900 rounded-lg hover:from-gold-400 hover:to-gold-500 transition-all duration-300"
                        >
                            Subscribe
                        </button>
                    </form>

                    <!-- Social Links -->
                    <div class="flex space-x-4 mt-6">
                        <a 
                            v-for="social in socialLinks" 
                            :key="social.name"
                            :href="social.href"
                            class="w-9 h-9 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:bg-gold-500 hover:text-white transition-all duration-300"
                            :aria-label="social.name"
                        >
                            <!-- Facebook -->
                            <svg v-if="social.icon === 'facebook'" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/>
                            </svg>
                            <!-- Twitter -->
                            <svg v-if="social.icon === 'twitter'" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                            <!-- LinkedIn -->
                            <svg v-if="social.icon === 'linkedin'" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z"/>
                            </svg>
                            <!-- Instagram -->
                            <svg v-if="social.icon === 'instagram'" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legal Footer -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <!-- Copyright -->
                    <div class="text-sm text-gray-500 text-center md:text-left">
                        © {{ copyrightYear }} {{ copyrightText }}. All rights reserved.
                    </div>

                    <!-- Legal Links -->
                    <div class="flex flex-wrap justify-center gap-4 md:gap-6">
                        <Link 
                            v-for="link in legalLinks" 
                            :key="link.name"
                            :href="link.href"
                            class="text-sm text-gray-500 hover:text-gold-400 transition-colors duration-200"
                        >
                            {{ link.name }}
                        </Link>
                    </div>
                </div>

                <!-- Regulatory Info -->
                <div class="mt-6 pt-6 border-t border-gray-800 text-center">
                    <div class="flex flex-wrap justify-center items-center gap-6 mb-4">
                        <!-- FDIC -->
                        <div class="flex items-center text-xs text-gray-500">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Member FDIC
                        </div>
                        <!-- Equal Housing -->
                        <div class="flex items-center text-xs text-gray-500">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Equal Housing Lender
                        </div>
                        <!-- NMLS -->
                        <div class="text-xs text-gray-500">
                            NMLS# 123456
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 max-w-3xl mx-auto leading-relaxed">
                        Finora Bank is the fastest-growing bank in Lithuania. Licensed by the European Central Bank (ECB), offers flexible financing solutions  to small and medium enterprises, from loans and leasing, to factoring and state guarantees - helping ambitious companies grow.
                    </p>
                </div>
            </div>
        </div>

        <!-- Mobile Spacer - for sticky mobile footer -->
        <div class="h-20 md:hidden"></div>
    </footer>
</template>
