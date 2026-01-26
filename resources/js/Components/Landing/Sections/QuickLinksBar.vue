<script setup>
import { Link } from '@inertiajs/vue3';

const quickLinks = [
    {
        icon: 'user',
        title: 'Personal Banking',
        description: 'Savings, checking, and everyday banking solutions for your lifestyle.',
        href: '/personal-banking'
    },
    {
        icon: 'briefcase',
        title: 'Business Banking',
        description: 'Comprehensive solutions to help your business grow and thrive.',
        href: '/business-banking'
    },
    {
        icon: 'credit-card',
        title: 'Cards & Payments',
        description: 'Premium debit and credit cards with exclusive rewards.',
        href: '/credit-cards'
    },
    {
        icon: 'chart',
        title: 'Loans & Financing',
        description: 'Competitive rates on personal, auto, and home loans.',
        href: '/loans-and-mortgages'
    }
];
</script>

<template>
    <section class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-gray-100">
                <Link
                    v-for="link in quickLinks"
                    :key="link.title"
                    :href="link.href"
                    class="group py-8 lg:py-10 px-4 lg:px-8 text-center hover:bg-gray-50/50 transition-colors duration-300 first:rounded-l-none last:rounded-r-none"
                >
                    <!-- Icon -->
                    <div class="w-14 h-14 mx-auto mb-4 bg-primary-50 rounded-xl flex items-center justify-center group-hover:bg-primary-100 group-hover:scale-105 transition-all duration-300">
                        <!-- User Icon -->
                        <svg v-if="link.icon === 'user'" class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <!-- Briefcase Icon -->
                        <svg v-else-if="link.icon === 'briefcase'" class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <!-- Credit Card Icon -->
                        <svg v-else-if="link.icon === 'credit-card'" class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <!-- Chart Icon -->
                        <svg v-else class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="text-sm lg:text-base font-semibold text-gray-900 group-hover:text-primary-600 transition-colors mb-2">
                        {{ link.title }}
                    </h3>
                    
                    <!-- Description (hidden on mobile) -->
                    <p class="hidden lg:block text-sm text-gray-500 leading-relaxed">
                        {{ link.description }}
                    </p>
                </Link>
            </div>
        </div>
    </section>
</template>
