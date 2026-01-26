<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * CopyrightText Component
 * Displays copyright information using system settings
 */
defineProps({
    showExtraText: {
        type: Boolean,
        default: false,
    },
    textClass: {
        type: String,
        default: 'text-xs text-gray-500',
    },
});

const page = usePage();

const copyrightText = computed(() => page.props.settings?.branding?.copyright_text || 'Finora Bank');
const copyrightYear = computed(() => page.props.settings?.branding?.copyright_year || new Date().getFullYear());
const footerExtraText = computed(() => page.props.settings?.branding?.footer_extra_text || 'Member FDIC | Equal Housing Lender');
</script>

<template>
    <p :class="textClass">
        © {{ copyrightYear }} {{ copyrightText }}. All Rights Reserved.
        <template v-if="showExtraText">
            <br>
            {{ footerExtraText }}
        </template>
    </p>
</template>
