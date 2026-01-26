<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * AppLogo Component
 * Displays the application logo with support for system settings
 * Falls back to hardcoded "F" icon if no logo is configured
 */
defineProps({
    showText: {
        type: Boolean,
        default: true,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    dark: {
        type: Boolean,
        default: false,
    },
    linkTo: {
        type: String,
        default: '/',
    },
});

const page = usePage();

const appName = computed(() => page.props.settings?.general?.app_name || 'Finora Bank');
const logoLight = computed(() => page.props.settings?.branding?.logo_light || '');
const logoDark = computed(() => page.props.settings?.branding?.logo_dark || '');

const sizeClasses = {
    sm: { icon: 'w-8 h-8 text-base', text: 'text-sm', img: 'h-8' },
    md: { icon: 'w-12 h-12 text-xl', text: 'text-lg', img: 'h-12' },
    lg: { icon: 'w-14 h-14 text-2xl', text: 'text-3xl', img: 'h-14' },
};
</script>

<template>
    <div class="flex items-center gap-3">
        <!-- Logo Image (Light Theme) -->
        <img
            v-if="logoLight && !dark"
            :src="logoLight"
            :alt="appName"
            :class="[sizeClasses[size].img, 'w-auto dark:hidden']"
        >
        
        <!-- Logo Image (Dark Theme) -->
        <img
            v-if="logoDark && dark"
            :src="logoDark"
            :alt="appName"
            :class="[sizeClasses[size].img, 'w-auto hidden dark:block']"
        >

        <!-- Fallback Logo Icon (when no images configured) -->
        <div
            v-if="!logoLight && !logoDark"
            :class="[
                sizeClasses[size].icon,
                'flex items-center justify-center rounded-2xl font-bold shadow-lg',
                dark ? 'bg-white text-primary-600' : 'bg-gradient-to-br from-primary-600 to-primary-700 text-white shadow-primary-600/30'
            ]"
        >
            {{ appName.charAt(0) }}
        </div>

        <!-- Logo Text -->
        <span
            v-if="showText"
            :class="[
                sizeClasses[size].text,
                'font-bold',
                dark ? 'text-white' : 'text-gray-900 dark:text-white'
            ]"
        >
            {{ appName.split(' ')[0] }}<span class="text-primary-600">{{ appName.split(' ')[1] || '' }}</span>
        </span>
    </div>
</template>
