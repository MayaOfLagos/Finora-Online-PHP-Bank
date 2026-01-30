<script setup>
/**
 * LiveChatWidget Component
 * 
 * Dynamically loads and injects live chat widgets based on admin settings.
 * Supports: Tawk.to, Smartsupp, JivoChat
 * 
 * No Vue build needed - scripts are injected at runtime from external CDNs.
 */

import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    // Context where the widget is displayed
    context: {
        type: String,
        default: 'dashboard', // 'public', 'auth', 'dashboard'
        validator: (value) => ['public', 'auth', 'dashboard'].includes(value),
    },
});

const page = usePage();

// Get live chat settings from Inertia shared props
const livechatSettings = computed(() => page.props.settings?.livechat || {});

// Check if chat should be shown on this page context
const shouldShowChat = computed(() => {
    const settings = livechatSettings.value;
    
    if (!settings.enabled || settings.provider === 'none' || !settings.widget_id) {
        return false;
    }

    switch (props.context) {
        case 'public':
            return settings.show_on_public;
        case 'auth':
            return settings.show_on_auth;
        case 'dashboard':
            return settings.show_on_dashboard;
        default:
            return false;
    }
});

const provider = computed(() => livechatSettings.value.provider);
const widgetId = computed(() => livechatSettings.value.widget_id);

// Track if widget is loaded
let widgetLoaded = false;

/**
 * Load Tawk.to widget
 * @param {string} propertyId - Format: "PROPERTY_ID/WIDGET_ID"
 */
const loadTawkTo = (propertyId) => {
    if (window.Tawk_API) {
        // Already loaded, just show it
        window.Tawk_API.showWidget?.();
        return;
    }

    window.Tawk_API = window.Tawk_API || {};
    window.Tawk_LoadStart = new Date();

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://embed.tawk.to/${propertyId}`;
    script.charset = 'UTF-8';
    script.setAttribute('crossorigin', '*');
    script.id = 'tawkto-script';
    
    document.head.appendChild(script);
    widgetLoaded = true;
};

/**
 * Load Smartsupp widget
 * @param {string} key - Smartsupp key
 */
const loadSmartsupp = (key) => {
    if (window.smartsupp) {
        // Already loaded
        return;
    }

    window._smartsupp = window._smartsupp || {};
    window._smartsupp.key = key;

    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.smartsuppchat.com/loader.js?';
    script.id = 'smartsupp-script';

    (function(d, s, id, src) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = src;
        fjs.parentNode.insertBefore(js, fjs);
    })(document, 'script', 'smartsupp-script', 'https://www.smartsuppchat.com/loader.js?');

    widgetLoaded = true;
};

/**
 * Load JivoChat widget
 * @param {string} widgetId - JivoChat widget ID
 */
const loadJivoChat = (widgetId) => {
    if (window.jivo_api) {
        // Already loaded
        return;
    }

    const script = document.createElement('script');
    script.async = true;
    script.src = `//code.jivosite.com/widget/${widgetId}`;
    script.id = 'jivochat-script';
    
    document.head.appendChild(script);
    widgetLoaded = true;
};

/**
 * Initialize the appropriate chat widget
 */
const initChatWidget = () => {
    if (!shouldShowChat.value) return;

    const id = widgetId.value;
    if (!id) return;

    switch (provider.value) {
        case 'tawkto':
            loadTawkTo(id);
            break;
        case 'smartsupp':
            loadSmartsupp(id);
            break;
        case 'jivochat':
            loadJivoChat(id);
            break;
    }
};

/**
 * Clean up / hide widget when component unmounts or settings change
 */
const cleanupWidget = () => {
    // Tawk.to
    if (window.Tawk_API?.hideWidget) {
        window.Tawk_API.hideWidget();
    }

    // Smartsupp - hide chat
    if (window.smartsupp) {
        try {
            window.smartsupp('chat:hide');
        } catch (e) {
            // Ignore errors
        }
    }

    // JivoChat - close
    if (window.jivo_api?.close) {
        window.jivo_api.close();
    }
};

// Initialize on mount
onMounted(() => {
    initChatWidget();
});

// Clean up on unmount
onUnmounted(() => {
    // We don't remove the scripts as they may be needed on other pages
    // Just hide the widget if possible
    if (!shouldShowChat.value) {
        cleanupWidget();
    }
});

// Watch for settings changes (in case of hot reload or dynamic changes)
watch(
    () => [livechatSettings.value, props.context],
    () => {
        if (shouldShowChat.value) {
            initChatWidget();
        } else {
            cleanupWidget();
        }
    },
    { deep: true }
);
</script>

<template>
    <!-- This component doesn't render anything visible -->
    <!-- It just manages the chat widget scripts -->
</template>
