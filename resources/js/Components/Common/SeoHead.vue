<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    keywords: {
        type: String,
        default: '',
    },
    image: {
        type: String,
        default: '',
    },
    canonical: {
        type: String,
        default: '',
    },
    appendSiteName: {
        type: Boolean,
        default: true,
    },
    noIndex: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const settings = computed(() => page.props.settings || {});
const branding = computed(() => settings.value.branding || {});
const seo = computed(() => settings.value.seo || {});

const siteName = computed(() => {
    return settings.value.general?.site_name || settings.value.general?.app_name || 'Finora Bank';
});

const resolvedTitle = computed(() => {
    const base = props.title || seo.value.meta_title || siteName.value;
    if (!props.appendSiteName) {
        return base;
    }
    if (!base) {
        return siteName.value;
    }
    return `${base} - ${siteName.value}`;
});

const resolvedDescription = computed(() => props.description || seo.value.meta_description || '');
const resolvedKeywords = computed(() => props.keywords || seo.value.meta_keywords || '');
const resolvedImage = computed(() => props.image || seo.value.og_image || branding.value.logo_light || branding.value.logo_dark || '');
const resolvedRobots = computed(() => (props.noIndex ? 'noindex, nofollow' : 'index, follow'));
const favicon = computed(() => branding.value.favicon || '');
</script>

<template>
    <Head>
        <title>{{ resolvedTitle }}</title>
        <link v-if="favicon" rel="icon" :href="favicon" />
        <link v-if="canonical" rel="canonical" :href="canonical" />

        <meta name="application-name" :content="siteName" />
        <meta name="apple-mobile-web-app-title" :content="siteName" />
        <meta name="description" v-if="resolvedDescription" :content="resolvedDescription" />
        <meta name="keywords" v-if="resolvedKeywords" :content="resolvedKeywords" />
        <meta name="robots" :content="resolvedRobots" />

        <meta property="og:title" :content="resolvedTitle" />
        <meta property="og:description" v-if="resolvedDescription" :content="resolvedDescription" />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" :content="siteName" />
        <meta property="og:image" v-if="resolvedImage" :content="resolvedImage" />

        <meta name="twitter:card" :content="seo.twitter_card || 'summary_large_image'" />
        <meta name="twitter:site" v-if="seo.twitter_site" :content="seo.twitter_site" />
        <meta name="twitter:title" :content="resolvedTitle" />
        <meta name="twitter:description" v-if="resolvedDescription" :content="resolvedDescription" />
        <meta name="twitter:image" v-if="resolvedImage" :content="resolvedImage" />
    </Head>
</template>
